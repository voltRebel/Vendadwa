<?php
/**
 * Super Admin Controller
 * Handles platform-level management tasks.
 */

session_start();
require_once '../includes/db.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

// Security check: Only Super Admins allowed
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'super_admin') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    ob_start();
    try {
        $action = isset($_POST['action']) ? $_POST['action'] : '';

        if ($action === 'create_company') {
            $name = isset($_POST['name']) ? trim($_POST['name']) : '';
            $email = isset($_POST['email']) ? trim($_POST['email']) : '';
            $phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
            $address = isset($_POST['address']) ? trim($_POST['address']) : '';
            $adminUsername = isset($_POST['username']) ? trim($_POST['username']) : '';
            $adminPassword = isset($_POST['admin_password']) ? $_POST['admin_password'] : '';
            
            $logoFilename = null;

            if (empty($name) || empty($email) || empty($phone) || empty($address) || empty($adminUsername) || empty($adminPassword)) {
                ob_clean();
                echo json_encode(['status' => 'error', 'message' => 'Please fill in all required fields.']);
                exit;
            }

            // Handle Logo Upload
            if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = '../assets/image/logos/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                
                $fileExt = pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION);
                $logoFilename = 'logo_' . time() . '_' . uniqid() . '.' . $fileExt;
                $uploadPath = $uploadDir . $logoFilename;

                if (!move_uploaded_file($_FILES['logo']['tmp_name'], $uploadPath)) {
                    $logoFilename = null; // Fallback if move fails
                }
            }

            try {
                $pdo->beginTransaction();

                // 1. Create the company
                $stmt = $pdo->prepare("INSERT INTO companies (name, email, phone, address, logo) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$name, $email, $phone, $address, $logoFilename]);
                $companyId = $pdo->lastInsertId();

                // 2. Create the company admin
                $hashedPassword = password_hash($adminPassword, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (name, username, password, role, company_id) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$name . ' Admin', $adminUsername, $hashedPassword, 'admin', $companyId]);

                $pdo->commit();
                ob_clean();
                echo json_encode(['status' => 'success', 'message' => "Company '$name' and its admin account have been created successfully."]);
            } catch (PDOException $e) {
                if ($pdo->inTransaction()) $pdo->rollBack();
                ob_clean();
                if ($e->getCode() == 23000) { // Duplicate entry
                    echo json_encode(['status' => 'error', 'message' => 'The username is already in use.']);
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
                }
            }
        } else if ($action === 'get_company') {
            $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
            // Join with users table to get admin credentials
            $stmt = $pdo->prepare("
                SELECT c.*, u.username as admin_username 
                FROM companies c 
                LEFT JOIN users u ON u.company_id = c.id AND u.role = 'admin' 
                WHERE c.id = ?
                LIMIT 1
            ");
            $stmt->execute([$id]);
            $company = $stmt->fetch();
            
            ob_clean();
            if ($company) {
                echo json_encode(['status' => 'success', 'data' => $company]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Company not found.']);
            }
        } else if ($action === 'toggle_status') {
            $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
            $currentStatus = isset($_POST['status']) ? $_POST['status'] : 'active';
            $newStatus = ($currentStatus === 'active') ? 'inactive' : 'active';
            
            $stmt = $pdo->prepare("UPDATE companies SET status = ? WHERE id = ?");
            ob_clean();
            if ($stmt->execute([$newStatus, $id])) {
                echo json_encode(['status' => 'success', 'message' => "Company status updated to $newStatus."]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to update status.']);
            }
        } else if ($action === 'delete_company') {
            $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

            $stmt = $pdo->prepare("SELECT logo FROM companies WHERE id = ?");
            $stmt->execute([$id]);
            $company = $stmt->fetch();

            if ($company) {
                try {
                    $pdo->beginTransaction();

                    // Delete logo file from disk if present
                    if (!empty($company['logo'])) {
                        $logoPath = '../assets/image/logos/' . $company['logo'];
                        if (file_exists($logoPath)) {
                            @unlink($logoPath);
                        }
                    }

                    // Deleting the company will cascade to all related records via FKs
                    $stmt = $pdo->prepare("DELETE FROM companies WHERE id = ?");
                    $success = $stmt->execute([$id]);

                    if ($success) {
                        $pdo->commit();
                        ob_clean();
                        echo json_encode(['status' => 'success', 'message' => 'Company and all associated data deleted successfully.']);
                    } else {
                        $pdo->rollBack();
                        ob_clean();
                        echo json_encode(['status' => 'error', 'message' => 'Failed to delete company.']);
                    }
                } catch (Exception $e) {
                    if ($pdo->inTransaction()) {
                        $pdo->rollBack();
                    }
                    ob_clean();
                    echo json_encode(['status' => 'error', 'message' => 'Error while deleting company: ' . $e->getMessage()]);
                }
            } else {
                ob_clean();
                echo json_encode(['status' => 'error', 'message' => 'Company not found.']);
            }
        } else if ($action === 'update_company') {
            $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
            $name = isset($_POST['name']) ? trim($_POST['name']) : '';
            $email = isset($_POST['email']) ? trim($_POST['email']) : '';
            $phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
            $address = isset($_POST['address']) ? trim($_POST['address']) : '';
            $adminUsername = isset($_POST['username']) ? trim($_POST['username']) : '';
            $adminPassword = isset($_POST['admin_password']) ? $_POST['admin_password'] : '';
            
            if (empty($id) || empty($name) || empty($email) || empty($phone) || empty($address)) {
                ob_clean();
                echo json_encode(['status' => 'error', 'message' => 'Please fill in all required fields.']);
                exit;
            }

            try {
                $pdo->beginTransaction();

                $logoUpdateSql = "";
                $params = [$name, $email, $phone, $address];
                
                if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
                    $uploadDir = '../assets/image/logos/';
                    $fileExt = pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION);
                    $logoFilename = 'logo_' . time() . '_' . uniqid() . '.' . $fileExt;
                    $uploadPath = $uploadDir . $logoFilename;

                    if (move_uploaded_file($_FILES['logo']['tmp_name'], $uploadPath)) {
                        $stmt = $pdo->prepare("SELECT logo FROM companies WHERE id = ?");
                        $stmt->execute([$id]);
                        $oldLogo = $stmt->fetchColumn();
                        if ($oldLogo) {
                            $oldLogoPath = $uploadDir . $oldLogo;
                            if (file_exists($oldLogoPath)) unlink($oldLogoPath);
                        }
                        
                        $logoUpdateSql = ", logo = ?";
                        $params[] = $logoFilename;
                    }
                }
                
                $params[] = $id;
                $sql = "UPDATE companies SET name = ?, email = ?, phone = ?, address = ? $logoUpdateSql WHERE id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);

                // Update Admin User
                if (!empty($adminUsername)) {
                    $userUpdateSql = "UPDATE users SET username = ?";
                    $userParams = [$adminUsername];
                    
                    if (!empty($adminPassword)) {
                        $userUpdateSql .= ", password = ?";
                        $userParams[] = password_hash($adminPassword, PASSWORD_DEFAULT);
                    }
                    
                    $userUpdateSql .= " WHERE company_id = ? AND role = 'admin'";
                    $userParams[] = $id;
                    
                    $stmtUsers = $pdo->prepare($userUpdateSql);
                    $stmtUsers->execute($userParams);
                }

                $pdo->commit();
                ob_clean();
                echo json_encode(['status' => 'success', 'message' => 'Company and Admin updated successfully.']);
            } catch (Exception $e) {
                if ($pdo->inTransaction()) $pdo->rollBack();
                ob_clean();
                echo json_encode(['status' => 'error', 'message' => 'Update failed: ' . $e->getMessage()]);
            }
        } else {
            ob_clean();
            echo json_encode(['status' => 'error', 'message' => 'Invalid action.']);
        }
    } catch (Exception $e) {
        ob_clean();
        echo json_encode(['status' => 'error', 'message' => 'Fatal Error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'POST method required.']);
}
?>
