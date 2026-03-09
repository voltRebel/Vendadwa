<?php
/**
 * Customer Controller
 * Handles customer CRUD, purchase history, and loyalty points
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // =============================================
    // SAVE CUSTOMER (Add / Edit)
    // =============================================
    if ($action === 'save_customer') {
        $id      = (int)($_POST['id'] ?? 0);
        $name    = trim($_POST['name'] ?? '');
        $email   = trim($_POST['email'] ?? '');
        $phone   = trim($_POST['phone'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $notes   = trim($_POST['notes'] ?? '');

        if (empty($name)) {
            echo json_encode(['status' => 'error', 'message' => 'Customer name is required.']);
            exit;
        }

        try {
            if ($id > 0) {
                $stmt = $pdo->prepare("UPDATE customers SET name=?, email=?, phone=?, address=?, notes=? WHERE id=? AND company_id=?");
                $stmt->execute([$name, $email, $phone, $address, $notes, $id, $company_id]);
                logActivity($pdo, $company_id, $_SESSION['user_id'], 'customer_update', "Updated customer: $name");
                echo json_encode(['status' => 'success', 'message' => 'Customer updated successfully!']);
            } else {
                $stmt = $pdo->prepare("INSERT INTO customers (company_id, name, email, phone, address, notes, loyalty_points) VALUES (?,?,?,?,?,?,0)");
                $stmt->execute([$company_id, $name, $email, $phone, $address, $notes]);
                $newId = $pdo->lastInsertId();
                logActivity($pdo, $company_id, $_SESSION['user_id'], 'customer_create', "Added new customer: $name");
                echo json_encode(['status' => 'success', 'message' => 'Customer added successfully!', 'id' => $newId]);
            }
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
        }

    // =============================================
    // GET SINGLE CUSTOMER
    // =============================================
    } elseif ($action === 'get_customer') {
        $id = (int)($_POST['id'] ?? 0);
        try {
            $stmt = $pdo->prepare("SELECT * FROM customers WHERE id=? AND company_id=?");
            $stmt->execute([$id, $company_id]);
            $cust = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($cust) {
                echo json_encode(['status' => 'success', 'data' => $cust]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Customer not found.']);
            }
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }

    // =============================================
    // DELETE CUSTOMER
    // =============================================
    } elseif ($action === 'delete_customer') {
        $id = (int)($_POST['id'] ?? 0);
        try {
            $stmt = $pdo->prepare("SELECT name FROM customers WHERE id=? AND company_id=?");
            $stmt->execute([$id, $company_id]);
            $cust = $stmt->fetch();
            $custName = $cust ? $cust['name'] : "ID: $id";
            
            $stmt = $pdo->prepare("DELETE FROM customers WHERE id=? AND company_id=?");
            $stmt->execute([$id, $company_id]);
            logActivity($pdo, $company_id, $_SESSION['user_id'], 'customer_delete', "Deleted customer: $custName");
            echo json_encode(['status' => 'success', 'message' => 'Customer deleted successfully.']);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => 'Could not delete customer. They may have purchase records.']);
        }

    // =============================================
    // GET PURCHASE HISTORY (filtered by customer)
    // =============================================
    } elseif ($action === 'get_purchase_history') {
        $customer_id = (int)($_POST['customer_id'] ?? 0);
        try {
            // Fetch from BOTH customer_purchases (manual entries) AND sales (POS entries)
            $sql = "(SELECT cp.id, cp.company_id, cp.customer_id, cp.receipt_no, cp.items, cp.total, 
                            cp.payment_method, cp.points_earned, cp.notes, cp.purchase_date, cp.created_at, 
                            c.name as customer_name, 'manual' as entry_type
                     FROM customer_purchases cp 
                     JOIN customers c ON cp.customer_id = c.id 
                     WHERE cp.company_id = ?" . ($customer_id > 0 ? " AND cp.customer_id = ?" : "") . ")
                    UNION ALL
                    (SELECT s.id, s.company_id, s.customer_id, s.receipt_no, 
                            (SELECT COALESCE(SUM(qty), 0) FROM sale_items WHERE sale_id = s.id) as items, 
                            s.total_amount as total, s.payment_method, 
                            FLOOR(s.total_amount / 10) as points_earned, s.notes, 
                            DATE(s.created_at) as purchase_date, s.created_at, 
                            c.name as customer_name, 'pos' as entry_type
                     FROM sales s 
                     JOIN customers c ON s.customer_id = c.id 
                     WHERE s.company_id = ? AND s.status = 'completed'" . ($customer_id > 0 ? " AND s.customer_id = ?" : "") . ")
                    ORDER BY purchase_date DESC, created_at DESC LIMIT 200";
            
            $params = [$company_id];
            if ($customer_id > 0) $params[] = $customer_id;
            $params[] = $company_id;
            if ($customer_id > 0) $params[] = $customer_id;

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['status' => 'success', 'data' => $results]);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'data' => [], 'message' => $e->getMessage()]);
        }

    // =============================================
    // ADD PURCHASE RECORD (manual entry)
    // =============================================
    } elseif ($action === 'add_purchase') {
        $customer_id   = (int)($_POST['customer_id'] ?? 0);
        $receipt_no    = trim($_POST['receipt_no'] ?? '');
        $items         = (int)($_POST['items'] ?? 1);
        $total         = (float)($_POST['total'] ?? 0);
        $payment_method = trim($_POST['payment_method'] ?? 'Cash');
        $purchase_date = trim($_POST['purchase_date'] ?? date('Y-m-d'));
        $notes         = trim($_POST['notes'] ?? '');

        if ($customer_id <= 0 || $total <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid customer or purchase amount.']);
            exit;
        }
        if (empty($receipt_no)) {
            $receipt_no = 'REC-' . strtoupper(substr(md5(time() . $customer_id), 0, 6));
        }

        try {
            // Calculate points earned: 1 point per GH₵10 spent
            $points_earned = (int)floor($total / 10);

            $stmt = $pdo->prepare("INSERT INTO customer_purchases (company_id, customer_id, receipt_no, items, total, payment_method, purchase_date, notes, points_earned) VALUES (?,?,?,?,?,?,?,?,?)");
            $stmt->execute([$company_id, $customer_id, $receipt_no, $items, $total, $payment_method, $purchase_date, $notes, $points_earned]);

            // Update customer loyalty points & total spend
            $pdo->prepare("UPDATE customers SET loyalty_points = loyalty_points + ?, total_purchases = total_purchases + ? WHERE id=? AND company_id=?")
                ->execute([$points_earned, $total, $customer_id, $company_id]);

            logActivity($pdo, $company_id, $_SESSION['user_id'], 'customer_purchase', "Added manual purchase for Customer ID $customer_id ($receipt_no, $total)");
            echo json_encode(['status' => 'success', 'message' => "Purchase recorded! +$points_earned loyalty points earned.", 'points' => $points_earned]);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => 'Error: ' . $e->getMessage()]);
        }

    // =============================================
    // ADJUST LOYALTY POINTS (manual)
    // =============================================
    } elseif ($action === 'adjust_points') {
        $customer_id = (int)($_POST['customer_id'] ?? 0);
        $points      = (int)($_POST['points'] ?? 0);
        $type        = $_POST['type'] ?? 'add'; // 'add' or 'redeem'
        $reason      = trim($_POST['reason'] ?? '');

        if ($customer_id <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid customer.']);
            exit;
        }
        try {
            if ($type === 'redeem') {
                // Ensure they have enough
                $cur = $pdo->prepare("SELECT loyalty_points FROM customers WHERE id=? AND company_id=?");
                $cur->execute([$customer_id, $company_id]);
                $row = $cur->fetch();
                if (!$row || $row['loyalty_points'] < $points) {
                    echo json_encode(['status' => 'error', 'message' => 'Insufficient loyalty points.']);
                    exit;
                }
                $pdo->prepare("UPDATE customers SET loyalty_points = loyalty_points - ? WHERE id=? AND company_id=?")
                    ->execute([$points, $customer_id, $company_id]);
                $msg = "$points points redeemed successfully!";
            } else {
                $pdo->prepare("UPDATE customers SET loyalty_points = loyalty_points + ? WHERE id=? AND company_id=?")
                    ->execute([$points, $customer_id, $company_id]);
                $msg = "$points points added successfully!";
            }
            logActivity($pdo, $company_id, $_SESSION['user_id'], 'customer_points', "Adjusted points for Customer ID $customer_id: $msg");
            echo json_encode(['status' => 'success', 'message' => $msg]);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => 'Error: ' . $e->getMessage()]);
        }

    } else {
        echo json_encode(['status' => 'error', 'message' => 'Unknown action.']);
    }
}
?>
