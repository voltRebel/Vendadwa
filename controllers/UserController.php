<?php
/**
 * User Controller
 * Handles user CRUD, role permissions, and activity logging
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
$current_user_id = $_SESSION['user_id'];
$current_role = $_SESSION['user_role'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // =============================================
    // GET ALL USERS
    // =============================================
    if ($action === 'get_users') {
        try {
            $stmt = $pdo->prepare("
                SELECT id, name, username, email, role, status, created_at, last_login 
                FROM users WHERE company_id = ? ORDER BY created_at ASC
            ");
            $stmt->execute([$company_id]);
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['status' => 'success', 'data' => $users]);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }

    // =============================================
    // SAVE USER (Add / Edit)
    // =============================================
    } elseif ($action === 'save_user') {
        // Only admin can manage users
        if ($current_role !== 'admin' && $current_role !== 'super_admin') {
            echo json_encode(['status' => 'error', 'message' => 'Only admins can manage users.']);
            exit;
        }

        $id       = (int)($_POST['id'] ?? 0);
        $name     = trim($_POST['name'] ?? '');
        $username = trim($_POST['username'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $role     = trim($_POST['role'] ?? 'cashier');
        $password = $_POST['password'] ?? '';
        $status   = trim($_POST['status'] ?? 'active');

        if (empty($name) || empty($username)) {
            echo json_encode(['status' => 'error', 'message' => 'Name and username are required.']);
            exit;
        }

        try {
            // Check for duplicate username
            $checkSql = "SELECT id FROM users WHERE username = ? AND company_id = ?";
            $checkParams = [$username, $company_id];
            if ($id > 0) {
                $checkSql .= " AND id != ?";
                $checkParams[] = $id;
            }
            $stmt = $pdo->prepare($checkSql);
            $stmt->execute($checkParams);
            if ($stmt->rowCount() > 0) {
                echo json_encode(['status' => 'error', 'message' => 'Username already exists.']);
                exit;
            }

            if ($id > 0) {
                // Update user
                if (!empty($password)) {
                    $hash = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("UPDATE users SET name=?, username=?, email=?, role=?, status=?, password=? WHERE id=? AND company_id=?");
                    $stmt->execute([$name, $username, $email, $role, $status, $hash, $id, $company_id]);
                } else {
                    $stmt = $pdo->prepare("UPDATE users SET name=?, username=?, email=?, role=?, status=? WHERE id=? AND company_id=?");
                    $stmt->execute([$name, $username, $email, $role, $status, $id, $company_id]);
                }
                logActivity($pdo, $company_id, $current_user_id, 'user_update', "Updated user: $name");
                echo json_encode(['status' => 'success', 'message' => 'User updated successfully!']);
            } else {
                // Create user
                if (empty($password)) {
                    echo json_encode(['status' => 'error', 'message' => 'Password is required for new users.']);
                    exit;
                }
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (company_id, name, username, email, password, role, status, created_at) VALUES (?,?,?,?,?,?,?,NOW())");
                $stmt->execute([$company_id, $name, $username, $email, $hash, $role, $status]);
                logActivity($pdo, $company_id, $current_user_id, 'user_create', "Created user: $name ($role)");
                echo json_encode(['status' => 'success', 'message' => 'User created successfully!']);
            }
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
        }

    // =============================================
    // DELETE USER
    // =============================================
    } elseif ($action === 'delete_user') {
        if ($current_role !== 'admin' && $current_role !== 'super_admin') {
            echo json_encode(['status' => 'error', 'message' => 'Only admins can delete users.']);
            exit;
        }

        $id = (int)($_POST['id'] ?? 0);

        if ($id === $current_user_id) {
            echo json_encode(['status' => 'error', 'message' => 'You cannot delete your own account.']);
            exit;
        }

        try {
            $stmt = $pdo->prepare("SELECT name FROM users WHERE id=? AND company_id=?");
            $stmt->execute([$id, $company_id]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                echo json_encode(['status' => 'error', 'message' => 'User not found.']);
                exit;
            }

            $pdo->prepare("DELETE FROM users WHERE id=? AND company_id=?")->execute([$id, $company_id]);
            logActivity($pdo, $company_id, $current_user_id, 'user_delete', "Deleted user: " . $user['name']);
            echo json_encode(['status' => 'success', 'message' => 'User deleted successfully.']);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => 'Cannot delete user. They may have associated records.']);
        }

    // =============================================
    // TOGGLE USER STATUS
    // =============================================
    } elseif ($action === 'toggle_status') {
        if ($current_role !== 'admin' && $current_role !== 'super_admin') {
            echo json_encode(['status' => 'error', 'message' => 'Only admins can change user status.']);
            exit;
        }

        $id = (int)($_POST['id'] ?? 0);

        if ($id === $current_user_id) {
            echo json_encode(['status' => 'error', 'message' => 'You cannot disable your own account.']);
            exit;
        }

        try {
            $stmt = $pdo->prepare("SELECT status, name FROM users WHERE id=? AND company_id=?");
            $stmt->execute([$id, $company_id]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$user) {
                echo json_encode(['status' => 'error', 'message' => 'User not found.']);
                exit;
            }
            $newStatus = $user['status'] === 'active' ? 'inactive' : 'active';
            $pdo->prepare("UPDATE users SET status=? WHERE id=? AND company_id=?")->execute([$newStatus, $id, $company_id]);
            logActivity($pdo, $company_id, $current_user_id, 'user_status', "Changed {$user['name']} status to $newStatus");
            echo json_encode(['status' => 'success', 'message' => "User {$newStatus}d successfully.", 'new_status' => $newStatus]);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }

    // =============================================
    // GET ROLES & PERMISSIONS CONFIG
    // =============================================
    } elseif ($action === 'get_roles') {
        // Define the default permissions matrix
        try {
            $stmt = $pdo->prepare("SELECT id, name, permissions FROM roles WHERE company_id = ?");
            $stmt->execute([$company_id]);
            $dbRoles = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $roles = [];
            foreach ($dbRoles as $row) {
                // Map to the format frontend expects
                $badge = 'badge-purple';
                if($row['name'] === 'admin') $badge = 'badge-pink';
                elseif($row['name'] === 'manager') $badge = 'badge-info';

                $roles[$row['name']] = [
                    'id' => $row['id'],
                    'label' => ucfirst($row['name']),
                    'badge' => $badge,
                    'permissions' => json_decode($row['permissions'], true)
                ];
            }
            echo json_encode(['status' => 'success', 'data' => $roles]);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }

    // =============================================
    // SAVE ROLE
    // =============================================
    } elseif ($action === 'save_role') {
        if ($current_role !== 'admin' && $current_role !== 'super_admin') {
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized.']);
            exit;
        }

        $id = (int)($_POST['id'] ?? 0);
        $name = strtolower(trim($_POST['name'] ?? ''));
        $perms = $_POST['permissions'] ?? [];

        if (empty($name)) {
            echo json_encode(['status' => 'error', 'message' => 'Role name is required.']);
            exit;
        }

        try {
            $permJson = json_encode($perms);
            if ($id > 0) {
                $stmt = $pdo->prepare("UPDATE roles SET name = ?, permissions = ? WHERE id = ? AND company_id = ?");
                $stmt->execute([$name, $permJson, $id, $company_id]);
                logActivity($pdo, $company_id, $current_user_id, 'role_update', "Updated role: $name");
            } else {
                $stmt = $pdo->prepare("INSERT INTO roles (company_id, name, permissions) VALUES (?, ?, ?)");
                $stmt->execute([$company_id, $name, $permJson]);
                logActivity($pdo, $company_id, $current_user_id, 'role_create', "Created role: $name");
            }
            echo json_encode(['status' => 'success', 'message' => 'Role saved successfully!']);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }

    // =============================================
    // DELETE ROLE
    // =============================================
    } elseif ($action === 'delete_role') {
        if ($current_role !== 'admin' && $current_role !== 'super_admin') {
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized.']);
            exit;
        }

        $id = (int)($_POST['id'] ?? 0);
        try {
            // Check if role is in use
            $stmt = $pdo->prepare("SELECT name FROM roles WHERE id = ? AND company_id = ?");
            $stmt->execute([$id, $company_id]);
            $role = $stmt->fetch();
            
            if ($role) {
                $check = $pdo->prepare("SELECT COUNT(*) FROM users WHERE role = ? AND company_id = ?");
                $check->execute([$role['name'], $company_id]);
                if ($check->fetchColumn() > 0) {
                    echo json_encode(['status' => 'error', 'message' => 'Cannot delete role while users are assigned to it.']);
                    exit;
                }
                
                $pdo->prepare("DELETE FROM roles WHERE id = ? AND company_id = ?")->execute([$id, $company_id]);
                logActivity($pdo, $company_id, $current_user_id, 'role_delete', "Deleted role: " . $role['name']);
                echo json_encode(['status' => 'success', 'message' => 'Role deleted.']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Role not found.']);
            }
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }

    // =============================================
    // GET ACTIVITY LOGS
    // =============================================
    } elseif ($action === 'get_activity_logs') {
        $filterUser = $_POST['user_id'] ?? '';

        try {
            $sql = "SELECT al.*, COALESCE(u.name, 'Deleted User') as user_name 
                    FROM activity_logs al 
                    LEFT JOIN users u ON al.user_id = u.id 
                    WHERE al.company_id = ?";
            $params = [$company_id];

            if (!empty($filterUser)) {
                $sql .= " AND al.user_id = ?";
                $params[] = (int)$filterUser;
            }

            $sql .= " ORDER BY al.created_at DESC LIMIT 200";

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode(['status' => 'success', 'data' => $logs]);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }

    // =============================================
    // GET USER STATS
    // =============================================
    } elseif ($action === 'get_user_stats') {
        try {
            $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM users WHERE company_id = ?");
            $stmt->execute([$company_id]);
            $total = $stmt->fetchColumn();

            $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE company_id = ? AND status = 'active'");
            $stmt->execute([$company_id]);
            $active = $stmt->fetchColumn();

            echo json_encode(['status' => 'success', 'data' => [
                'total_users' => $total,
                'active_users' => $active,
                'inactive_users' => $total - $active
            ]]);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }

    // =============================================
    // UPDATE PROFILE (Current User)
    // =============================================
    } elseif ($action === 'update_profile') {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $new_password = $_POST['new_password'] ?? '';
        
        if (empty($name)) {
            echo json_encode(['status' => 'error', 'message' => 'Name is required.']);
            exit;
        }

        try {
            $updateFields = ["name = ?", "email = ?"];
            $params = [$name, $email];

            // Handle Avatar Upload
            if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
                $fileTmpPath = $_FILES['avatar']['tmp_name'];
                $fileName = $_FILES['avatar']['name'];
                $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                $newFileName = $current_user_id . '_' . time() . '.' . $fileExtension;
                $uploadDir = '../assets/image/avatars/';
                $destPath = $uploadDir . $newFileName;

                // Create dir if not exists
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

                if (move_uploaded_file($fileTmpPath, $destPath)) {
                    $updateFields[] = "avatar = ?";
                    $params[] = $newFileName;
                    $_SESSION['user_avatar'] = $newFileName;
                }
            }

            // Handle Password Change
            if (!empty($new_password)) {
                $hash = password_hash($new_password, PASSWORD_DEFAULT);
                $updateFields[] = "password = ?";
                $params[] = $hash;
            }

            $sql = "UPDATE users SET " . implode(", ", $updateFields) . " WHERE id = ? AND company_id = ?";
            $params[] = $current_user_id;
            $params[] = $company_id;

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);

            // Update Session
            $_SESSION['user_name'] = $name;
            $_SESSION['user_email'] = $email;

            logActivity($pdo, $company_id, $current_user_id, 'profile_update', "Updated personal profile");
            echo json_encode(['status' => 'success', 'message' => 'Profile updated successfully!']);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
        }

    } else {
        echo json_encode(['status' => 'error', 'message' => 'Unknown action.']);
    }
}

?>
