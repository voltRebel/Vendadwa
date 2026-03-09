<?php
/**
 * Analytics Controller
 * Provides data for Dashboard and Reports
 */

session_start();
require_once '../includes/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access.']);
    exit;
}

$company_id = $_SESSION['company_id'];
$action = $_POST['action'] ?? '';

if ($action === 'get_dashboard_stats') {
    try {
        // Today's Sales (POS + Manual - Refunds)
        $stmt = $pdo->prepare("
            SELECT 
                (SELECT COALESCE(SUM(total_amount), 0) FROM sales WHERE company_id = ? AND DATE(created_at) = CURDATE() AND status = 'completed') +
                (SELECT COALESCE(SUM(total), 0) FROM customer_purchases WHERE company_id = ? AND purchase_date = CURDATE()) -
                (SELECT COALESCE(SUM(refund_amount), 0) FROM sales_returns WHERE company_id = ? AND DATE(created_at) = CURDATE() AND status = 'refunded') as today_sales,
                (SELECT COUNT(*) FROM sales WHERE company_id = ? AND DATE(created_at) = CURDATE() AND status = 'completed') +
                (SELECT COUNT(*) FROM customer_purchases WHERE company_id = ? AND purchase_date = CURDATE()) as today_transactions
        ");
        $stmt->execute([$company_id, $company_id, $company_id, $company_id, $company_id]);
        $stats = $stmt->fetch(PDO::FETCH_ASSOC);

        // Lifetime Revenue (For Cash Balance - Deducting all refunds)
        $stmt = $pdo->prepare("
            SELECT 
                (SELECT COALESCE(SUM(total_amount), 0) FROM sales WHERE company_id = ? AND status = 'completed') +
                (SELECT COALESCE(SUM(total), 0) FROM customer_purchases WHERE company_id = ?) -
                (SELECT COALESCE(SUM(refund_amount), 0) FROM sales_returns WHERE company_id = ? AND status = 'refunded') as lifetime_revenue
        ");
        $stmt->execute([$company_id, $company_id, $company_id]);
        $lifetime_revenue = $stmt->fetchColumn();

        // Low Stock Count
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM products WHERE company_id = ? AND stock_quantity <= min_stock_level");
        $stmt->execute([$company_id]);
        $low_stock_count = $stmt->fetchColumn();

        // Recent Sales (POS + Manual)
        $stmt = $pdo->prepare("
            (SELECT s.receipt_no, s.total_amount as total, s.payment_method, s.created_at, c.name as customer_name, 'pos' as type
             FROM sales s 
             LEFT JOIN customers c ON s.customer_id = c.id 
             WHERE s.company_id = ? AND s.status = 'completed')
            UNION ALL
            (SELECT cp.receipt_no, cp.total, cp.payment_method, cp.created_at, c.name as customer_name, 'manual' as type
             FROM customer_purchases cp
             LEFT JOIN customers c ON cp.customer_id = c.id
             WHERE cp.company_id = ?)
            ORDER BY created_at DESC 
            LIMIT 5
        ");
        $stmt->execute([$company_id, $company_id]);
        $recent_sales = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Low Stock Products (Top 5)
        $stmt = $pdo->prepare("
            SELECT name, sku, stock_quantity, min_stock_level 
            FROM products 
            WHERE company_id = ? AND stock_quantity <= min_stock_level 
            ORDER BY stock_quantity ASC 
            LIMIT 5
        ");
        $stmt->execute([$company_id]);
        $low_stock_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Weekly Sales (Last 7 Days)
        $stmt = $pdo->prepare("
            SELECT dates.date, COALESCE(SUM(sales_total), 0) as total
            FROM (
                SELECT CURDATE() - INTERVAL (a.a + (10 * b.a)) DAY as date
                FROM (SELECT 0 as a UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) as a
                CROSS JOIN (SELECT 0 as a UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) as b
                LIMIT 7
            ) dates
            LEFT JOIN (
                SELECT DATE(created_at) as sale_date, SUM(total_amount) as sales_total
                FROM sales 
                WHERE company_id = ? AND status = 'completed' AND created_at >= CURDATE() - INTERVAL 6 DAY
                GROUP BY DATE(created_at)
                UNION ALL
                SELECT purchase_date as sale_date, SUM(total) as sales_total
                FROM customer_purchases
                WHERE company_id = ? AND purchase_date >= CURDATE() - INTERVAL 6 DAY
                GROUP BY purchase_date
            ) all_sales ON dates.date = all_sales.sale_date
            GROUP BY dates.date
            ORDER BY dates.date ASC
        ");
        $stmt->execute([$company_id, $company_id]);
        $weekly_sales = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Recent Activity
        $stmt = $pdo->prepare("
            SELECT al.*, u.name as user_name 
            FROM activity_logs al
            LEFT JOIN users u ON al.user_id = u.id
            WHERE al.company_id = ? 
            ORDER BY al.created_at DESC 
            LIMIT 5
        ");
        $stmt->execute([$company_id]);
        $recent_activity = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            'status' => 'success',
            'data' => [
                'today_sales' => (float)($stats['today_sales'] ?? 0),
                'today_transactions' => (int)($stats['today_transactions'] ?? 0),
                'lifetime_revenue' => (float)$lifetime_revenue,
                'low_stock_count' => (int)$low_stock_count,
                'recent_sales' => $recent_sales,
                'low_stock_items' => $low_stock_items,
                'weekly_sales' => $weekly_sales,
                'recent_activity' => $recent_activity
            ]
        ]);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    }

} elseif ($action === 'get_sales_report') {
    $startDate = $_POST['start_date'] ?? date('Y-m-01');
    $endDate = $_POST['end_date'] ?? date('Y-m-d');

    try {
        // Sales grouping by date (POS + Manual - Refunds)
        $stmt = $pdo->prepare("
            SELECT sale_date, SUM(transactions) as transactions, SUM(total) as total, SUM(cash_total) as cash_total, SUM(other_total) as other_total
            FROM (
                SELECT DATE(created_at) as sale_date, COUNT(*) as transactions, SUM(total_amount) as total, 
                       SUM(CASE WHEN payment_method = 'Cash' THEN total_amount ELSE 0 END) as cash_total,
                       SUM(CASE WHEN payment_method != 'Cash' THEN total_amount ELSE 0 END) as other_total
                FROM sales 
                WHERE company_id = ? AND DATE(created_at) BETWEEN ? AND ? AND status = 'completed'
                GROUP BY DATE(created_at)
                UNION ALL
                SELECT purchase_date as sale_date, COUNT(*) as transactions, SUM(total) as total,
                       SUM(CASE WHEN payment_method = 'Cash' THEN total ELSE 0 END) as cash_total,
                       SUM(CASE WHEN payment_method != 'Cash' THEN total ELSE 0 END) as other_total
                FROM customer_purchases
                WHERE company_id = ? AND purchase_date BETWEEN ? AND ?
                GROUP BY purchase_date
                UNION ALL
                SELECT DATE(created_at) as sale_date, 0 as transactions, -SUM(refund_amount) as total,
                       -SUM(CASE WHEN refund_method = 'Cash' THEN refund_amount ELSE 0 END) as cash_total,
                       -SUM(CASE WHEN refund_method != 'Cash' THEN refund_amount ELSE 0 END) as other_total
                FROM sales_returns
                WHERE company_id = ? AND DATE(created_at) BETWEEN ? AND ? AND status = 'refunded'
                GROUP BY DATE(created_at)
            ) combined
            GROUP BY sale_date
            ORDER BY sale_date DESC
        ");
        $stmt->execute([$company_id, $startDate, $endDate, $company_id, $startDate, $endDate, $company_id, $startDate, $endDate]);
        $report = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Totals (POS + Manual - Refunds)
        $stmt = $pdo->prepare("
            SELECT SUM(total) as total_revenue, SUM(transactions) as total_transactions
            FROM (
                SELECT SUM(total_amount) as total, COUNT(*) as transactions FROM sales 
                WHERE company_id = ? AND DATE(created_at) BETWEEN ? AND ? AND status = 'completed'
                UNION ALL
                SELECT SUM(total) as total, COUNT(*) as transactions FROM customer_purchases
                WHERE company_id = ? AND purchase_date BETWEEN ? AND ?
                UNION ALL
                SELECT -SUM(refund_amount) as total, 0 as transactions FROM sales_returns
                WHERE company_id = ? AND DATE(created_at) BETWEEN ? AND ? AND status = 'refunded'
            ) combined
        ");
        $stmt->execute([$company_id, $startDate, $endDate, $company_id, $startDate, $endDate, $company_id, $startDate, $endDate]);
        $totals = $stmt->fetch(PDO::FETCH_ASSOC);

        echo json_encode([
            'status' => 'success',
            'data' => [
                'report' => $report,
                'totals' => $totals
            ]
        ]);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    }
} elseif ($action === 'get_all_sales') {
    $startDate = $_POST['start_date'] ?? '';
    $endDate = $_POST['end_date'] ?? '';
    $search = trim($_POST['search'] ?? '');
    $payment = $_POST['payment_method'] ?? '';

    try {
        $sql = "
            SELECT s.id, s.receipt_no, s.total_amount, s.payment_method, s.discount_amount,
                   s.created_at, c.name as customer_name,
                   (SELECT COALESCE(SUM(qty), 0) FROM sale_items WHERE sale_id = s.id) as items_count
            FROM sales s
            LEFT JOIN customers c ON s.customer_id = c.id
            WHERE s.company_id = ? AND s.status = 'completed'
        ";
        $params = [$company_id];

        if (!empty($startDate) && !empty($endDate)) {
            $sql .= " AND DATE(s.created_at) BETWEEN ? AND ?";
            $params[] = $startDate;
            $params[] = $endDate;
        }
        if (!empty($search)) {
            $sql .= " AND (s.receipt_no LIKE ? OR c.name LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }
        if (!empty($payment)) {
            $sql .= " AND s.payment_method = ?";
            $params[] = $payment;
        }

        $sql .= " ORDER BY s.created_at DESC LIMIT 500";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $sales = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Quick totals
        $totalRev = array_sum(array_column($sales, 'total_amount'));
        $totalTrans = count($sales);

        echo json_encode([
            'status' => 'success',
            'data' => $sales,
            'totals' => [
                'total_revenue' => $totalRev,
                'total_transactions' => $totalTrans,
                'avg_transaction' => $totalTrans > 0 ? round($totalRev / $totalTrans, 2) : 0
            ]
        ]);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    }

} elseif ($action === 'get_inventory_report') {
    try {
        $stmt = $pdo->prepare("
            SELECT p.name, c.name as category_name, p.stock_quantity, p.cost_price, p.selling_price, p.min_stock_level
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE p.company_id = ?
            ORDER BY p.stock_quantity ASC
        ");
        $stmt->execute([$company_id]);
        $report = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stmt = $pdo->prepare("SELECT SUM(stock_quantity * cost_price) as total_value, SUM(stock_quantity) as total_items FROM products WHERE company_id = ?");
        $stmt->execute([$company_id]);
        $summary = $stmt->fetch(PDO::FETCH_ASSOC);

        echo json_encode([
            'status' => 'success',
            'data' => [
                'report' => $report,
                'summary' => $summary
            ]
        ]);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    }

} elseif ($action === 'get_cashier_report') {
    $startDate = !empty($_POST['start_date']) ? $_POST['start_date'] : '2000-01-01';
    $endDate = !empty($_POST['end_date']) ? $_POST['end_date'] : '2099-12-31';

    try {
        $stmt = $pdo->prepare("
            SELECT u.name as cashier_name, COUNT(*) as transactions, 
                   SUM(s.total_amount) as total_sales,
                   SUM(CASE WHEN s.payment_method = 'Cash' THEN s.total_amount ELSE 0 END) as cash_sales,
                   SUM(CASE WHEN s.payment_method != 'Cash' THEN s.total_amount ELSE 0 END) as other_sales,
                   ROUND(AVG(s.total_amount), 2) as avg_sale
            FROM sales s
            JOIN users u ON s.user_id = u.id
            WHERE s.company_id = ? AND DATE(s.created_at) BETWEEN ? AND ? AND s.status = 'completed'
            GROUP BY s.user_id
            ORDER BY total_sales DESC
        ");
        $stmt->execute([$company_id, $startDate, $endDate]);
        $report = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            'status' => 'success',
            'data' => $report
        ]);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    }

// =============================================
// PROFIT & LOSS REPORT
// =============================================
} elseif ($action === 'get_profit_loss') {
    $startDate = !empty($_POST['start_date']) ? $_POST['start_date'] : '2000-01-01';
    $endDate = !empty($_POST['end_date']) ? $_POST['end_date'] : '2099-12-31';

    try {
        // Total Revenue (POS + Manual)
        $stmt = $pdo->prepare("
            SELECT COALESCE(SUM(total_amount), 0) FROM sales 
            WHERE company_id = ? AND DATE(created_at) BETWEEN ? AND ? AND status = 'completed'
        ");
        $stmt->execute([$company_id, $startDate, $endDate]);
        $pos_revenue = (float)$stmt->fetchColumn();

        $stmt = $pdo->prepare("
            SELECT COALESCE(SUM(total), 0) FROM customer_purchases 
            WHERE company_id = ? AND purchase_date BETWEEN ? AND ?
        ");
        $stmt->execute([$company_id, $startDate, $endDate]);
        $manual_revenue = (float)$stmt->fetchColumn();

        // Cost of Goods Sold (from sale_items)
        $stmt = $pdo->prepare("
            SELECT COALESCE(SUM(si.qty * p.cost_price), 0) 
            FROM sale_items si
            JOIN sales s ON si.sale_id = s.id
            JOIN products p ON si.product_id = p.id
            WHERE s.company_id = ? AND DATE(s.created_at) BETWEEN ? AND ? AND s.status = 'completed'
        ");
        $stmt->execute([$company_id, $startDate, $endDate]);
        $cogs = (float)$stmt->fetchColumn();

        // Refunds
        $stmt = $pdo->prepare("
            SELECT COALESCE(SUM(refund_amount), 0) FROM sales_returns 
            WHERE company_id = ? AND DATE(created_at) BETWEEN ? AND ? AND status = 'refunded'
        ");
        $stmt->execute([$company_id, $startDate, $endDate]);
        $refunds = (float)$stmt->fetchColumn();

        // Expenses
        $stmt = $pdo->prepare("
            SELECT COALESCE(SUM(amount), 0) FROM expenses 
            WHERE company_id = ? AND expense_date BETWEEN ? AND ?
        ");
        $stmt->execute([$company_id, $startDate, $endDate]);
        $expenses = (float)$stmt->fetchColumn();

        // Expense Breakdown by Category
        $stmt = $pdo->prepare("
            SELECT ec.name as category_name, COALESCE(SUM(e.amount), 0) as total
            FROM expenses e
            LEFT JOIN expense_categories ec ON e.category_id = ec.id
            WHERE e.company_id = ? AND e.expense_date BETWEEN ? AND ?
            GROUP BY e.category_id
            ORDER BY total DESC
        ");
        $stmt->execute([$company_id, $startDate, $endDate]);
        $expense_breakdown = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $gross_revenue = $pos_revenue + $manual_revenue;
        $net_revenue = $gross_revenue - $refunds;
        $gross_profit = $net_revenue - $cogs;
        $net_profit = $gross_profit - $expenses;

        echo json_encode([
            'status' => 'success',
            'data' => [
                'gross_revenue' => $gross_revenue,
                'refunds' => $refunds,
                'net_revenue' => $net_revenue,
                'cogs' => $cogs,
                'gross_profit' => $gross_profit,
                'expenses' => $expenses,
                'net_profit' => $net_profit,
                'expense_breakdown' => $expense_breakdown
            ]
        ]);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    }

// =============================================
// DAILY SUMMARY (Z-REPORT)
// =============================================
} elseif ($action === 'get_daily_summary') {
    $date = $_POST['date'] ?? date('Y-m-d');

    try {
        // Sales for the day
        $stmt = $pdo->prepare("
            SELECT 
                COALESCE(SUM(total_amount), 0) as total_sales,
                COUNT(*) as transaction_count,
                SUM(CASE WHEN payment_method = 'Cash' THEN total_amount ELSE 0 END) as cash_sales,
                SUM(CASE WHEN payment_method = 'Card' THEN total_amount ELSE 0 END) as card_sales,
                SUM(CASE WHEN payment_method = 'Mobile Money' THEN total_amount ELSE 0 END) as mobile_sales,
                SUM(CASE WHEN payment_method NOT IN ('Cash','Card','Mobile Money') THEN total_amount ELSE 0 END) as other_sales
            FROM sales WHERE company_id = ? AND DATE(created_at) = ? AND status = 'completed'
        ");
        $stmt->execute([$company_id, $date]);
        $sales = $stmt->fetch(PDO::FETCH_ASSOC);

        // Manual purchases
        $stmt = $pdo->prepare("
            SELECT COALESCE(SUM(total), 0) as total, COUNT(*) as count
            FROM customer_purchases WHERE company_id = ? AND purchase_date = ?
        ");
        $stmt->execute([$company_id, $date]);
        $manual = $stmt->fetch(PDO::FETCH_ASSOC);

        // Refunds for the day
        $stmt = $pdo->prepare("
            SELECT COALESCE(SUM(refund_amount), 0) as total_refunds, COUNT(*) as refund_count
            FROM sales_returns WHERE company_id = ? AND DATE(created_at) = ? AND status = 'refunded'
        ");
        $stmt->execute([$company_id, $date]);
        $refunds = $stmt->fetch(PDO::FETCH_ASSOC);

        // Expenses for the day
        $stmt = $pdo->prepare("
            SELECT COALESCE(SUM(amount), 0) as total_expenses, COUNT(*) as expense_count
            FROM expenses WHERE company_id = ? AND expense_date = ?
        ");
        $stmt->execute([$company_id, $date]);
        $expenses = $stmt->fetch(PDO::FETCH_ASSOC);

        // Items sold
        $stmt = $pdo->prepare("
            SELECT COALESCE(SUM(si.qty), 0) as items_sold
            FROM sale_items si
            JOIN sales s ON si.sale_id = s.id
            WHERE s.company_id = ? AND DATE(s.created_at) = ? AND s.status = 'completed'
        ");
        $stmt->execute([$company_id, $date]);
        $items_sold = (int)$stmt->fetchColumn();

        echo json_encode([
            'status' => 'success',
            'data' => [
                'total_sales' => (float)$sales['total_sales'],
                'transaction_count' => (int)$sales['transaction_count'] + (int)$manual['count'],
                'cash_sales' => (float)$sales['cash_sales'],
                'card_sales' => (float)$sales['card_sales'],
                'mobile_sales' => (float)$sales['mobile_sales'],
                'other_sales' => (float)$sales['other_sales'],
                'manual_sales' => (float)$manual['total'],
                'total_refunds' => (float)$refunds['total_refunds'],
                'refund_count' => (int)$refunds['refund_count'],
                'total_expenses' => (float)$expenses['total_expenses'],
                'expense_count' => (int)$expenses['expense_count'],
                'items_sold' => $items_sold,
                'net_sales' => (float)$sales['total_sales'] + (float)$manual['total'] - (float)$refunds['total_refunds']
            ]
        ]);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    }
}
?>
