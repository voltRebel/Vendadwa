<?php
$companyId = $_SESSION['company_id'];
$company = getCompanyDetails($companyId);
$branches = getBranches($companyId);
?>

<!-- Top Bar -->
<div class="top-bar">
    <div class="top-bar-left">
        <button class="mobile-menu-toggle" id="mobileMenuToggle"><i class="fa-solid fa-bars"></i></button>
        <h1>Settings</h1>
    </div>
    <div class="top-bar-right">
        <button class="btn btn-primary" id="saveAllSettings"><i class="fa-solid fa-floppy-disk"></i> Save All</button>
    </div>
</div>

<form id="settingsForm">
    <input type="hidden" name="action" value="save_settings">
    
    <!-- Business Profile -->
    <div class="settings-section">
        <div class="settings-section-header open">
            <h3><i class="fa-solid fa-store text-pink"></i> Business Profile</h3>
            <i class="fa-solid fa-chevron-down toggle-icon"></i>
        </div>
        <div class="settings-section-body open">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                <div class="form-group"><label class="form-label">Business Name</label><input type="text" name="business_name" class="form-control" value="<?php echo htmlspecialchars($company['name'] ?? ''); ?>"></div>
                <div class="form-group"><label class="form-label">Phone</label><input type="tel" name="phone" class="form-control" value="<?php echo htmlspecialchars($company['phone'] ?? ''); ?>"></div>
                <div class="form-group"><label class="form-label">Email</label><input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($company['email'] ?? ''); ?>"></div>
                <div class="form-group"><label class="form-label">Website</label><input type="url" name="website" class="form-control" value="<?php echo htmlspecialchars($company['website'] ?? ''); ?>"></div>
            </div>
            <div class="form-group"><label class="form-label">Address</label><textarea name="address" class="form-control" style="min-height:60px;"><?php echo htmlspecialchars($company['address'] ?? ''); ?></textarea></div>
        </div>
    </div>

    <!-- Branch / Store -->
    <div class="settings-section">
        <div class="settings-section-header">
            <h3><i class="fa-solid fa-map-location-dot text-purple"></i> Branch / Store Setup</h3>
            <i class="fa-solid fa-chevron-down toggle-icon"></i>
        </div>
        <div class="settings-section-body">
            <div class="table-container mb-16">
                <table class="data-table" id="branchesTable">
                    <thead><tr><th>#</th><th>Branch</th><th>Address</th><th>Status</th><th>Actions</th></tr></thead>
                    <tbody>
                        <?php if (empty($branches)): ?>
                            <tr class="empty-row"><td colspan="5" class="text-center">No branches found.</td></tr>
                        <?php else: foreach ($branches as $index => $branch): ?>
                            <tr data-id="<?php echo $branch['id']; ?>">
                                <td><?php echo $index + 1; ?></td>
                                <td class="fw-600"><?php echo htmlspecialchars($branch['name']); ?></td>
                                <td><?php echo htmlspecialchars($branch['address']); ?></td>
                                <td><span class="badge <?php echo $branch['status'] === 'active' ? 'badge-success' : 'badge-danger'; ?>"><?php echo ucfirst($branch['status']); ?></span></td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-secondary edit-branch" data-id="<?php echo $branch['id']; ?>" data-name="<?php echo htmlspecialchars($branch['name']); ?>" data-address="<?php echo htmlspecialchars($branch['address']); ?>" data-status="<?php echo $branch['status']; ?>"><i class="fa-solid fa-pen"></i></button>
                                    <button type="button" class="btn btn-sm btn-danger delete-branch" data-id="<?php echo $branch['id']; ?>"><i class="fa-solid fa-trash"></i></button>
                                </td>
                            </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
            <button type="button" class="btn btn-sm btn-outline" id="addBranchBtn"><i class="fa-solid fa-plus"></i> Add Branch</button>
        </div>
    </div>

    <!-- Tax Settings -->
    <div class="settings-section">
        <div class="settings-section-header">
            <h3><i class="fa-solid fa-percent text-pink"></i> Tax Settings</h3>
            <i class="fa-solid fa-chevron-down toggle-icon"></i>
        </div>
        <div class="settings-section-body">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                <div class="form-group">
                    <label class="form-label">Tax Name</label>
                    <input type="text" name="tax_name" class="form-control" value="<?php echo htmlspecialchars($company['tax_name'] ?? 'Sales Tax'); ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Tax Rate (%)</label>
                    <input type="number" name="tax_rate" class="form-control" value="<?php echo htmlspecialchars($company['tax_rate'] ?? '10.00'); ?>" step="0.01">
                </div>
            </div>
            <div class="form-group">
                <label class="form-label d-flex align-center gap-8">
                    <label class="toggle-switch"><input type="checkbox" name="tax_enabled" <?php echo ($company['tax_enabled'] ?? 1) ? 'checked' : ''; ?>><span class="toggle-slider"></span></label>
                    Enable tax on all sales
                </label>
            </div>
            <div class="form-group">
                <label class="form-label d-flex align-center gap-8">
                    <label class="toggle-switch"><input type="checkbox" name="tax_included" <?php echo ($company['tax_included'] ?? 0) ? 'checked' : ''; ?>><span class="toggle-slider"></span></label>
                    Tax included in product price
                </label>
            </div>
        </div>
    </div>

    <!-- Payment Methods -->
    <div class="settings-section">
        <div class="settings-section-header">
            <h3><i class="fa-solid fa-credit-card text-purple"></i> Payment Methods</h3>
            <i class="fa-solid fa-chevron-down toggle-icon"></i>
        </div>
        <div class="settings-section-body">
            <div class="table-container mb-16">
                <table class="data-table">
                    <thead><tr><th>#</th><th>Method</th><th>Status</th><th>Toggle</th></tr></thead>
                    <tbody>
                        <tr><td>1</td><td class="fw-600"><i class="fa-solid fa-money-bill text-success"></i> &nbsp;Cash</td><td><span class="badge badge-success">Active</span></td><td><label class="toggle-switch"><input type="checkbox" name="payment_cash" <?php echo ($company['payment_cash'] ?? 1) ? 'checked' : ''; ?>><span class="toggle-slider"></span></label></td></tr>
                        <tr><td>2</td><td class="fw-600"><i class="fa-solid fa-credit-card text-info"></i> &nbsp;Card</td><td><span class="badge badge-success">Active</span></td><td><label class="toggle-switch"><input type="checkbox" name="payment_card" <?php echo ($company['payment_card'] ?? 1) ? 'checked' : ''; ?>><span class="toggle-slider"></span></label></td></tr>
                        <tr><td>3</td><td class="fw-600"><i class="fa-solid fa-mobile text-purple"></i> &nbsp;Mobile Payment</td><td><span class="badge badge-success">Active</span></td><td><label class="toggle-switch"><input type="checkbox" name="payment_mobile" <?php echo ($company['payment_mobile'] ?? 1) ? 'checked' : ''; ?>><span class="toggle-slider"></span></label></td></tr>
                        <tr><td>4</td><td class="fw-600"><i class="fa-solid fa-money-check text-pink"></i> &nbsp;Bank Transfer</td><td><span class="badge badge-warning">Optional</span></td><td><label class="toggle-switch"><input type="checkbox" name="payment_bank" <?php echo ($company['payment_bank'] ?? 0) ? 'checked' : ''; ?>><span class="toggle-slider"></span></label></td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Receipt Settings -->
    <div class="settings-section">
        <div class="settings-section-header">
            <h3><i class="fa-solid fa-print text-pink"></i> Receipt Settings</h3>
            <i class="fa-solid fa-chevron-down toggle-icon"></i>
        </div>
        <div class="settings-section-body">
            <div class="form-group"><label class="form-label">Receipt Header</label><textarea name="receipt_header" class="form-control" style="min-height:60px;"><?php echo htmlspecialchars($company['receipt_header'] ?? ''); ?></textarea></div>
            <div class="form-group"><label class="form-label">Receipt Footer</label><textarea name="receipt_footer" class="form-control" style="min-height:60px;"><?php echo htmlspecialchars($company['receipt_footer'] ?? ''); ?></textarea></div>
            <div class="form-group">
                <label class="form-label d-flex align-center gap-8">
                    <label class="toggle-switch"><input type="checkbox" name="receipt_autoprint" <?php echo ($company['receipt_autoprint'] ?? 1) ? 'checked' : ''; ?>><span class="toggle-slider"></span></label>
                    Auto-print receipt after sale
                </label>
            </div>
            <div class="form-group">
                <label class="form-label d-flex align-center gap-8">
                    <label class="toggle-switch"><input type="checkbox" name="receipt_email" <?php echo ($company['receipt_email'] ?? 0) ? 'checked' : ''; ?>><span class="toggle-slider"></span></label>
                    Email receipt to customer
                </label>
            </div>
        </div>
    </div>

    <!-- Currency Settings -->
    <div class="settings-section">
        <div class="settings-section-header">
            <h3><i class="fa-solid fa-coins text-purple"></i> Currency Settings</h3>
            <i class="fa-solid fa-chevron-down toggle-icon"></i>
        </div>
        <div class="settings-section-body">
            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;">
                <div class="form-group">
                    <label class="form-label">Currency Code</label>
                    <input type="text" name="currency_code" class="form-control" value="<?php echo htmlspecialchars($company['currency_code'] ?? 'USD'); ?>" placeholder="e.g. USD">
                </div>
                <div class="form-group">
                    <label class="form-label">Symbol</label>
                    <input type="text" name="currency_symbol" class="form-control" value="<?php echo htmlspecialchars($company['currency_symbol'] ?? '$'); ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Decimal Places</label>
                    <select name="currency_decimals" class="form-control">
                        <option value="0" <?php echo ($company['currency_decimals'] ?? 2) == 0 ? 'selected' : ''; ?>>0</option>
                        <option value="1" <?php echo ($company['currency_decimals'] ?? 2) == 1 ? 'selected' : ''; ?>>1</option>
                        <option value="2" <?php echo ($company['currency_decimals'] ?? 2) == 2 ? 'selected' : ''; ?>>2</option>
                        <option value="3" <?php echo ($company['currency_decimals'] ?? 2) == 3 ? 'selected' : ''; ?>>3</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Barcode Settings -->
    <div class="settings-section">
        <div class="settings-section-header">
            <h3><i class="fa-solid fa-barcode text-pink"></i> Barcode Settings</h3>
            <i class="fa-solid fa-chevron-down toggle-icon"></i>
        </div>
        <div class="settings-section-body">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                <div class="form-group">
                    <label class="form-label">Barcode Format</label>
                    <select name="barcode_format" class="form-control">
                        <option value="Code 128" <?php echo ($company['barcode_format'] ?? '') == 'Code 128' ? 'selected' : ''; ?>>Code 128</option>
                        <option value="EAN-13" <?php echo ($company['barcode_format'] ?? '') == 'EAN-13' ? 'selected' : ''; ?>>EAN-13</option>
                        <option value="UPC-A" <?php echo ($company['barcode_format'] ?? '') == 'UPC-A' ? 'selected' : ''; ?>>UPC-A</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Prefix</label>
                    <input type="text" name="barcode_prefix" class="form-control" value="<?php echo htmlspecialchars($company['barcode_prefix'] ?? 'VEN-'); ?>">
                </div>
            </div>
            <div class="form-group">
                <label class="form-label d-flex align-center gap-8">
                    <label class="toggle-switch"><input type="checkbox" name="barcode_autogen" <?php echo ($company['barcode_autogen'] ?? 1) ? 'checked' : ''; ?>><span class="toggle-slider"></span></label>
                    Auto-generate barcode for new products
                </label>
            </div>
        </div>
    </div>
</form>

<!-- Modal for Add/Edit Branch -->
<div class="modal-overlay" id="branchModal">
    <div class="modal-content" style="max-width: 500px;">
        <div class="modal-header">
            <h3 id="modalTitle">Add Branch</h3>
            <button class="modal-close"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <form id="branchForm">
            <div class="modal-body">
                <input type="hidden" name="action" value="save_branch">
                <input type="hidden" name="id" id="branch_id" value="">
                <div class="form-group">
                    <label class="form-label">Branch Name</label>
                    <input type="text" name="name" id="branch_name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Address</label>
                    <textarea name="address" id="branch_address" class="form-control" rows="3"></textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">Status</label>
                    <select name="status" id="branch_status" class="form-control">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary modal-cancel">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Branch</button>
            </div>
        </form>
    </div>
</div>

<script>
$(document).ready(function() {
    // Save All Settings
    $('#saveAllSettings').click(function() {
        const btn = $(this);
        const originalHtml = btn.html();
        btn.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin"></i> Saving...');

        $.ajax({
            url: 'controllers/SettingsController.php',
            type: 'POST',
            data: $('#settingsForm').serialize(),
            success: function(response) {
                if (response.status === 'success') {
                    window.showToast('Success', response.message, 'success');
                } else {
                    window.showToast('Error', response.message, 'error');
                }
            },
            error: function() {
                window.showToast('Error', 'An error occurred while saving settings.', 'error');
            },
            complete: function() {
                btn.prop('disabled', false).html(originalHtml);
            }
        });
    });

    // Branch Management
    $('#addBranchBtn').click(function() {
        $('#branchForm')[0].reset();
        $('#branch_id').val('');
        $('#modalTitle').text('Add Branch');
        $('#branchModal').addClass('active');
    });

    $(document).on('click', '.edit-branch', function() {
        const data = $(this).data();
        $('#branch_id').val(data.id);
        $('#branch_name').val(data.name);
        $('#branch_address').val(data.address);
        $('#branch_status').val(data.status);
        $('#modalTitle').text('Edit Branch');
        $('#branchModal').addClass('active');
    });

    $('#branchForm').submit(function(e) {
        e.preventDefault();
        const btn = $(this).find('button[type="submit"]');
        btn.prop('disabled', true).text('Saving...');

        $.ajax({
            url: 'controllers/SettingsController.php',
            type: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                if (response.status === 'success') {
                    location.reload(); // Simple reload to refresh table
                } else {
                    showToast('error', response.message);
                    btn.prop('disabled', false).text('Save Branch');
                }
            }
        });
    });

    $(document).on('click', '.delete-branch', function() {
        const id = $(this).data('id');
        Swal.fire({
            title: 'Delete Branch?',
            text: "Are you sure you want to delete this branch? This action cannot be undone.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: 'var(--danger)',
            cancelButtonColor: 'var(--secondary)',
            confirmButtonText: 'Yes, delete it'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: 'controllers/SettingsController.php',
                    type: 'POST',
                    data: { action: 'delete_branch', id: id },
                    success: function(response) {
                        if (response.status === 'success') {
                            location.reload();
                        } else {
                            window.showToast('Error', response.message, 'error');
                        }
                    }
                });
            }
        });
    });
});
</script>
