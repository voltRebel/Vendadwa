<?php
/**
 * Returns Controller
 * Handles sales returns, refunds, and voiding transactions
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
    // GET SALE DETAILS (for return prep)
    // =============================================
    if ($action === 'get_sale_details') {
        $receipt_no = trim($_POST['receipt_no'] ?? '');
        try {
            $stmt = $pdo->prepare("
                SELECT s.*, c.name as customer_name 
                FROM sales s 
                LEFT JOIN customers c ON s.customer_id = c.id 
                WHERE s.receipt_no = ? AND s.company_id = ? AND s.status = 'completed'
            ");
            $stmt->execute([$receipt_no, $company_id]);
            $sale = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($sale) {
                $itemStmt = $pdo->prepare("
                    SELECT si.*, p.name as product_name 
                    FROM sale_items si
                    JOIN products p ON si.product_id = p.id
                    WHERE si.sale_id = ? AND si.company_id = ?
                ");
                $itemStmt->execute([$sale['id'], $company_id]);
                $items = $itemStmt->fetchAll(PDO::FETCH_ASSOC);
                
                echo json_encode(['status' => 'success', 'sale' => $sale, 'items' => $items]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Active sale not found for this receipt number.']);
            }
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }

    // =============================================
    // PROCESS RETURN
    // =============================================
    } elseif ($action === 'process_return') {
        $sale_id = (int)($_POST['sale_id'] ?? 0);
        $reason = trim($_POST['reason'] ?? '');
        $items_json = $_POST['items'] ?? '[]';
        $return_items = json_decode($items_json, true);

        if ($sale_id <= 0 || empty($return_items)) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid return data.']);
            exit;
        }

        try {
            $pdo->beginTransaction();

            $return_no = 'RTN-' . time() . '-' . rand(100, 999);
            
            // Get original sale
            $sStmt = $pdo->prepare("SELECT receipt_no, customer_id FROM sales WHERE id = ? AND company_id = ?");
            $sStmt->execute([$sale_id, $company_id]);
            $orig_sale = $sStmt->fetch();

            // Insert into sales_returns
            $stmt = $pdo->prepare("
                INSERT INTO sales_returns (company_id, sale_id, customer_id, receipt_no, return_number, reason, status)
                VALUES (?, ?, ?, ?, ?, ?, 'pending')
            ");
            $stmt->execute([$company_id, $sale_id, $orig_sale['customer_id'], $orig_sale['receipt_no'], $return_no, $reason]);
            $return_id = $pdo->lastInsertId();

            $total_return_val = 0;

            foreach ($return_items as $item) {
                $product_id = (int)$item['product_id'];
                $qty = (int)$item['qty'];
                $price = (float)$item['price'];
                $condition = $item['condition'] ?? 'restock';

                $line_total = $qty * $price;
                $total_return_val += $line_total;

                $stmt = $pdo->prepare("
                    INSERT INTO sales_return_items (company_id, return_id, product_id, qty, unit_price, condition_status)
                    VALUES (?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([$company_id, $return_id, $product_id, $qty, $price, $condition]);

                // Update stock if restock
                if ($condition === 'restock') {
                    $pdo->prepare("UPDATE products SET stock_quantity = stock_quantity + ? WHERE id = ? AND company_id = ?")
                        ->execute([$qty, $product_id, $company_id]);
                    
                    // Log movement
                    $pdo->prepare("
                        INSERT INTO stock_movements (company_id, product_id, type, qty, ref_number, reason, date)
                        VALUES (?, ?, 'in', ?, ?, 'Sale Return', CURDATE())
                    ")->execute([$company_id, $product_id, $qty, $return_no]);
                }
            }

            // Update return total
            $pdo->prepare("UPDATE sales_returns SET total_amount = ? WHERE id = ?")->execute([$total_return_val, $return_id]);

            logActivity($pdo, $company_id, $_SESSION['user_id'], 'return_processed', "Processed return {$return_no} for sale {$orig_sale['receipt_no']}");

            $pdo->commit();
            echo json_encode(['status' => 'success', 'message' => 'Return processed successfully!', 'return_number' => $return_no]);
        } catch (Exception $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }

    // =============================================
    // PROCESS REFUND
    // =============================================
    } elseif ($action === 'process_refund') {
        $return_id = (int)($_POST['return_id'] ?? 0);
        $method = $_POST['refund_method'] ?? 'Cash';
        $amount = (float)($_POST['amount'] ?? 0);

        try {
            $pdo->beginTransaction();

            // 1. Update Return Status
            $stmt = $pdo->prepare("UPDATE sales_returns SET refund_amount = ?, refund_method = ?, status = 'refunded' WHERE id = ? AND company_id = ?");
            $stmt->execute([$amount, $method, $return_id, $company_id]);

            // 2. Deduct from Customer Lifetime Spend
            $cStmt = $pdo->prepare("SELECT customer_id FROM sales_returns WHERE id = ?");
            $cStmt->execute([$return_id]);
            $cid = $cStmt->fetchColumn();

            if ($cid) {
                $pdo->prepare("UPDATE customers SET total_purchases = total_purchases - ? WHERE id = ? AND company_id = ?")
                    ->execute([$amount, $cid, $company_id]);
            }

            logActivity($pdo, $company_id, $_SESSION['user_id'], 'refund_processed', "Processed refund for return ID {$return_id} amount {$amount}");

            $pdo->commit();
            echo json_encode(['status' => 'success', 'message' => 'Refund marked as completed and customer total adjusted.']);
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }

    // =============================================
    // VOID TRANSACTION
    // =============================================
    } elseif ($action === 'void_transaction') {
        $receipt_no = trim($_POST['receipt_no'] ?? '');
        $reason = trim($_POST['reason'] ?? '');
        $admin_password = $_POST['admin_password'] ?? '';

        // Verification logic (assuming super_admin or admin role)
        $authStmt = $pdo->prepare("SELECT password, role FROM users WHERE id = ?");
        $authStmt->execute([$user_id]);
        $user = $authStmt->fetch();

        if (!password_verify($admin_password, $user['password']) || !in_array($user['role'], ['admin', 'super_admin'])) {
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized or invalid admin password.']);
            exit;
        }

        try {
            $pdo->beginTransaction();

            // Find sale
            $stmt = $pdo->prepare("SELECT id, receipt_no FROM sales WHERE receipt_no = ? AND company_id = ? AND status = 'completed'");
            $stmt->execute([$receipt_no, $company_id]);
            $sale = $stmt->fetch();

            if (!$sale) {
                throw new Exception("Sale not found or already cancelled/voided.");
            }

            // 1. Mark Sale as cancelled
            $pdo->prepare("UPDATE sales SET status = 'cancelled' WHERE id = ?")->execute([$sale['id']]);

            // 1b. Deduct from Customer Lifetime Spend
            $sData = $pdo->prepare("SELECT customer_id, total_amount FROM sales WHERE id = ?");
            $sData->execute([$sale['id']]);
            $row = $sData->fetch();
            if ($row && $row['customer_id']) {
                $pdo->prepare("UPDATE customers SET total_purchases = total_purchases - ? WHERE id = ? AND company_id = ?")
                    ->execute([$row['total_amount'], $row['customer_id'], $company_id]);
            }

            // 2. Log void
            $pdo->prepare("INSERT INTO void_logs (company_id, sale_id, receipt_no, reason, admin_id) VALUES (?, ?, ?, ?, ?)")
                ->execute([$company_id, $sale['id'], $receipt_no, $reason, $user_id]);

            // 3. Restore stock & Log movement
            $itemStmt = $pdo->prepare("SELECT product_id, qty FROM sale_items WHERE sale_id = ?");
            $itemStmt->execute([$sale['id']]);
            foreach ($itemStmt->fetchAll() as $item) {
                $pdo->prepare("UPDATE products SET stock_quantity = stock_quantity + ? WHERE id = ?")->execute([$item['qty'], $item['product_id']]);
                
                $pdo->prepare("
                    INSERT INTO stock_movements (company_id, product_id, type, qty, ref_number, reason, date)
                    VALUES (?, ?, 'in', ?, ?, 'Sale Voided', CURDATE())
                ")->execute([$company_id, $item['product_id'], $item['qty'], $receipt_no]);
            }

            logActivity($pdo, $company_id, $_SESSION['user_id'], 'sale_voided', "Voided sale {$receipt_no}: {$reason}");

            $pdo->commit();
            echo json_encode(['status' => 'success', 'message' => "Transaction $receipt_no has been voided successfully."]);
        } catch (Exception $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }

    // =============================================
    // GET RETURNS HISTORY
    // =============================================
    } elseif ($action === 'get_returns_history') {
        try {
            $stmt = $pdo->prepare("
                SELECT r.*, c.name as customer_name,
                       (SELECT COUNT(*) FROM sales_return_items WHERE return_id = r.id) as items_count
                FROM sales_returns r
                LEFT JOIN customers c ON r.customer_id = c.id
                WHERE r.company_id = ? 
                ORDER BY r.created_at DESC
            ");
            $stmt->execute([$company_id]);
            echo json_encode(['status' => 'success', 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    } elseif ($action === 'get_return_stats') {
        try {
            $stmt = $pdo->prepare("
                SELECT 
                    (SELECT COUNT(*) FROM sales_returns WHERE company_id = ? AND DATE(created_at) = CURDATE()) as total_returns,
                    (SELECT COALESCE(SUM(refund_amount), 0) FROM sales_returns WHERE company_id = ? AND status = 'refunded' AND DATE(created_at) = CURDATE()) as today_refunded,
                    (SELECT COUNT(*) FROM sales_returns WHERE company_id = ? AND status = 'pending') as pending_refunds
            ");
            $stmt->execute([$company_id, $company_id, $company_id]);
            echo json_encode(['status' => 'success', 'data' => $stmt->fetch(PDO::FETCH_ASSOC)]);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }
}
