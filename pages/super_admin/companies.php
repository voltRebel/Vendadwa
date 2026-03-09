<?php
/**
 * Manage Companies
 * List, Add, and Edit tenants.
 */
?>

<?php 
require_once 'includes/queries.php';

// Fetch Stats
$totalStmt = $pdo->query("SELECT COUNT(*) FROM companies");
$totalCompanies = $totalStmt->fetchColumn();

$activeStmt = $pdo->query("SELECT COUNT(*) FROM companies WHERE status = 'active'");
$activeCompanies = $activeStmt->fetchColumn();

$inactiveStmt = $pdo->query("SELECT COUNT(*) FROM companies WHERE status = 'inactive'");
$inactiveCompanies = $inactiveStmt->fetchColumn();
?>

<!-- Top Bar -->
<div class="top-bar">
    <div class="top-bar-left">
        <h1>Company Management</h1>
    </div>
    <div class="top-bar-right">
        <button class="btn btn-primary" id="addCompanyBtn">
            <i class="fa-solid fa-plus"></i> Add New Company
        </button>
    </div>
</div>

<!-- Stats Grid -->
<div class="stat-grid mb-24" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px;">
    <div class="stat-card glass-card">
        <div class="stat-icon pink">
            <i class="fa-solid fa-building"></i>
        </div>
        <div class="stat-info">
            <h3><?= $totalCompanies ?></h3>
            <p>Total Companies</p>
        </div>
    </div>
    <div class="stat-card glass-card">
        <div class="stat-icon green">
            <i class="fa-solid fa-circle-check"></i>
        </div>
        <div class="stat-info">
            <h3><?= $activeCompanies ?></h3>
            <p>Active Platforms</p>
        </div>
    </div>
    <div class="stat-card glass-card">
        <div class="stat-icon gold">
            <i class="fa-solid fa-circle-xmark"></i>
        </div>
        <div class="stat-info">
            <h3><?= $inactiveCompanies ?></h3>
            <p>Inactive/Locked</p>
        </div>
    </div>
</div>

<!-- Company List -->
<div class="glass-card">
    <div class="d-flex align-center justify-between mb-16">
        <div class="search-bar">
            <i class="fa-solid fa-magnifying-glass search-icon"></i>
            <input type="text" placeholder="Filter companies...">
        </div>
    </div>

    <div class="table-container">
        <table class="data-table" id="companiesTable">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Company Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Address</th>
                    <th>Users</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $stmt = $pdo->query("SELECT c.*, (SELECT COUNT(*) FROM users u WHERE u.company_id = c.id) as user_count FROM companies c ORDER BY c.created_at DESC");
                $i = 1;
                while ($company = $stmt->fetch()): 
                    $logoSrc = $company['logo'] ? 'assets/image/logos/' . $company['logo'] : 'assets/image/logo.png';
                    $status = $company['status'] === 'active';
                ?>
                <tr>
                    <td><?= $i++ ?></td>
                    <td class="d-flex align-center gap-12">
                        <img src="<?= $logoSrc ?>" alt="Logo" style="width: 32px; height: 32px; border-radius: 50%; object-fit: cover; border: 1px solid var(--border-light);">
                        <span class="fw-600"><?= htmlspecialchars($company['name']) ?></span>
                    </td>
                    <td><?= htmlspecialchars($company['email']) ?></td>
                    <td><?= htmlspecialchars($company['phone'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($company['address'] ?? '-') ?></td>
                    <td><span class="badge badge-teal"><?= $company['user_count'] ?></span></td>
                    <td><span class="badge badge-<?= $status ? 'success' : 'danger' ?>"><?= ucfirst($company['status']) ?></span></td>
                    <td>
                        <div class="d-flex gap-8">
                            <button class="btn btn-sm btn-secondary view-company" data-id="<?= $company['id'] ?>" title="View"><i class="fa-solid fa-eye" style="color: var(--info);"></i></button>
                            <button class="btn btn-sm btn-secondary edit-company" data-id="<?= $company['id'] ?>" title="Edit"><i class="fa-solid fa-pen-to-square" style="color: var(--primary-500);"></i></button>
                            <button class="btn btn-sm btn-secondary toggle-status" data-id="<?= $company['id'] ?>" data-status="<?= $company['status'] ?>" title="<?= $status ? 'Lock' : 'Unlock' ?>">
                                <i class="fa-solid fa-<?= $status ? 'lock' : 'lock-open' ?>" style="color: var(--warning);"></i>
                            </button>
                            <button class="btn btn-sm btn-secondary delete-company" data-id="<?= $company['id'] ?>" title="Delete"><i class="fa-solid fa-trash" style="color: var(--danger);"></i></button>
                        </div>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add Company Modal -->
<div class="modal-overlay" id="companyModal">
    <div class="modal">
        <div class="modal-header">
            <h3 id="modalTitle">🏢 Add New Company</h3>
            <button class="modal-close">&times;</button>
        </div>
        <form id="companyForm" enctype="multipart/form-data">
            <div class="floating-group">
                <input type="text" name="name" class="floating-control" required placeholder=" ">
                <label class="floating-label">Company Name</label>
            </div>
            <div class="floating-group">
                <input type="email" name="email" class="floating-control" required placeholder=" ">
                <label class="floating-label">Company Email</label>
            </div>
            <div class="floating-group">
                <input type="text" name="phone" class="floating-control" required placeholder=" ">
                <label class="floating-label">Phone Number</label>
            </div>
            <div class="floating-group">
                <input type="text" name="address" class="floating-control" required placeholder=" ">
                <label class="floating-label">Business Address</label>
            </div>
            <div class="form-group" style="margin-bottom: 24px;">
                <label class="form-label" style="font-size: 0.8rem; color: var(--text-muted); display: block; margin-bottom: 6px;">Company Logo (Optional)</label>
                <input type="file" name="logo" class="form-control" accept="image/*" style="padding: 10px;">
                <small class="text-muted" style="display: block; margin-top: 4px;">Uses default logo if empty.</small>
            </div>
            <div class="floating-group">
                <input type="text" name="username" class="floating-control" required placeholder=" ">
                <label class="floating-label">Admin Username</label>
            </div>
            <div class="floating-group">
                <input type="password" name="admin_password" id="adminPasswordInput" class="floating-control" required placeholder=" ">
                <label class="floating-label">Admin Password</label>
                <small class="text-muted pwd-hint" style="display: none; position: absolute; bottom: -18px; left: 0; font-size: 0.75rem;">Leave blank to keep current password.</small>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary modal-cancel">Cancel</button>
                <button type="submit" class="btn btn-primary">Create Company & Admin</button>
            </div>
        </form>
    </div>
</div>

<!-- View Company Modal -->
<div class="modal-overlay" id="viewCompanyModal">
    <div class="modal modal-lg">
        <div class="modal-header">
            <h3>🏢 Company Profile Details</h3>
            <button class="modal-close" onclick="$('#viewCompanyModal').removeClass('active')">&times;</button>
        </div>
        <div class="modal-body p-24">
            <div class="d-flex align-center gap-24 mb-24 pb-24" style="border-bottom: 1px solid var(--border-light);">
                <div class="view-logo-wrapper">
                    <img id="viewCompanyLogo" src="assets/image/logo.png" alt="Company Logo" style="width: 80px; height: 80px; border-radius: 50%; object-fit: cover; border: 2px solid var(--primary-100);">
                </div>
                <div>
                    <h2 id="viewCompanyName" class="mb-4">Company Name</h2>
                    <span id="viewCompanyStatus" class="badge">Active</span>
                </div>
            </div>

            <table class="details-table">
                <tr>
                    <th width="35%">Business Email</th>
                    <td id="viewCompanyEmail">-</td>
                </tr>
                <tr>
                    <th>Phone Number</th>
                    <td id="viewCompanyPhone">-</td>
                </tr>
                <tr>
                    <th>Business Address</th>
                    <td id="viewCompanyAddress">-</td>
                </tr>
                <tr>
                    <th colspan="2" style="background: var(--bg-light); padding: 8px 16px; font-weight: 700; color: var(--primary-600);">Admin Credentials</th>
                </tr>
                <tr>
                    <th>Admin Username</th>
                    <td id="viewAdminUsername" class="fw-700 color-primary">-</td>
                </tr>
            </table>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="$('#viewCompanyModal').removeClass('active')">Close Details</button>
        </div>
    </div>
</div>

<style>
.details-table {
    width: 100%;
    border-collapse: collapse;
}
.details-table th, .details-table td {
    padding: 14px 16px;
    border-bottom: 1px solid var(--border-light);
    text-align: left;
}
.details-table th {
    background: rgba(26, 138, 124, 0.03);
    font-weight: 600;
    color: var(--text-muted);
}
.color-primary { color: var(--primary-600); }
</style>

<script>
$(document).ready(function() {
    const modal = $('#companyModal');
    const form = $('#companyForm');
    const modalTitle = $('#modalTitle');
    const submitBtn = form.find('button[type="submit"]');

    // Add Company Click - Reset Form
    $('#addCompanyBtn').on('click', function() {
        form[0].reset();
        form.find('input[name="id"]').remove();
        form.find('input, select, textarea').prop('disabled', false); // Enable inputs
        submitBtn.show(); // Show submit
        form.find('.floating-group').show(); // Show all groups
        form.find('input[name="admin_password"]').attr('required', true); // Password required for NEW
        modal.find('.pwd-hint').hide();
        modalTitle.text('🏢 Add New Company');
        submitBtn.text('Create Company & Admin');
        modal.addClass('active');
    });

    // Close Modal
    $('.modal-close, .modal-cancel').on('click', function() {
        modal.removeClass('active');
    });

    // Edit Company Click
    $(document).on('click', '.edit-company', function() {
        const id = $(this).data('id');
        $.post('controllers/SuperAdminController.php', { action: 'get_company', id: id }, function(response) {
            if (response.status === 'success') {
                const data = response.data;
                form[0].reset();
                form.find('input[name="id"]').remove();
                form.append(`<input type="hidden" name="id" value="${data.id}">`);
                form.find('input, select, textarea').prop('disabled', false); // Enable inputs
                submitBtn.show(); // Show submit
                
                // Populate fields
                form.find('input[name="name"]').val(data.name).trigger('change');
                form.find('input[name="email"]').val(data.email).trigger('change');
                form.find('input[name="phone"]').val(data.phone).trigger('change');
                form.find('input[name="address"]').val(data.address).trigger('change');
                
                // Show username/password for edit
                form.find('input[name="username"]').closest('.floating-group').show();
                form.find('input[name="username"]').val(data.admin_username || '').trigger('change');
                
                form.find('input[name="admin_password"]').closest('.floating-group').show();
                form.find('input[name="admin_password"]').attr('required', false); // Password OPTIONAL for edit
                modal.find('.pwd-hint').show();
                
                modalTitle.text('📝 Edit Company');
                submitBtn.text('Update Company');
                modal.addClass('active');
            }
        }, 'json');
    });

    // View Company Click
    $(document).on('click', '.view-company', function() {
        const id = $(this).data('id');
        const viewModal = $('#viewCompanyModal');
        
        $.post('controllers/SuperAdminController.php', { action: 'get_company', id: id }, function(response) {
            if (response.status === 'success') {
                const data = response.data;
                
                // Set Header Info
                $('#viewCompanyName').text(data.name);
                $('#viewCompanyLogo').attr('src', data.logo ? 'assets/image/logos/' + data.logo : 'assets/image/logo.png');
                
                const statusBadge = $('#viewCompanyStatus');
                statusBadge.text(data.status.toUpperCase());
                statusBadge.removeClass('badge-success badge-danger')
                           .addClass(data.status === 'active' ? 'badge-success' : 'badge-danger');

                // Set Table Info
                $('#viewCompanyEmail').text(data.email);
                $('#viewCompanyPhone').text(data.phone || '-');
                $('#viewCompanyAddress').text(data.address || '-');
                
                // Admin Info
                $('#viewAdminUsername').text(data.admin_username || '-');
                
                viewModal.addClass('active');
            }
        }, 'json');
    });

    // Toggle Status (Lock/Unlock)
    $(document).on('click', '.toggle-status', function() {
        const btn = $(this);
        const id = btn.data('id');
        const status = btn.data('status');
        const actionText = status === 'active' ? 'Lock' : 'Unlock';

        Swal.fire({
            title: `${actionText} Company?`,
            text: `This will ${status === 'active' ? 'disable' : 'enable'} access for all users of this company.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: `Yes, ${actionText} it!`
        }).then((result) => {
            if (result.isConfirmed) {
                $.post('controllers/SuperAdminController.php', { action: 'toggle_status', id: id, status: status }, function(res) {
                    if (res.status === 'success') {
                        Swal.fire('Updated!', res.message, 'success').then(() => location.reload());
                    }
                }, 'json');
            }
        });
    });

    // Delete Company
    $(document).on('click', '.delete-company', function() {
        const id = $(this).data('id');
        Swal.fire({
            title: 'Delete Company?',
            text: "This action is permanent and will delete all associated data!",
            icon: 'error',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Yes, DELETE it'
        }).then((result) => {
            if (result.isConfirmed) {
                $.post('controllers/SuperAdminController.php', { action: 'delete_company', id: id }, function(res) {
                    if (res.status === 'success') {
                        Swal.fire('Deleted!', res.message, 'success').then(() => location.reload());
                    }
                }, 'json');
            }
        });
    });

    // Form Submit (Create or Update)
    form.on('submit', function(e) {
        e.preventDefault();
        
        const isUpdate = form.find('input[name="id"]').length > 0;
        submitBtn.html('<i class="fas fa-spinner fa-spin"></i> Processing...').attr('disabled', true);

        const formData = new FormData(this);
        formData.append('action', isUpdate ? 'update_company' : 'create_company');

        $.ajax({
            url: 'controllers/SuperAdminController.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    modal.removeClass('active');
                    Swal.fire('Success', response.message, 'success').then(() => location.reload());
                } else {
                    Swal.fire('Error', response.message, 'error');
                    submitBtn.html(isUpdate ? 'Update Company' : 'Create Company & Admin').attr('disabled', false);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', status, error);
                console.error('Response Text:', xhr.responseText);
                Swal.fire('Error', 'Communication failed. Check console for details.', 'error');
                submitBtn.html(isUpdate ? 'Update Company' : 'Create Company & Admin').attr('disabled', false);
            }
        });
    });

    // Helper to initialize DataTables
    function initDataTable(selector, options = {}) {
        if ($.fn.DataTable.isDataTable(selector)) {
            $(selector).DataTable().clear().destroy();
        }
        $(selector).DataTable($.extend({
            pageLength: 10,
            ordering: false,
            responsive: true,
            language: { search: "", searchPlaceholder: "Search..." }
        }, options));
    }

    initDataTable('#companiesTable');
});
</script>
