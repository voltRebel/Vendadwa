<?php
/**
 * Product Controller
 * Handles product, category, unit management, stock movements, and pricing
 */

session_start();
require_once '../includes/db.php';
require_once '../includes/queries.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access.']);
    exit;
}

$company_id = $_SESSION['company_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : '';

    // =============================================
    // PRODUCT ACTIONS
    // =============================================
    if ($action === 'save_product') {
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        $name = trim($_POST['name'] ?? '');
        $sku = trim($_POST['sku'] ?? '');
        $category_id = isset($_POST['category_id']) && $_POST['category_id'] !== '' ? (int)$_POST['category_id'] : null;
        $unit_id = isset($_POST['unit_id']) && $_POST['unit_id'] !== '' ? (int)$_POST['unit_id'] : null;
        $cost_price = (float)($_POST['cost_price'] ?? 0);
        $selling_price = (float)($_POST['selling_price'] ?? 0);
        $stock_quantity = (int)($_POST['stock_quantity'] ?? 0);
        $min_stock_level = (int)($_POST['min_stock_level'] ?? 5);
        $description = trim($_POST['description'] ?? '');

        if (empty($name)) {
            echo json_encode(['status' => 'error', 'message' => 'Product name is required.']);
            exit;
        }

        try {
            $image_name = null;
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $target_dir = "../assets/image/products/";
                if (!file_exists($target_dir)) mkdir($target_dir, 0777, true);
                
                $file_extension = pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION);
                $image_name = "prod_" . time() . "_" . uniqid() . "." . $file_extension;
                move_uploaded_file($_FILES["image"]["tmp_name"], $target_dir . $image_name);
            }

            if ($id > 0) {
                // Fetch old image if no new one uploaded
                if (!$image_name) {
                    $old = $pdo->prepare("SELECT image FROM products WHERE id = ? AND company_id = ?");
                    $old->execute([$id, $company_id]);
                    $oldRow = $old->fetch();
                    $image_name = $oldRow ? $oldRow['image'] : null;
                }
                $sql = "UPDATE products SET name = ?, sku = ?, category_id = ?, unit_id = ?, 
                        cost_price = ?, selling_price = ?, stock_quantity = ?, min_stock_level = ?, 
                        description = ?, image = ? WHERE id = ? AND company_id = ?";
                $params = [$name, $sku, $category_id, $unit_id, $cost_price, $selling_price, $stock_quantity, $min_stock_level, $description, $image_name, $id, $company_id];
                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
                $message = "Product updated successfully!";
                logActivity($pdo, $company_id, $_SESSION['user_id'], 'product_update', "Updated product: $name");
            } else {
                $sql = "INSERT INTO products (company_id, name, sku, category_id, unit_id, image, cost_price, selling_price, stock_quantity, min_stock_level, description) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$company_id, $name, $sku, $category_id, $unit_id, $image_name, $cost_price, $selling_price, $stock_quantity, $min_stock_level, $description]);
                $message = "Product created successfully!";
                logActivity($pdo, $company_id, $_SESSION['user_id'], 'product_create', "Added new product: $name");
            }

            echo json_encode(['status' => 'success', 'message' => $message]);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
        }

    } elseif ($action === 'get_product') {
        $id = (int)($_POST['id'] ?? 0);
        try {
            $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ? AND company_id = ?");
            $stmt->execute([$id, $company_id]);
            $product = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($product) {
                echo json_encode(['status' => 'success', 'data' => $product]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Product not found.']);
            }
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }

    } elseif ($action === 'delete_product') {
        $id = (int)($_POST['id'] ?? 0);
        try {
            $nStmt = $pdo->prepare("SELECT name FROM products WHERE id = ? AND company_id = ?");
            $nStmt->execute([$id, $company_id]);
            $pName = $nStmt->fetchColumn() ?: "ID $id";

            $stmt = $pdo->prepare("DELETE FROM products WHERE id = ? AND company_id = ?");
            $stmt->execute([$id, $company_id]);
            logActivity($pdo, $company_id, $_SESSION['user_id'], 'product_delete', "Deleted product: $pName");
            echo json_encode(['status' => 'success', 'message' => 'Product deleted.']);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => 'Could not delete product.']);
        }

    // =============================================
    // CATEGORY ACTIONS
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
                $stmt = $pdo->prepare("UPDATE categories SET name = ?, description = ? WHERE id = ? AND company_id = ?");
                $stmt->execute([$name, $description, $id, $company_id]);
                $newId = $id;
                $message = 'Category updated!';
                logActivity($pdo, $company_id, $_SESSION['user_id'], 'category_update', "Updated category: $name");
            } else {
                $stmt = $pdo->prepare("INSERT INTO categories (company_id, name, description) VALUES (?, ?, ?)");
                $stmt->execute([$company_id, $name, $description]);
                $newId = $pdo->lastInsertId();
                $message = 'Category added!';
                logActivity($pdo, $company_id, $_SESSION['user_id'], 'category_create', "Added new category: $name");
            }
            echo json_encode(['status' => 'success', 'message' => $message, 'id' => $newId, 'name' => $name]);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => 'Error saving category: ' . $e->getMessage()]);
        }

    } elseif ($action === 'delete_category') {
        $id = (int)($_POST['id'] ?? 0);
        try {
            $nStmt = $pdo->prepare("SELECT name FROM categories WHERE id = ? AND company_id = ?");
            $nStmt->execute([$id, $company_id]);
            $cName = $nStmt->fetchColumn() ?: "ID $id";

            $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ? AND company_id = ?");
            $stmt->execute([$id, $company_id]);
            logActivity($pdo, $company_id, $_SESSION['user_id'], 'category_delete', "Deleted category: $cName");
            echo json_encode(['status' => 'success', 'message' => 'Category deleted.']);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => 'Cannot delete category (it may be in use).']);
        }

    } elseif ($action === 'get_categories') {
        try {
            $stmt = $pdo->prepare("SELECT c.id, c.name, c.description, c.status, COUNT(p.id) as product_count 
                FROM categories c LEFT JOIN products p ON p.category_id = c.id AND p.company_id = c.company_id
                WHERE c.company_id = ? GROUP BY c.id ORDER BY c.name ASC");
            $stmt->execute([$company_id]);
            echo json_encode(['status' => 'success', 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'data' => []]);
        }

    // =============================================
    // UNIT ACTIONS
    // =============================================
    } elseif ($action === 'save_unit') {
        $id = (int)($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $short_name = trim($_POST['short_name'] ?? '');
        if (empty($name)) {
            echo json_encode(['status' => 'error', 'message' => 'Unit name is required.']);
            exit;
        }
        try {
            if ($id > 0) {
                $stmt = $pdo->prepare("UPDATE units SET name = ?, short_name = ? WHERE id = ? AND company_id = ?");
                $stmt->execute([$name, $short_name, $id, $company_id]);
                $newId = $id;
                $message = 'Unit updated!';
                logActivity($pdo, $company_id, $_SESSION['user_id'], 'unit_update', "Updated unit: $name");
            } else {
                $stmt = $pdo->prepare("INSERT INTO units (company_id, name, short_name) VALUES (?, ?, ?)");
                $stmt->execute([$company_id, $name, $short_name]);
                $newId = $pdo->lastInsertId();
                $message = 'Unit added!';
                logActivity($pdo, $company_id, $_SESSION['user_id'], 'unit_create', "Added new unit: $name");
            }
            echo json_encode(['status' => 'success', 'message' => $message, 'id' => $newId, 'name' => $name]);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => 'Error saving unit.']);
        }

    } elseif ($action === 'delete_unit') {
        $id = (int)($_POST['id'] ?? 0);
        try {
            $nStmt = $pdo->prepare("SELECT name FROM units WHERE id = ? AND company_id = ?");
            $nStmt->execute([$id, $company_id]);
            $uName = $nStmt->fetchColumn() ?: "ID $id";

            $stmt = $pdo->prepare("DELETE FROM units WHERE id = ? AND company_id = ?");
            $stmt->execute([$id, $company_id]);
            logActivity($pdo, $company_id, $_SESSION['user_id'], 'unit_delete', "Deleted unit: $uName");
            echo json_encode(['status' => 'success', 'message' => 'Unit deleted.']);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => 'Cannot delete unit (it may be in use).']);
        }

    } elseif ($action === 'get_units') {
        try {
            $stmt = $pdo->prepare("SELECT id, name, short_name FROM units WHERE company_id = ? ORDER BY name ASC");
            $stmt->execute([$company_id]);
            echo json_encode(['status' => 'success', 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'data' => []]);
        }

    // =============================================
    // PRICE MANAGEMENT
    // =============================================
    } elseif ($action === 'update_price') {
        $id = (int)($_POST['id'] ?? 0);
        $cost_price = (float)($_POST['cost_price'] ?? 0);
        $selling_price = (float)($_POST['selling_price'] ?? 0);
        try {
            $stmt = $pdo->prepare("UPDATE products SET cost_price = ?, selling_price = ? WHERE id = ? AND company_id = ?");
            $stmt->execute([$cost_price, $selling_price, $id, $company_id]);
            echo json_encode(['status' => 'success', 'message' => 'Price updated successfully!']);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => 'Could not update price.']);
        }

    // =============================================
    // STOCK IN
    // =============================================
    } elseif ($action === 'stock_in') {
        $product_id = (int)($_POST['product_id'] ?? 0);
        $qty = (int)($_POST['qty'] ?? 0);
        $supplier = trim($_POST['supplier'] ?? '');
        $ref_number = trim($_POST['ref_number'] ?? '');
        $notes = trim($_POST['notes'] ?? '');
        $date = trim($_POST['date'] ?? date('Y-m-d'));

        if ($product_id <= 0 || $qty <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'Select a product and enter a valid quantity.']);
            exit;
        }
        try {
            // Ensure stock_movements table exists
            $pdo->exec("CREATE TABLE IF NOT EXISTS stock_movements (
                id INT AUTO_INCREMENT PRIMARY KEY,
                company_id INT NOT NULL,
                product_id INT NOT NULL,
                type ENUM('in','out','adjustment') NOT NULL,
                qty INT NOT NULL,
                before_qty INT DEFAULT 0,
                after_qty INT DEFAULT 0,
                supplier VARCHAR(100),
                ref_number VARCHAR(50),
                reason VARCHAR(100),
                notes TEXT,
                date DATE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

            // Get current stock
            $cur = $pdo->prepare("SELECT stock_quantity FROM products WHERE id = ? AND company_id = ?");
            $cur->execute([$product_id, $company_id]);
            $row = $cur->fetch();
            $before = $row ? (int)$row['stock_quantity'] : 0;
            $after = $before + $qty;

            // Update stock
            $pdo->prepare("UPDATE products SET stock_quantity = ? WHERE id = ? AND company_id = ?")->execute([$after, $product_id, $company_id]);

            // Log movement
            $pdo->prepare("INSERT INTO stock_movements (company_id, product_id, type, qty, before_qty, after_qty, supplier, ref_number, notes, date) VALUES (?,?,?,?,?,?,?,?,?,?)")
                ->execute([$company_id, $product_id, 'in', $qty, $before, $after, $supplier, $ref_number, $notes, $date]);

            $pNameStmt = $pdo->prepare("SELECT name FROM products WHERE id=? AND company_id=?");
            $pNameStmt->execute([$product_id, $company_id]);
            $pName = $pNameStmt->fetchColumn();

            logActivity($pdo, $company_id, $_SESSION['user_id'], 'stock_in', "Stock In for $pName: $before → $after (+$qty)");
            echo json_encode(['status' => 'success', 'message' => "Stock In recorded! Stock: $before → $after"]);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => 'Error: ' . $e->getMessage()]);
        }

    // =============================================
    // STOCK OUT
    // =============================================
    } elseif ($action === 'stock_out') {
        $product_id = (int)($_POST['product_id'] ?? 0);
        $qty = (int)($_POST['qty'] ?? 0);
        $reason = trim($_POST['reason'] ?? '');
        $notes = trim($_POST['notes'] ?? '');
        $date = trim($_POST['date'] ?? date('Y-m-d'));

        if ($product_id <= 0 || $qty <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'Select a product and enter a valid quantity.']);
            exit;
        }
        try {
            $pdo->exec("CREATE TABLE IF NOT EXISTS stock_movements (
                id INT AUTO_INCREMENT PRIMARY KEY,
                company_id INT NOT NULL,
                product_id INT NOT NULL,
                type ENUM('in','out','adjustment') NOT NULL,
                qty INT NOT NULL,
                before_qty INT DEFAULT 0,
                after_qty INT DEFAULT 0,
                supplier VARCHAR(100),
                ref_number VARCHAR(50),
                reason VARCHAR(100),
                notes TEXT,
                date DATE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

            $cur = $pdo->prepare("SELECT stock_quantity FROM products WHERE id = ? AND company_id = ?");
            $cur->execute([$product_id, $company_id]);
            $row = $cur->fetch();
            $before = $row ? (int)$row['stock_quantity'] : 0;
            $after = max(0, $before - $qty);

            $pdo->prepare("UPDATE products SET stock_quantity = ? WHERE id = ? AND company_id = ?")->execute([$after, $product_id, $company_id]);
            $pdo->prepare("INSERT INTO stock_movements (company_id, product_id, type, qty, before_qty, after_qty, reason, notes, date) VALUES (?,?,?,?,?,?,?,?,?)")
                ->execute([$company_id, $product_id, 'out', $qty, $before, $after, $reason, $notes, $date]);

            $pNameStmt = $pdo->prepare("SELECT name FROM products WHERE id=? AND company_id=?");
            $pNameStmt->execute([$product_id, $company_id]);
            $pName = $pNameStmt->fetchColumn();

            logActivity($pdo, $company_id, $_SESSION['user_id'], 'stock_out', "Stock Out for $pName: $before → $after (-$qty)");
            echo json_encode(['status' => 'success', 'message' => "Stock Out recorded! Stock: $before → $after"]);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => 'Error: ' . $e->getMessage()]);
        }

    // =============================================
    // ADJUSTMENT
    // =============================================
    } elseif ($action === 'adjust_stock') {
        $product_id = (int)($_POST['product_id'] ?? 0);
        $new_qty = (int)($_POST['new_qty'] ?? 0);
        $reason = trim($_POST['reason'] ?? '');
        $notes = trim($_POST['notes'] ?? '');
        $date = trim($_POST['date'] ?? date('Y-m-d'));

        if ($product_id <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'Select a product.']);
            exit;
        }
        try {
            $pdo->exec("CREATE TABLE IF NOT EXISTS stock_movements (
                id INT AUTO_INCREMENT PRIMARY KEY,
                company_id INT NOT NULL,
                product_id INT NOT NULL,
                type ENUM('in','out','adjustment') NOT NULL,
                qty INT NOT NULL,
                before_qty INT DEFAULT 0,
                after_qty INT DEFAULT 0,
                supplier VARCHAR(100),
                ref_number VARCHAR(50),
                reason VARCHAR(100),
                notes TEXT,
                date DATE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

            $cur = $pdo->prepare("SELECT stock_quantity FROM products WHERE id = ? AND company_id = ?");
            $cur->execute([$product_id, $company_id]);
            $row = $cur->fetch();
            $before = $row ? (int)$row['stock_quantity'] : 0;
            $diff = $new_qty - $before;

            $pdo->prepare("UPDATE products SET stock_quantity = ? WHERE id = ? AND company_id = ?")->execute([$new_qty, $product_id, $company_id]);
            $pdo->prepare("INSERT INTO stock_movements (company_id, product_id, type, qty, before_qty, after_qty, reason, notes, date) VALUES (?,?,?,?,?,?,?,?,?)")
                ->execute([$company_id, $product_id, 'adjustment', abs($diff), $before, $new_qty, $reason, $notes, $date]);

            $pNameStmt = $pdo->prepare("SELECT name FROM products WHERE id=? AND company_id=?");
            $pNameStmt->execute([$product_id, $company_id]);
            $pName = $pNameStmt->fetchColumn();

            $sign = $diff >= 0 ? "+$diff" : "$diff";
            logActivity($pdo, $company_id, $_SESSION['user_id'], 'stock_adjust', "Stock Adjusted for $pName: $before → $new_qty ($sign)");
            echo json_encode(['status' => 'success', 'message' => "Adjustment recorded! $before → $new_qty ($sign)"]);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => 'Error: ' . $e->getMessage()]);
        }
    }
}
?>
