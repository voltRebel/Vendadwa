<?php
/**
 * Settings Controller
 * Handles company settings and branch management
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
$user_role = $_SESSION['user_role'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // =============================================
    // SAVE ALL SETTINGS
    // =============================================
    if ($action === 'save_settings') {
        if ($user_role !== 'admin' && $user_role !== 'super_admin') {
            echo json_encode(['status' => 'error', 'message' => 'Only admins can change settings.']);
            exit;
        }

        try {
            $stmt = $pdo->prepare("UPDATE companies SET 
                name = ?, 
                phone = ?, 
                email = ?, 
                website = ?, 
                address = ?,
                tax_name = ?,
                tax_rate = ?,
                tax_enabled = ?,
                tax_included = ?,
                payment_cash = ?,
                payment_card = ?,
                payment_mobile = ?,
                payment_bank = ?,
                receipt_header = ?,
                receipt_footer = ?,
                receipt_autoprint = ?,
                receipt_email = ?,
                currency_code = ?,
                currency_symbol = ?,
                currency_decimals = ?,
                barcode_format = ?,
                barcode_prefix = ?,
                barcode_autogen = ?
                WHERE id = ?");

            $stmt->execute([
                $_POST['business_name'] ?? '',
                $_POST['phone'] ?? '',
                $_POST['email'] ?? '',
                $_POST['website'] ?? '',
                $_POST['address'] ?? '',
                $_POST['tax_name'] ?? 'Sales Tax',
                $_POST['tax_rate'] ?? 10.00,
                isset($_POST['tax_enabled']) ? 1 : 0,
                isset($_POST['tax_included']) ? 1 : 0,
                isset($_POST['payment_cash']) ? 1 : 0,
                isset($_POST['payment_card']) ? 1 : 0,
                isset($_POST['payment_mobile']) ? 1 : 0,
                isset($_POST['payment_bank']) ? 1 : 0,
                $_POST['receipt_header'] ?? '',
                $_POST['receipt_footer'] ?? '',
                isset($_POST['receipt_autoprint']) ? 1 : 0,
                isset($_POST['receipt_email']) ? 1 : 0,
                $_POST['currency_code'] ?? 'USD',
                $_POST['currency_symbol'] ?? '$',
                $_POST['currency_decimals'] ?? 2,
                $_POST['barcode_format'] ?? 'Code 128',
                $_POST['barcode_prefix'] ?? 'VEN-',
                isset($_POST['barcode_autogen']) ? 1 : 0,
                $company_id
            ]);

            logActivity($pdo, $company_id, $user_id, 'settings_update', "Updated company settings");
            echo json_encode(['status' => 'success', 'message' => 'Settings updated successfully!']);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
        }

    // =============================================
    // GET BRANCHES
    // =============================================
    } elseif ($action === 'get_branches') {
        try {
            $branches = getBranches($company_id);
            echo json_encode(['status' => 'success', 'data' => $branches]);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }

    // =============================================
    // SAVE BRANCH (Add / Edit)
    // =============================================
    } elseif ($action === 'save_branch') {
        $id = (int)($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $status = $_POST['status'] ?? 'active';

        if (empty($name)) {
            echo json_encode(['status' => 'error', 'message' => 'Branch name is required.']);
            exit;
        }

        try {
            if ($id > 0) {
                $stmt = $pdo->prepare("UPDATE branches SET name = ?, address = ?, status = ? WHERE id = ? AND company_id = ?");
                $stmt->execute([$name, $address, $status, $id, $company_id]);
                logActivity($pdo, $company_id, $user_id, 'branch_update', "Updated branch: $name");
            } else {
                $stmt = $pdo->prepare("INSERT INTO branches (company_id, name, address, status) VALUES (?, ?, ?, ?)");
                $stmt->execute([$company_id, $name, $address, $status]);
                logActivity($pdo, $company_id, $user_id, 'branch_create', "Created branch: $name");
            }
            echo json_encode(['status' => 'success', 'message' => 'Branch saved successfully!']);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }

    // =============================================
    // DELETE BRANCH
    // =============================================
    } elseif ($action === 'delete_branch') {
        $id = (int)($_POST['id'] ?? 0);
        try {
            $stmt = $pdo->prepare("DELETE FROM branches WHERE id = ? AND company_id = ?");
            $stmt->execute([$id, $company_id]);
            logActivity($pdo, $company_id, $user_id, 'branch_delete', "Deleted branch ID: $id");
            echo json_encode(['status' => 'success', 'message' => 'Branch deleted successfully.']);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
        }

    } else {
        echo json_encode(['status' => 'error', 'message' => 'Unknown action.']);
    }
}
