<?php
/**
 * Expense Controller
 * Handles expenses and expense categories
 */

session_start();
require_once '../includes/db.php';
require_once '../includes/queries.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access.']);
    exit;
}

$company_id = $_SESSION['company_id'];
$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // =============================================
    // GET EXPENSES
    // =============================================
    if ($action === 'get_expenses') {
        $search = trim($_POST['search'] ?? '');
        $category_id = (int)($_POST['category_id'] ?? 0);
        $date = $_POST['date'] ?? '';

        try {
            $sql = "SELECT e.*, ec.name as category_name 
                    FROM expenses e 
                    LEFT JOIN expense_categories ec ON e.category_id = ec.id 
                    WHERE e.company_id = ?";
            $params = [$company_id];

            if (!empty($search)) {
                $sql .= " AND e.description LIKE ?";
                $params[] = "%$search%";
            }
            if ($category_id > 0) {
                $sql .= " AND e.category_id = ?";
                $params[] = $category_id;
            }
            if (!empty($date)) {
                $sql .= " AND e.expense_date = ?";
                $params[] = $date;
            }

            $sql .= " ORDER BY e.expense_date DESC, e.created_at DESC";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            echo json_encode(['status' => 'success', 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }

    // =============================================
    // SAVE EXPENSE
    // =============================================
    } elseif ($action === 'save_expense') {
        $id = (int)($_POST['id'] ?? 0);
        $category_id = (int)($_POST['category_id'] ?? 0);
        $description = trim($_POST['description'] ?? '');
        $amount = (float)($_POST['amount'] ?? 0);
        $expense_date = $_POST['expense_date'] ?? date('Y-m-d');
        $payment_method = $_POST['payment_method'] ?? 'Cash';
        $notes = trim($_POST['notes'] ?? '');

        if (empty($description) || $amount <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'Description and valid amount are required.']);
            exit;
        }

        try {
            if ($id > 0) {
                $stmt = $pdo->prepare("UPDATE expenses SET category_id=?, description=?, amount=?, expense_date=?, payment_method=?, notes=? WHERE id=? AND company_id=?");
                $stmt->execute([$category_id > 0 ? $category_id : null, $description, $amount, $expense_date, $payment_method, $notes, $id, $company_id]);
                logActivity($pdo, $company_id, $_SESSION['user_id'], 'expense_update', "Updated expense: $description");
                echo json_encode(['status' => 'success', 'message' => 'Expense updated!']);
            } else {
                $stmt = $pdo->prepare("INSERT INTO expenses (company_id, category_id, description, amount, expense_date, payment_method, notes) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$company_id, $category_id > 0 ? $category_id : null, $description, $amount, $expense_date, $payment_method, $notes]);
                logActivity($pdo, $company_id, $_SESSION['user_id'], 'expense_create', "Added new expense: $description");
                echo json_encode(['status' => 'success', 'message' => 'Expense added!']);
            }
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }

    // =============================================
    // DELETE EXPENSE
    // =============================================
    } elseif ($action === 'delete_expense') {
        $id = (int)($_POST['id'] ?? 0);
        try {
            $eStmt = $pdo->prepare("SELECT description FROM expenses WHERE id=? AND company_id=?");
            $eStmt->execute([$id, $company_id]);
            $eDesc = $eStmt->fetchColumn() ?: "ID $id";

            $stmt = $pdo->prepare("DELETE FROM expenses WHERE id = ? AND company_id = ?");
            $stmt->execute([$id, $company_id]);
            logActivity($pdo, $company_id, $_SESSION['user_id'], 'expense_delete', "Deleted expense: $eDesc");
            echo json_encode(['status' => 'success', 'message' => 'Expense deleted.']);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }

    // =============================================
    // GET CATEGORIES
    // =============================================
    } elseif ($action === 'get_categories') {
        try {
            $stmt = $pdo->prepare("
                SELECT ec.*, 
                       (SELECT COUNT(*) FROM expenses WHERE category_id = ec.id) as expense_count,
                       (SELECT COALESCE(SUM(amount), 0) FROM expenses WHERE category_id = ec.id) as total_spent
                FROM expense_categories ec 
                WHERE ec.company_id = ? 
                ORDER BY ec.name ASC
            ");
            $stmt->execute([$company_id]);
            echo json_encode(['status' => 'success', 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }

    // =============================================
    // SAVE CATEGORY
    // =============================================
    } elseif ($action === 'save_category') {
        $id = (int)($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');

        if (empty($name)) {
            echo json_encode(['status' => 'error', 'message' => 'Category name is required.']);
            exit;
        }

        try {
            if ($id > 0) {
                $stmt = $pdo->prepare("UPDATE expense_categories SET name=?, description=? WHERE id=? AND company_id=?");
                $stmt->execute([$name, $description, $id, $company_id]);
                logActivity($pdo, $company_id, $_SESSION['user_id'], 'expense_category_update', "Updated expense category: $name");
                echo json_encode(['status' => 'success', 'message' => 'Category updated!']);
            } else {
                $stmt = $pdo->prepare("INSERT INTO expense_categories (company_id, name, description) VALUES (?, ?, ?)");
                $stmt->execute([$company_id, $name, $description]);
                logActivity($pdo, $company_id, $_SESSION['user_id'], 'expense_category_create', "Added new expense category: $name");
                echo json_encode(['status' => 'success', 'message' => 'Category added!']);
            }
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }

    // =============================================
    // DELETE CATEGORY
    // =============================================
    } elseif ($action === 'delete_category') {
        $id = (int)($_POST['id'] ?? 0);
        try {
            // Check if has expenses
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM expenses WHERE category_id = ?");
            $stmt->execute([$id]);
            if ($stmt->fetchColumn() > 0) {
                echo json_encode(['status' => 'error', 'message' => 'Cannot delete. This category has linked expenses.']);
                exit;
            }

            $cStmt = $pdo->prepare("SELECT name FROM expense_categories WHERE id=? AND company_id=?");
            $cStmt->execute([$id, $company_id]);
            $cName = $cStmt->fetchColumn() ?: "ID $id";

            $stmt = $pdo->prepare("DELETE FROM expense_categories WHERE id = ? AND company_id = ?");
            $stmt->execute([$id, $company_id]);
            logActivity($pdo, $company_id, $_SESSION['user_id'], 'expense_category_delete', "Deleted expense category: $cName");
            echo json_encode(['status' => 'success', 'message' => 'Category deleted.']);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }

    // =============================================
    // GET EXPENSE STATS
    // =============================================
    } elseif ($action === 'get_expense_stats') {
        try {
            $stmt = $pdo->prepare("
                SELECT 
                    (SELECT COALESCE(SUM(amount), 0) FROM expenses WHERE company_id = ? AND expense_date = CURDATE()) as today,
                    (SELECT COALESCE(SUM(amount), 0) FROM expenses WHERE company_id = ? AND MONTH(expense_date) = MONTH(CURDATE()) AND YEAR(expense_date) = YEAR(CURDATE())) as month,
                    (SELECT COALESCE(SUM(amount), 0) FROM expenses WHERE company_id = ? AND YEAR(expense_date) = YEAR(CURDATE())) as year
            ");
            $stmt->execute([$company_id, $company_id, $company_id]);
            echo json_encode(['status' => 'success', 'data' => $stmt->fetch(PDO::FETCH_ASSOC)]);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }
}
