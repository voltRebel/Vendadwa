<?php
/**
 * Auth Controller
 * Handles authentication requests
 */

session_start();
require_once '../includes/queries.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : '';

    if ($action === 'login') {
        $username = isset($_POST['username']) ? $_POST['username'] : '';
        $password = isset($_POST['password']) ? $_POST['password'] : '';

        if (empty($username) || empty($password)) {
            echo json_encode(['status' => 'error', 'message' => 'Please fill in all fields.']);
            exit;
        }

        $user = authenticateUser($username, $password);

        if ($user) {
            // Check for locked company status (Super Admins bypass this)
            if ($user['role'] !== 'super_admin' && $user['company_status'] === 'inactive') {
                echo json_encode(['status' => 'error', 'message' => 'Your company account is locked. Please contact support.']);
                exit;
            }

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_avatar'] = $user['avatar'];
            $_SESSION['company_id'] = $user['company_id'];
            
            // Decode and store permissions in session (default to empty array if none)
            $permissions = isset($user['permissions']) ? json_decode($user['permissions'], true) : [];
            $_SESSION['user_permissions'] = is_array($permissions) ? $permissions : [];
            
            logActivity($pdo, $user['company_id'], $user['id'], 'login', 'Logged in successfully');
            
            echo json_encode(['status' => 'success', 'message' => 'Login successful! Welcome back.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Invalid username or password.']);
        }
    } elseif ($action === 'logout') {
        if (isset($_SESSION['user_id']) && isset($_SESSION['company_id'])) {
            logActivity($pdo, $_SESSION['company_id'], $_SESSION['user_id'], 'logout', 'Logged out');
        }
        session_destroy();
        echo json_encode(['status' => 'success', 'message' => 'Logged out successfully.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid action.']);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'logout') {
    if (isset($_SESSION['user_id']) && isset($_SESSION['company_id'])) {
        logActivity($pdo, $_SESSION['company_id'], $_SESSION['user_id'], 'logout', 'Logged out');
    }
    session_destroy();
    header('Location: ../index.php?page=login');
    exit;
} else {
    echo json_encode(['status' => 'error', 'message' => 'Direct access not allowed.']);
}
?>

