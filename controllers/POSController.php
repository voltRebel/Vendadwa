<?php
/**
 * POS Controller
 * Handles product data fetching and completing sales
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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
    exit;
}

$action = $_POST['action'] ?? '';

if ($action === 'get_pos_data') {
    try {
        // Get Categories
        $stmt = $pdo->prepare("SELECT id, name FROM categories WHERE company_id = ? AND status = 'active' ORDER BY name ASC");
        $stmt->execute([$company_id]);
        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Get Products
        $stmt = $pdo->prepare("
            SELECT p.*, c.name as category_name 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            WHERE p.company_id = ? 
            ORDER BY p.name ASC
        ");
        $stmt->execute([$company_id]);
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Get Customers
        $stmt = $pdo->prepare("SELECT id, name, phone FROM customers WHERE company_id = ? ORDER BY name ASC");
        $stmt->execute([$company_id]);
        $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Get Company Settings (Tax, Currency, Payments)
        $stmt = $pdo->prepare("SELECT tax_name, tax_rate, tax_enabled, tax_included, payment_cash, payment_card, payment_mobile, payment_bank, currency_code, currency_symbol, currency_decimals FROM companies WHERE id = ?");
        $stmt->execute([$company_id]);
        $settings = $stmt->fetch(PDO::FETCH_ASSOC);

        echo json_encode([
            'status' => 'success',
            'data' => [
                'categories' => $categories,
                'products' => $products,
                'customers' => $customers,
                'settings' => $settings
            ]
        ]);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    }

} elseif ($action === 'add_customer_minimal') {
    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');

    if (empty($name)) {
        echo json_encode(['status' => 'error', 'message' => 'Name is required.']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO customers (company_id, name, phone) VALUES (?, ?, ?)");
        $stmt->execute([$company_id, $name, $phone]);
        $customer_id = $pdo->lastInsertId();

        echo json_encode([
            'status' => 'success',
            'message' => 'Customer added successfully!',
            'customer' => [
                'id' => $customer_id,
                'name' => $name,
                'phone' => $phone
            ]
        ]);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Error adding customer: ' . $e->getMessage()]);
    }

} elseif ($action === 'complete_sale') {
    $cart = json_decode($_POST['cart'] ?? '[]', true);
    $customer_id = (int)($_POST['customer_id'] ?? 0);
    $subtotal = (float)($_POST['subtotal'] ?? 0);
    $tax = (float)($_POST['tax'] ?? 0);
    $discount = (float)($_POST['discount'] ?? 0);
    $total = (float)($_POST['total'] ?? 0);
    $amount_received = (float)($_POST['amount_received'] ?? 0);
    $payment_method = $_POST['payment_method'] ?? 'Cash';
    $notes = trim($_POST['notes'] ?? '');

    if (empty($cart)) {
        echo json_encode(['status' => 'error', 'message' => 'Cart is empty.']);
        exit;
    }

    try {
        $pdo->beginTransaction();

        // 1. Generate Receipt Number: REC-YYYYMMDD-XXXX
        $today = date('Ymd');
        $countStmt = $pdo->prepare("SELECT COUNT(*) + 1 FROM sales WHERE company_id = ? AND DATE(created_at) = CURDATE()");
        $countStmt->execute([$company_id]);
        $seq = str_pad($countStmt->fetchColumn(), 4, '0', STR_PAD_LEFT);
        
        // Get Company Prefix if exists (placeholder for now, using REC)
        $receipt_no = "REC-{$today}-{$seq}";

        // Get Real Tax Rate from settings
        $stmtTax = $pdo->prepare("SELECT tax_rate, tax_enabled FROM companies WHERE id = ?");
        $stmtTax->execute([$company_id]);
        $comp = $stmtTax->fetch();
        $realTaxRate = ($comp['tax_enabled'] == 1) ? (float)$comp['tax_rate'] : 0;
        
        // Recalculate tax if not provided correctly from front
        if ($realTaxRate > 0) {
            $tax = ($subtotal - $discount) * ($realTaxRate / 100);
            $total = ($subtotal - $discount) + $tax;
        } else {
            $tax = 0;
            $total = $subtotal - $discount;
        }

        // 2. Insert into Sales
        $stmt = $pdo->prepare("
            INSERT INTO sales (company_id, user_id, customer_id, receipt_no, subtotal, tax_amount, discount_amount, total_amount, amount_received, change_amount, payment_method, status, notes) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'completed', ?)
        ");
        $change = $amount_received - $total;
        $stmt->execute([
            $company_id, $user_id, $customer_id > 0 ? $customer_id : null, 
            $receipt_no, $subtotal, $tax, $discount, $total, $amount_received, 
            $change > 0 ? $change : 0, $payment_method, $notes
        ]);
        $sale_id = $pdo->lastInsertId();

        // 3. Process Cart Items
        foreach ($cart as $item) {
            $product_id = (int)$item['id'];
            $qty = (int)$item['qty'];
            $price = (float)$item['price'];
            $line_total = $qty * $price;

            // Check stock
            $stRow = $pdo->prepare("SELECT stock_quantity, name FROM products WHERE id = ? AND company_id = ? FOR UPDATE");
            $stRow->execute([$product_id, $company_id]);
            $product = $stRow->fetch(PDO::FETCH_ASSOC);

            if (!$product) throw new Exception("Product ID {$product_id} not found.");
            if ($product['stock_quantity'] < $qty) {
                throw new Exception("Insufficient stock for {$product['name']}. Available: {$product['stock_quantity']}");
            }

            // Insert Sale Item
            $stmt = $pdo->prepare("INSERT INTO sale_items (company_id, sale_id, product_id, unit_price, qty, total) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$company_id, $sale_id, $product_id, $price, $qty, $line_total]);

            // Update Stock
            $before = $product['stock_quantity'];
            $after = $before - $qty;
            $stmt = $pdo->prepare("UPDATE products SET stock_quantity = ? WHERE id = ?");
            $stmt->execute([$after, $product_id]);

            // Add Stock Movement
            $stmt = $pdo->prepare("
                INSERT INTO stock_movements (company_id, product_id, type, qty, before_qty, after_qty, ref_number, reason, date) 
                VALUES (?, ?, 'out', ?, ?, ?, ?, 'POS Sale', CURDATE())
            ");
            $stmt->execute([$company_id, $product_id, $qty, $before, $after, $receipt_no]);
        }

        // 4. Update Customer loyalty/totals if customer selected
        if ($customer_id > 0) {
            $stmt = $pdo->prepare("UPDATE customers SET total_purchases = total_purchases + ?, loyalty_points = loyalty_points + ? WHERE id = ?");
            $points = floor($total / 10); // Example: 1 point per 10 currency units
            $stmt->execute([$total, $points, $customer_id]);
        }

        logActivity($pdo, $company_id, $_SESSION['user_id'], 'sale_completed', "Completed sale {$receipt_no} for total {$total}");

        $pdo->commit();
        echo json_encode([
            'status' => 'success', 
            'message' => 'Sale completed successfully!', 
            'sale_id' => $sale_id, 
            'receipt_no' => $receipt_no
        ]);

    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }

} else {
    echo json_encode(['status' => 'error', 'message' => 'Unknown action: ' . $action]);
}
?>
