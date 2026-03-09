<?php
// Determine the active page for nav highlighting
$currentPage = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
$userRole = isset($_SESSION['user_role']) ? $_SESSION['user_role'] : 'user';
$userName = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'User';
$companyId = isset($_SESSION['company_id']) ? $_SESSION['company_id'] : null;

$companyName = 'Vendora';
$companyLogo = 'assets/image/logo.png'; // Default

if ($companyId && $userRole !== 'super_admin') {
    require_once 'includes/queries.php';
    $company = getCompanyDetails($companyId);
    if ($company) {
        $companyName = $company['name'];
        if (!empty($company['logo'])) {
            $companyLogo = 'assets/image/logos/' . $company['logo'];
        }
    }
}
?>

<!-- Sidebar Backdrop (mobile) -->
<div class="sidebar-backdrop" id="sidebarBackdrop"></div>

<!-- Sidebar -->
<aside class="sidebar" id="sidebar">
    <!-- Logo / Brand -->
    <div class="sidebar-header">
        <div class="sidebar-logo">
            <img src="<?= $companyLogo ?>" alt="Company Logo" style="height: 40px; width: auto; max-width: 100%; border-radius: 4px;">
        </div>
        <div class="sidebar-brand">
            <h2><?= $userRole === 'super_admin' ? 'Super Admin' : htmlspecialchars($companyName) ?></h2>
            <span><?= $userRole === 'super_admin' ? 'Control Panel' : 'Retail POS' ?></span>
        </div>
    </div>


    <!-- Navigation -->
    <nav class="sidebar-nav">

        <?php if ($userRole === 'super_admin'): ?>
            <!-- SUPER ADMIN MENU -->
            <div class="nav-section">
                <div class="nav-section-title">Global Management</div>
                <a href="?page=super_dashboard" class="nav-item <?= $currentPage === 'super_dashboard' ? 'active' : '' ?>">
                    <i class="fa-solid fa-gauge-high"></i>
                    Super Dashboard
                </a>
                <a href="?page=manage_companies" class="nav-item <?= $currentPage === 'manage_companies' ? 'active' : '' ?>">
                    <i class="fa-solid fa-building"></i>
                    Manage Companies
                </a>
                <a href="?page=system_settings" class="nav-item <?= $currentPage === 'system_settings' ? 'active' : '' ?>">
                    <i class="fa-solid fa-microchip"></i>
                    System Settings
                </a>
            </div>
        <?php else: 
            // Load user permissions from session for non-super admins
            $perms = isset($_SESSION['user_permissions']) ? $_SESSION['user_permissions'] : [];
        ?>
            <!-- Dashboard -->
            <?php if (isset($perms['dashboard']) && $perms['dashboard']): ?>
            <div class="nav-section">
                <a href="?page=dashboard" class="nav-item <?= $currentPage === 'dashboard' ? 'active' : '' ?>">
                    <i class="fa-solid fa-grid-2"></i>
                    Dashboard
                </a>
            </div>
            <?php endif; ?>

            <!-- SALES -->
            <?php if (isset($perms['pos']) && $perms['pos']): ?>
            <div class="nav-section">
                <div class="nav-section-title">Sales</div>
                <a href="?page=pos" class="nav-item <?= $currentPage === 'pos' ? 'active' : '' ?>">
                    <i class="fa-solid fa-cash-register"></i>
                    Point of Sale
                </a>
            </div>
            <?php endif; ?>

            <!-- INVENTORY -->
            <?php if (isset($perms['products']) && $perms['products']): ?>
            <div class="nav-section">
                <div class="nav-section-title">Inventory</div>
                <a href="?page=products" class="nav-item <?= $currentPage === 'products' ? 'active' : '' ?>">
                    <i class="fa-solid fa-tags"></i>
                    Products
                </a>
            </div>
            <?php endif; ?>

            <!-- PEOPLE -->
            <?php if ((isset($perms['customers']) && $perms['customers']) || (isset($perms['suppliers']) && $perms['suppliers'])): ?>
            <div class="nav-section">
                <div class="nav-section-title">People</div>
                <?php if (isset($perms['customers']) && $perms['customers']): ?>
                <a href="?page=customers" class="nav-item <?= $currentPage === 'customers' ? 'active' : '' ?>">
                    <i class="fa-solid fa-users"></i>
                    Customers
                </a>
                <?php endif; ?>
                <?php if (isset($perms['suppliers']) && $perms['suppliers']): ?>
                <a href="?page=suppliers" class="nav-item <?= $currentPage === 'suppliers' ? 'active' : '' ?>">
                    <i class="fa-solid fa-truck-field"></i>
                    Suppliers
                </a>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <!-- TRANSACTIONS -->
            <?php if ((isset($perms['returns']) && $perms['returns']) || (isset($perms['expenses']) && $perms['expenses'])): ?>
            <div class="nav-section">
                <div class="nav-section-title">Transactions</div>
                <?php if (isset($perms['returns']) && $perms['returns']): ?>
                <a href="?page=returns" class="nav-item <?= $currentPage === 'returns' ? 'active' : '' ?>">
                    <i class="fa-solid fa-rotate-left"></i>
                    Returns & Refunds
                </a>
                <?php endif; ?>
                <?php if (isset($perms['expenses']) && $perms['expenses']): ?>
                <a href="?page=expenses" class="nav-item <?= $currentPage === 'expenses' ? 'active' : '' ?>">
                    <i class="fa-solid fa-receipt"></i>
                    Expenses
                </a>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <!-- REPORTS -->
            <?php if (isset($perms['reports']) && $perms['reports']): ?>
            <div class="nav-section">
                <div class="nav-section-title">Analytics</div>
                <a href="?page=reports" class="nav-item <?= $currentPage === 'reports' ? 'active' : '' ?>">
                    <i class="fa-solid fa-chart-pie"></i>
                    Reports
                </a>
            </div>
            <?php endif; ?>

            <!-- MANAGEMENT -->
            <?php if ((isset($perms['users']) && $perms['users']) || (isset($perms['settings']) && $perms['settings'])): ?>
            <div class="nav-section">
                <div class="nav-section-title">Management</div>
                <?php if (isset($perms['users']) && $perms['users']): ?>
                <a href="?page=users" class="nav-item <?= $currentPage === 'users' ? 'active' : '' ?>">
                    <i class="fa-solid fa-user-shield"></i>
                    Users & Roles
                </a>
                <?php endif; ?>
                <?php if (isset($perms['settings']) && $perms['settings']): ?>
                <a href="?page=settings" class="nav-item <?= $currentPage === 'settings' ? 'active' : '' ?>">
                    <i class="fa-solid fa-gear"></i>
                    Settings
                </a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        <?php endif; ?>

        <!-- SYSTEM -->
        <div class="nav-section">
            <div class="nav-section-title">System</div>
            <a href="?page=tools" class="nav-item <?= $currentPage === 'tools' ? 'active' : '' ?>">
                <i class="fa-solid fa-toolbox"></i>
                System Tools
            </a>
            <a href="?page=help" class="nav-item <?= $currentPage === 'help' ? 'active' : '' ?>">
                <i class="fa-solid fa-circle-question"></i>
                Help & Info
            </a>
        </div>

    </nav>

    <!-- User / Profile / Logout -->
    <div class="sidebar-footer">
        <div class="sidebar-user" id="profileToggle">
            <div class="sidebar-user-avatar" id="sidebarAvatar">
                <?php 
                $avatar = isset($_SESSION['user_avatar']) ? $_SESSION['user_avatar'] : null;
                if ($avatar): ?>
                    <img src="assets/image/avatars/<?= $avatar ?>" alt="Avatar" style="width:100%; height:100%; border-radius:inherit; object-fit:cover;">
                <?php else: ?>
                    <?= strtoupper(substr($userName, 0, 2)) ?>
                <?php endif; ?>
            </div>
            <div class="sidebar-user-info">
                <div class="name"><?= htmlspecialchars($userName) ?></div>
                <div class="role"><?= ucfirst($userRole) ?></div>
            </div>
            <div class="profile-actions-trigger">
                <i class="fa-solid fa-chevron-up"></i>
            </div>
        </div>
        
        <!-- Profile Dropup Menu -->
        <div class="profile-dropup" id="profileDropup">
            <a href="javascript:void(0)" onclick="openProfileModal()">
                <i class="fa-solid fa-user-gear"></i> Profile Settings
            </a>
            <div class="divider"></div>
            <a href="controllers/AuthController.php?action=logout" style="color: var(--danger);">
                <i class="fa-solid fa-right-from-bracket"></i> Logout
            </a>
        </div>
    </div>
</aside>

<!-- User Profile Modal -->
<div class="modal fade-in" id="profileModal" style="display:none; z-index: 2000;">
    <div class="modal-content" style="max-width: 500px;">
        <div class="modal-header">
            <h2 id="profileModalTitle"><i class="fa-solid fa-user-circle mr-8"></i> My Profile</h2>
            <button class="btn-icon" onclick="closeProfileModal()"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="modal-body p-24">
            <form id="profileForm" enctype="multipart/form-data">
                <input type="hidden" name="action" value="update_profile">
                
                <div class="text-center mb-24">
                    <div class="profile-avatar-container" style="position:relative; width:100px; height:100px; margin: 0 auto 16px;">
                        <div id="modalAvatarPreview" style="width:100px; height:100px; border-radius:50%; background:var(--primary-100); color:var(--primary-500); display:flex; align-items:center; justify-content:center; font-size:2rem; font-weight:700; overflow:hidden; border:3px solid var(--white); box-shadow:var(--shadow-md);">
                            <?php if ($avatar): ?>
                                <img src="assets/image/avatars/<?= $avatar ?>" style="width:100%; height:100%; object-fit:cover;">
                            <?php else: ?>
                                <?= strtoupper(substr($userName, 0, 2)) ?>
                            <?php endif; ?>
                        </div>
                        <label for="avatarInput" class="avatar-edit-btn" style="position:absolute; bottom:0; right:0; width:32px; height:32px; border-radius:50%; background:var(--primary-500); color:white; display:flex; align-items:center; justify-content:center; cursor:pointer; border:2px solid var(--white); box-shadow:var(--shadow-sm);">
                            <i class="fa-solid fa-camera" style="font-size:0.8rem;"></i>
                        </label>
                        <input type="file" id="avatarInput" name="avatar" hidden accept="image/*">
                    </div>
                </div>

                <div class="floating-group">
                    <input type="text" name="name" class="floating-control" value="<?= htmlspecialchars($userName) ?>" placeholder=" " required>
                    <label class="floating-label">Full Name</label>
                </div>

                <div class="floating-group">
                    <input type="email" name="email" class="floating-control" value="<?= isset($_SESSION['user_email']) ? htmlspecialchars($_SESSION['user_email']) : '' ?>" placeholder=" ">
                    <label class="floating-label">Email Address</label>
                </div>

                <div class="divider mb-16 mt-8"></div>
                <p class="text-muted mb-16" style="font-size:0.8rem;"><i class="fa-solid fa-lock mr-4"></i> Change Password (leave blank to keep current)</p>

                <div class="floating-group">
                    <input type="password" name="new_password" class="floating-control" placeholder=" ">
                    <label class="floating-label">New Password</label>
                </div>

                <div class="modal-footer pt-16 px-0 pb-0">
                    <button type="button" class="btn btn-outline" onclick="closeProfileModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="saveProfileBtn">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Profile Dropup Logic
$(document).on('click', '#profileToggle', function(e) {
    e.stopPropagation();
    $('#profileDropup').toggleClass('show');
});

$(document).on('click', function() {
    $('#profileDropup').removeClass('show');
});

function openProfileModal() {
    $('#profileModal').fadeIn(200).css('display', 'flex');
    $('#profileDropup').removeClass('show');
}

function closeProfileModal() {
    $('#profileModal').fadeOut(200);
}

// Avatar Preview
$('#avatarInput').on('change', function() {
    const file = this.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            $('#modalAvatarPreview').html(`<img src="${e.target.result}" style="width:100%; height:100%; object-fit:cover;">`);
        }
        reader.readAsDataURL(file);
    }
});

// Profile Form Submit
$('#profileForm').on('submit', function(e) {
    e.preventDefault();
    const btn = $('#saveProfileBtn');
    btn.html('<i class="fas fa-spinner fa-spin mr-8"></i> Saving...').prop('disabled', true);

    const formData = new FormData(this);
    
    $.ajax({
        url: 'controllers/UserController.php',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function(response) {
            if (response.status === 'success') {
                showToast('success', response.message);
                setTimeout(() => location.reload(), 1500);
            } else {
                showToast('error', response.message);
                btn.html('Save Changes').prop('disabled', false);
            }
        },
        error: function() {
            showToast('error', 'Something went wrong. Please try again.');
            btn.html('Save Changes').prop('disabled', false);
        }
    });
});
</script>

<style>
.profile-dropup {
    position: absolute;
    bottom: 85px;
    left: 20px;
    right: 20px;
    background: var(--white);
    border: 1px solid var(--border-light);
    border-radius: 12px;
    box-shadow: var(--shadow-lg);
    display: none;
    flex-direction: column;
    overflow: hidden;
    z-index: 100;
}
.profile-dropup.show {
    display: flex;
}
.profile-dropup a {
    padding: 12px 16px;
    font-size: 0.85rem;
    font-weight: 600;
    color: var(--text-primary);
    display: flex;
    align-items: center;
    gap: 12px;
    transition: all 0.2s;
}
.profile-dropup a:hover {
    background: var(--primary-50);
    color: var(--primary-600);
}
.profile-dropup .divider {
    height: 1px;
    background: var(--border-light);
    margin: 4px 0;
}
.sidebar-user {
    cursor: pointer;
    transition: background 0.2s;
}
.sidebar-user:hover {
    background: rgba(26, 138, 124, 0.05);
}
.profile-actions-trigger {
    margin-left: auto;
    color: var(--text-muted);
    font-size: 0.8rem;
}
.avatar-edit-btn:hover {
    background: var(--primary-600) !important;
    transform: scale(1.1);
}
</style>

