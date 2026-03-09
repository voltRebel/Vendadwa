<?php
/**
 * Vendora — POS & Inventory System
 * Main Router
 */

session_start();

// Get the requested page
$page = isset($_GET['page']) ? $_GET['page'] : 'login';

// Pages that don't need the sidebar layout (auth pages)
$authPages = ['login', 'forgot_password', 'change_password'];

$isAuthPage = in_array($page, $authPages);

// Map page slugs to file paths
$pages = [
    'login'            => 'pages/auth/login.php',
    'forgot_password'  => 'pages/auth/forgot_password.php',
    'change_password'  => 'pages/auth/change_password.php',
    
    // User/Admin Pages
    'dashboard'        => 'pages/dashboard.php',
    'pos'              => 'pages/pos.php',
    'products'         => 'pages/products.php',
    'customers'        => 'pages/customers.php',
    'suppliers'        => 'pages/suppliers.php',
    'returns'          => 'pages/returns.php',
    'expenses'         => 'pages/expenses.php',
    'reports'          => 'pages/reports.php',
    'users'            => 'pages/users.php',
    'settings'         => 'pages/settings.php',
    'tools'            => 'pages/tools.php',
    'help'             => 'pages/help.php',

    // Super Admin Pages
    'super_dashboard'  => 'pages/super_admin/dashboard.php',
    'manage_companies' => 'pages/super_admin/companies.php',
    'system_settings'  => 'pages/super_admin/settings.php',
];

// Access Control
$userRole = isset($_SESSION['user_role']) ? $_SESSION['user_role'] : 'user';
$superAdminOnly = ['super_dashboard', 'manage_companies', 'system_settings'];
$tenantOnly = ['dashboard', 'pos', 'products', 'customers', 'suppliers', 'returns', 'expenses', 'reports', 'users', 'settings'];

// 1. Enforce super admin vs tenant boundaries
if ($userRole === 'super_admin' && in_array($page, $tenantOnly)) {
    $page = 'super_dashboard';
} elseif ($userRole !== 'super_admin' && in_array($page, $superAdminOnly)) {
    $page = 'dashboard';
}

// 2. Enforce specific menu permissions for logged-in tenants
if ($userRole !== 'super_admin' && !$isAuthPage && isset($_SESSION['user_permissions'])) {
    $perms = $_SESSION['user_permissions'];
    
    // Check if the user is trying to access a page they don't have permission for
    $isDenied = false;
    if ($page === 'dashboard' && empty($perms['dashboard'])) $isDenied = true;
    if ($page === 'pos' && empty($perms['pos'])) $isDenied = true;
    if ($page === 'products' && empty($perms['products'])) $isDenied = true;
    if ($page === 'customers' && empty($perms['customers'])) $isDenied = true;
    if ($page === 'suppliers' && empty($perms['suppliers'])) $isDenied = true;
    if ($page === 'returns' && empty($perms['returns'])) $isDenied = true;
    if ($page === 'expenses' && empty($perms['expenses'])) $isDenied = true;
    if ($page === 'reports' && empty($perms['reports'])) $isDenied = true;
    if ($page === 'users' && empty($perms['users'])) $isDenied = true;
    if ($page === 'settings' && empty($perms['settings'])) $isDenied = true;
    
    // If denied, find the first available page they DO have permission for
    if ($isDenied) {
        $page = 'login'; // fallback
        $menuOrder = ['dashboard', 'pos', 'products', 'customers', 'suppliers', 'returns', 'expenses', 'reports', 'users', 'settings'];
        foreach ($menuOrder as $menuPage) {
            if (!empty($perms[$menuPage])) {
                $page = $menuPage;
                break;
            }
        }
    }
}

// Resolve the page file
$pageFile = isset($pages[$page]) ? $pages[$page] : $pages['login'];

if ($isAuthPage) {
    // Auth pages render standalone (no sidebar)
    include $pageFile;
} else {
    // App layout with sidebar
    require_once 'includes/db.php';
    require_once 'includes/queries.php';
    include 'includes/header.php';
    echo '<div class="app-wrapper">';
    include 'includes/sidebar.php';
    echo '<div class="main-content fade-in">';
    include $pageFile;
    echo '</div></div>';
    include 'includes/footer.php';
}
?>
