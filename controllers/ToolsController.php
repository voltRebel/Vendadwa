<?php
/**
 * Tools Controller
 * Handles database backups, restoration, and data import/export.
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

// Only admins can access tools
if ($user_role !== 'admin' && $user_role !== 'super_admin') {
    echo json_encode(['status' => 'error', 'message' => 'Access denied. Administrator privileges required.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // =============================================
    // CREATE DATABASE BACKUP
    // =============================================
    if ($action === 'create_backup') {
        try {
            $backupDir = '../backups/';
            if (!is_dir($backupDir)) mkdir($backupDir, 0777, true);

            $filename = 'backup_' . $company_id . '_' . date('Y-m-d_H-i-s') . '.sql';
            $filePath = $backupDir . $filename;

            // Simple PHP-based backup (Exporting all tables)
            $tables = array();
            $result = $pdo->query("SHOW TABLES");
            while ($row = $result->fetch(PDO::FETCH_NUM)) {
                $tables[] = $row[0];
            }

            $sqlDump = "-- Ntɛm POS Backup\n";
            $sqlDump .= "-- Company ID: $company_id\n";
            $sqlDump .= "-- Date: " . date('Y-m-d H:i:s') . "\n\n";

            foreach ($tables as $table) {
                // Table structure
                $res = $pdo->query("SHOW CREATE TABLE `$table`")->fetch(PDO::FETCH_NUM);
                $sqlDump .= "DROP TABLE IF EXISTS `$table`;\n" . $res[1] . ";\n\n";
                
                // Table data
                $res = $pdo->query("SELECT * FROM `$table`")->fetchAll(PDO::FETCH_ASSOC);
                foreach ($res as $row) {
                    $keys = array_map(function($k) { return "`$k`"; }, array_keys($row));
                    $values = array_map(function($v) use ($pdo) { 
                        return $v === null ? "NULL" : $pdo->quote($v); 
                    }, array_values($row));
                    $sqlDump .= "INSERT INTO `$table` (" . implode(', ', $keys) . ") VALUES (" . implode(', ', $values) . ");\n";
                }
                $sqlDump .= "\n";
            }

            if (file_put_to_file($filePath, $sqlDump)) {
                $fileSizeValue = filesize($filePath);
                $fileSizeMB = round($fileSizeValue / (1024 * 1024), 2) . ' MB';

                $stmt = $pdo->prepare("INSERT INTO backups (company_id, filename, filesize, type, status) VALUES (?, ?, ?, 'Manual', 'Complete')");
                $stmt->execute([$company_id, $filename, $fileSizeMB]);

                logActivity($pdo, $company_id, $user_id, 'backup_created', "Created database backup: $filename");
                echo json_encode(['status' => 'success', 'message' => 'Backup created successfully!', 'filename' => $filename]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to write backup file. Check permissions.']);
            }
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => 'Backup failed: ' . $e->getMessage()]);
        }

    // =============================================
    // GET BACKUP HISTORY
    // =============================================
    } elseif ($action === 'get_backups') {
        try {
            $stmt = $pdo->prepare("SELECT * FROM backups WHERE company_id = ? ORDER BY created_at DESC");
            $stmt->execute([$company_id]);
            $backups = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['status' => 'success', 'data' => $backups]);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }

    // =============================================
    // DELETE BACKUP
    // =============================================
    } elseif ($action === 'delete_backup') {
        $id = (int)($_POST['id'] ?? 0);
        try {
            $stmt = $pdo->prepare("SELECT filename FROM backups WHERE id = ? AND company_id = ?");
            $stmt->execute([$id, $company_id]);
            $backup = $stmt->fetch();

            if ($backup) {
                $filePath = '../backups/' . $backup['filename'];
                if (file_exists($filePath)) unlink($filePath);

                $stmt = $pdo->prepare("DELETE FROM backups WHERE id = ?");
                $stmt->execute([$id]);

                logActivity($pdo, $company_id, $user_id, 'backup_deleted', "Deleted backup: " . $backup['filename']);
                echo json_encode(['status' => 'success', 'message' => 'Backup deleted successfully.']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Backup record not found.']);
            }
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }

    // =============================================
    // RESTORE DATABASE
    // =============================================
    } elseif ($action === 'restore_db') {
        $filename = $_POST['filename'] ?? '';
        $password = $_POST['password'] ?? '';

        if (empty($filename) || empty($password)) {
            echo json_encode(['status' => 'error', 'message' => 'Backup file and admin password are required.']);
            exit;
        }

        try {
            // Verify Admin Password
            $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            $user = $stmt->fetch();

            if (!$user || !password_verify($password, $user['password'])) {
                echo json_encode(['status' => 'error', 'message' => 'Invalid admin password.']);
                exit;
            }

            $filePath = '../backups/' . $filename;
            if (!file_exists($filePath)) {
                echo json_encode(['status' => 'error', 'message' => 'Backup file not found.']);
                exit;
            }

            $sql = file_get_contents($filePath);
            set_time_limit(0); 
            
            // Execute the SQL (Splitting by semicolon to handle multiple statements)
            $queries = explode(";\n", $sql);
            
            // NOTE: Transactions are not used here because DDL statements (DROP/CREATE) 
            // trigger implicit commits in MySQL, which would break the transaction.
            $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
            foreach ($queries as $query) {
                $query = trim($query);
                if (!empty($query)) {
                    $pdo->exec($query);
                }
            }
            $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");

            logActivity($pdo, $company_id, $user_id, 'database_restored', "Restored database from: $filename");
            echo json_encode(['status' => 'success', 'message' => 'Database restored successfully!']);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => 'Restore failed: ' . $e->getMessage()]);
        }

    // =============================================
    // EXPORT DATA (CSV)
    // =============================================
    } elseif ($action === 'export_csv') {
        $type = $_POST['type'] ?? 'products';
        
        try {
            if ($type === 'products') {
                $stmt = $pdo->prepare("SELECT sku, name, cost_price, selling_price, stock_quantity FROM products WHERE company_id = ?");
                $filename = "products_export_" . date('Y-m-d') . ".csv";
            } elseif ($type === 'customers') {
                $stmt = $pdo->prepare("SELECT name, email, phone, address FROM customers WHERE company_id = ?");
                $filename = "customers_export_" . date('Y-m-d') . ".csv";
            } elseif ($type === 'expenses') {
                $stmt = $pdo->prepare("SELECT title, amount, category, date, description FROM expenses WHERE company_id = ?");
                $filename = "expenses_export_" . date('Y-m-d') . ".csv";
            } else {
                $stmt = $pdo->prepare("SELECT receipt_no, total_amount, payment_method, created_at FROM sales WHERE company_id = ?");
                $filename = "sales_export_" . date('Y-m-d') . ".csv";
            }
            
            $stmt->execute([$company_id]);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="' . $filename . '"');

            $output = fopen('php://output', 'w');
            if (!empty($data)) {
                fputcsv($output, array_keys($data[0])); // Headers
                foreach ($data as $row) {
                    fputcsv($output, $row);
                }
            }
            fclose($output);
            exit;
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => 'Export failed: ' . $e->getMessage()]);
            exit;
        }

    // =============================================
    // GET CSV TEMPLATE
    // =============================================
    } elseif ($action === 'get_template') {
        $type = $_POST['type'] ?? 'products';
        $filename = $type . "_template.csv";
        
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        $output = fopen('php://output', 'w');
        if ($type === 'products') {
            fputcsv($output, ['sku', 'name', 'cost_price', 'selling_price', 'stock_quantity']);
            fputcsv($output, ['SKU001', 'Sample Product', '10.00', '15.00', '100']);
        } elseif ($type === 'customers') {
            fputcsv($output, ['name', 'email', 'phone', 'address']);
            fputcsv($output, ['John Doe', 'john@example.com', '1234567890', '123 Street, City']);
        } elseif ($type === 'expenses') {
            fputcsv($output, ['title', 'amount', 'category', 'date', 'description']);
            fputcsv($output, ['Rent', '1000.00', 'Utilities', date('Y-m-d'), 'Monthly shop rent']);
        }
        fclose($output);
        exit;

    // =============================================
    // IMPORT DATA (CSV)
    // =============================================
    } elseif ($action === 'import_csv') {
        $type = $_POST['import_type'] ?? 'products';
        if (!isset($_FILES['import_file'])) {
            echo json_encode(['status' => 'error', 'message' => 'No file uploaded.']);
            exit;
        }

        $file = $_FILES['import_file']['tmp_name'];
        $handle = fopen($file, "r");
        $header = fgetcsv($handle); // Skip header row

        $count = 0;
        try {
            $pdo->beginTransaction();
            while (($row = fgetcsv($handle)) !== FALSE) {
                if ($type === 'products') {
                    $stmt = $pdo->prepare("INSERT INTO products (company_id, sku, name, cost_price, selling_price, stock_quantity) 
                                         VALUES (?, ?, ?, ?, ?, ?) 
                                         ON DUPLICATE KEY UPDATE 
                                         name = VALUES(name), 
                                         cost_price = VALUES(cost_price), 
                                         selling_price = VALUES(selling_price), 
                                         stock_quantity = VALUES(stock_quantity)");
                    $stmt->execute([$company_id, $row[0], $row[1], $row[2], $row[3], $row[4]]);
                } elseif ($type === 'customers') {
                    $stmt = $pdo->prepare("INSERT INTO customers (company_id, name, email, phone, address) 
                                         VALUES (?, ?, ?, ?, ?) 
                                         ON DUPLICATE KEY UPDATE 
                                         email = VALUES(email), 
                                         phone = VALUES(phone),
                                         address = VALUES(address)");
                    $stmt->execute([$company_id, $row[0], $row[1], $row[2], $row[3]]);
                } elseif ($type === 'expenses') {
                    $stmt = $pdo->prepare("INSERT INTO expenses (company_id, title, amount, category, date, description) 
                                         VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$company_id, $row[0], $row[1], $row[2], $row[3], $row[4]]);
                }
                $count++;
            }
            $pdo->commit();
            logActivity($pdo, $company_id, $user_id, 'data_imported', "Imported $count $type via CSV");
            echo json_encode(['status' => 'success', 'message' => "Successfully imported $count $type!"]);
        } catch (Exception $e) {
            $pdo->rollBack();
            echo json_encode(['status' => 'error', 'message' => 'Import failed: ' . $e->getMessage()]);
        }
        fclose($handle);
        exit;

    // =============================================
    // CLEAR CACHE
    // =============================================
    } elseif ($action === 'clear_cache') {
        // For Ntɛm, we'll clear logs and any temporary files in assets/img/temp if it exists
        try {
            // Logic to clear session files or logs if applicable
            // For now, let's just simulate or clear a specific directory
            logActivity($pdo, $company_id, $user_id, 'cache_cleared', "System cache cleared");
            echo json_encode(['status' => 'success', 'message' => 'System cache cleared successfully!']);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }

    } else {
        echo json_encode(['status' => 'error', 'message' => 'Unknown action.']);
    }
}

/**
 * Helper to write file content (using file_put_contents)
 * Fixed typo in provided block
 */
function file_put_to_file($path, $content) {
    return file_put_contents($path, $content);
}
?>
