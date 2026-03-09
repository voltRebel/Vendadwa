<?php
/**
 * Database Queries
 */

require_once 'db.php';

/**
 * Authenticate a user
 */
function authenticateUser($username, $password) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT u.*, c.status as company_status, r.permissions
            FROM users u 
            LEFT JOIN companies c ON u.company_id = c.id 
            LEFT JOIN roles r ON u.role = r.name AND u.company_id = r.company_id
            WHERE u.username = ? 
            LIMIT 1
        ");
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }
    } catch (PDOException $e) {
        return false;
    }
    return false;
}

/**
 * Get products for the current company
 */
function getProducts() {
    global $pdo;
    $companyId = isset($_SESSION['company_id']) ? $_SESSION['company_id'] : 0;
    
    try {
        $stmt = $pdo->prepare("
            SELECT p.*, c.name as category_name, u.name as unit_name 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            LEFT JOIN units u ON p.unit_id = u.id 
            WHERE p.company_id = ? 
            ORDER BY p.name ASC
        ");
        $stmt->execute([$companyId]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

/**
 * Get categories for the current company
 */
function getCategories() {
    global $pdo;
    $companyId = isset($_SESSION['company_id']) ? $_SESSION['company_id'] : 0;
    try {
        $stmt = $pdo->prepare("
            SELECT c.*, COUNT(p.id) as product_count 
            FROM categories c 
            LEFT JOIN products p ON p.category_id = c.id AND p.company_id = c.company_id
            WHERE c.company_id = ? 
            GROUP BY c.id 
            ORDER BY c.name ASC
        ");
        $stmt->execute([$companyId]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

/**
 * Get units for the current company
 */
function getUnits() {
    global $pdo;
    $companyId = isset($_SESSION['company_id']) ? $_SESSION['company_id'] : 0;
    try {
        $stmt = $pdo->prepare("SELECT * FROM units WHERE company_id = ? ORDER BY name ASC");
        $stmt->execute([$companyId]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

/**
 * Get low stock products
 */
function getLowStockProducts() {
    global $pdo;
    $companyId = isset($_SESSION['company_id']) ? $_SESSION['company_id'] : 0;
    try {
        $stmt = $pdo->prepare("
            SELECT p.*, c.name as category_name, u.name as unit_name
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            LEFT JOIN units u ON p.unit_id = u.id 
            WHERE p.company_id = ? AND p.stock_quantity <= p.min_stock_level
            ORDER BY p.stock_quantity ASC
        ");
        $stmt->execute([$companyId]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

/**
 * Get stock movements (in, out, adjustments)
 */
function getStockMovements($type = null) {
    global $pdo;
    $companyId = isset($_SESSION['company_id']) ? $_SESSION['company_id'] : 0;
    try {
        $typeFilter = $type ? "AND sm.type = '$type'" : "";
        $stmt = $pdo->prepare("
            SELECT sm.*, p.name as product_name, p.sku
            FROM stock_movements sm
            JOIN products p ON sm.product_id = p.id
            WHERE sm.company_id = ? $typeFilter
            ORDER BY sm.created_at DESC
            LIMIT 100
        ");
        $stmt->execute([$companyId]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

/**
 * Get customers for the current company
 */
function getCustomers() {
    global $pdo;
    $companyId = isset($_SESSION['company_id']) ? $_SESSION['company_id'] : 0;
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM customers WHERE company_id = ? ORDER BY created_at DESC");
        $stmt->execute([$companyId]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

/**
 * Get company details
 */
function getCompanyDetails($companyId) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT * FROM companies WHERE id = ? LIMIT 1");
        $stmt->execute([$companyId]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        return null;
    }
}

/**
 * Get branches for the current company
 */
function getBranches($companyId) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT * FROM branches WHERE company_id = ? ORDER BY id ASC");
        $stmt->execute([$companyId]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

/**
 * Get suppliers for the current company
 */
function getSuppliers() {
    global $pdo;
    $companyId = isset($_SESSION['company_id']) ? $_SESSION['company_id'] : 0;
    try {
        $stmt = $pdo->prepare("
            SELECT s.*,
                   COUNT(DISTINCT po.id)          AS po_count,
                   COALESCE(SUM(po.total),0)       AS po_total
            FROM suppliers s
            LEFT JOIN purchase_orders po ON po.supplier_id = s.id AND po.company_id = s.company_id
            WHERE s.company_id = ?
            GROUP BY s.id
            ORDER BY s.name ASC
        ");
        $stmt->execute([$companyId]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

/**
 * Get supplier-module stat totals for the current company
 */
function getSupplierStats() {
    global $pdo;
    $companyId = isset($_SESSION['company_id']) ? $_SESSION['company_id'] : 0;
    try {
        $s = $pdo->prepare("SELECT
            COUNT(DISTINCT s.id)                                          AS total_suppliers,
            COALESCE(SUM(po.total),0)                                     AS total_po_value,
            COUNT(DISTINCT CASE WHEN po.status='pending' THEN po.id END)  AS pending_pos,
            COALESCE(SUM(CASE WHEN sp.status='paid' THEN sp.amount END),0) AS total_paid
            FROM suppliers s
            LEFT JOIN purchase_orders   po ON po.supplier_id = s.id AND po.company_id = s.company_id
            LEFT JOIN supplier_payments sp ON sp.supplier_id = s.id AND sp.company_id = s.company_id
            WHERE s.company_id = ?");
        $s->execute([$companyId]);
        return $s->fetch(PDO::FETCH_ASSOC) ?: ['total_suppliers'=>0,'total_po_value'=>0,'pending_pos'=>0,'total_paid'=>0];
    } catch (PDOException $e) {
        return ['total_suppliers'=>0,'total_po_value'=>0,'pending_pos'=>0,'total_paid'=>0];
    }
}

/**
 * Log user activity
 */
function logActivity($pdo, $company_id, $user_id, $action_type, $details) {
    try {
        $stmt = $pdo->prepare("INSERT INTO activity_logs (company_id, user_id, action_type, details) VALUES (?,?,?,?)");
        $stmt->execute([$company_id, $user_id, $action_type, $details]);
    } catch (PDOException $e) {
        // Silent fail for logging
    }
}
?>
