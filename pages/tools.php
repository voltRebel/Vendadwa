<?php
// Fetch backup history via PHP
$company_id = $_SESSION['company_id'];
$stmt = $pdo->prepare("SELECT * FROM backups WHERE company_id = ? ORDER BY created_at DESC");
$stmt->execute([$company_id]);
$backups = $stmt->fetchAll(PDO::FETCH_ASSOC);

$lastBackup = !empty($backups) ? date('M j, Y — g:i A', strtotime($backups[0]['created_at'])) : 'Never';
$backupCount = count($backups);
?>

<!-- Top Bar -->
<div class="top-bar">
    <div class="top-bar-left">
        <button class="mobile-menu-toggle" id="mobileMenuToggle"><i class="fa-solid fa-bars"></i></button>
        <h1>System Tools</h1>
    </div>
</div>

<!-- Tools Grid -->
<div class="tools-grid">
    <div class="tool-card">
        <div class="tool-icon" style="background:var(--primary-50);color:var(--primary-500);">
            <i class="fa-solid fa-database"></i>
        </div>
        <h4>Backup Data</h4>
        <p>Create a full backup of your POS data including products, sales, and settings</p>
        <div class="mt-auto">
            <button class="btn btn-sm btn-primary w-100" data-modal="backupModal">Create Backup</button>
            <div class="mt-8 text-muted" style="font-size:0.72rem;">Last backup: <span id="lastBackupText"><?php echo $lastBackup; ?></span></div>
        </div>
    </div>

    <div class="tool-card">
        <div class="tool-icon" style="background:var(--secondary-50);color:var(--secondary-500);">
            <i class="fa-solid fa-clock-rotate-left"></i>
        </div>
        <h4>Restore Data</h4>
        <p>Restore your system from a previous backup point</p>
        <div class="mt-auto">
            <button class="btn btn-sm btn-primary w-100" data-modal="restoreModal">Restore</button>
            <div class="mt-8 text-muted" style="font-size:0.72rem;"><span id="backupCountBadge"><?php echo $backupCount; ?></span> backups available</div>
        </div>
    </div>
    
    <!-- ... Rest of tool cards (Import, Export, etc.) ... -->
    <div class="tool-card">
        <div class="tool-icon" style="background:var(--accent-gold-lighter);color:var(--accent-gold);">
            <i class="fa-solid fa-file-import"></i>
        </div>
        <h4>Import Data</h4>
        <p>Import products, customers, or inventory from CSV/Excel files</p>
        <div class="mt-auto">
            <button class="btn btn-sm btn-primary w-100" data-modal="importModal">Import</button>
        </div>
    </div>

    <div class="tool-card">
        <div class="tool-icon" style="background:var(--success-bg);color:var(--success);">
            <i class="fa-solid fa-file-export"></i>
        </div>
        <h4>Export Data</h4>
        <p>Export your data to CSV files</p>
        <div class="mt-auto d-flex flex-wrap gap-8">
            <button class="btn btn-sm btn-outline export-btn flex-1" data-type="products" style="padding:6px 4px;">Products</button>
            <button class="btn btn-sm btn-outline export-btn flex-1" data-type="sales" style="padding:6px 4px;">Sales</button>
            <button class="btn btn-sm btn-outline export-btn flex-1" data-type="customers" style="padding:6px 4px;">Customers</button>
            <button class="btn btn-sm btn-outline export-btn flex-1" data-type="expenses" style="padding:6px 4px;">Expenses</button>
        </div>
    </div>

    <div class="tool-card">
        <div class="tool-icon" style="background:var(--info-bg);color:var(--info);">
            <i class="fa-solid fa-cloud-arrow-up"></i>
        </div>
        <h4>Cloud Sync</h4>
        <p>Sync your POS data across multiple devices and locations</p>
        <div class="mt-auto">
            <span class="badge badge-warning">Coming Soon</span>
        </div>
    </div>

    <div class="tool-card">
        <div class="tool-icon" style="background:var(--warning-bg);color:var(--warning);">
            <i class="fa-solid fa-broom"></i>
        </div>
        <h4>Clear Cache</h4>
        <p>Clear temporary files and optimize system performance</p>
        <div class="mt-auto">
            <button class="btn btn-sm btn-primary w-100 btn-clear-cache">Clear</button>
        </div>
    </div>
</div>

<!-- Backup History -->
<div class="glass-card-static mt-100">
    <div class="d-flex align-center justify-between mb-16">
        <h3 style="font-size:1.1rem;font-weight:700;"><i class="fa-solid fa-history mr-8"></i> Backup History</h3>
    </div>
    <div class="table-container">
        <table class="data-table" id="backupTable">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Date & Time</th>
                    <th>Type</th>
                    <th>Size</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="backupHistoryBody">
                <?php foreach ($backups as $index => $backup): ?>
                <tr>
                    <td><?php echo $index + 1; ?></td>
                    <td class="fw-600"><?php echo date('M j, Y — g:i A', strtotime($backup['created_at'])); ?></td>
                    <td><span class="badge badge-<?php echo $backup['type'] === 'Auto' ? 'teal' : 'purple'; ?>"><?php echo $backup['type']; ?></span></td>
                    <td><?php echo $backup['filesize']; ?></td>
                    <td><span class="badge badge-<?php echo $backup['status'] === 'Complete' ? 'success' : 'warning'; ?>"><?php echo $backup['status']; ?></span></td>
                    <td>
                        <div class="d-flex gap-8">
                            <a href="backups/<?php echo $backup['filename']; ?>" download class="btn btn-sm btn-secondary"><i class="fa-solid fa-download"></i></a>
                            <button class="btn btn-sm btn-secondary delete-backup" data-id="<?php echo $backup['id']; ?>"><i class="fa-solid fa-trash"></i></button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modals -->
<div class="modal-overlay" id="backupModal">
    <div class="modal">
        <div class="modal-header">
            <h3><i class="fa-solid fa-database mr-8"></i> Create New Backup</h3>
            <button class="modal-close">&times;</button>
        </div>
        <div class="modal-body text-center p-24">
            <div class="mb-16">
                <i class="fa-solid fa-cloud-arrow-up" style="font-size:3rem;color:var(--primary-200);"></i>
            </div>
            <p>This will create a compressed SQL dump of your entire database including all products, sales, customers, and system settings.</p>
            <p class="text-muted mt-8" style="font-size:0.85rem;">The process usually takes 5-10 seconds depending on your data size.</p>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary modal-cancel">Cancel</button>
            <button class="btn btn-primary" id="startBackupBtn"><i class="fa-solid fa-download"></i> Start Backup</button>
        </div>
    </div>
</div>

<div class="modal-overlay" id="restoreModal">
    <div class="modal">
        <div class="modal-header">
            <h3><i class="fa-solid fa-clock-rotate-left mr-8"></i> Restore System</h3>
            <button class="modal-close">&times;</button>
        </div>
        <div class="modal-body">
            <p class="mb-16" style="font-size:0.85rem;color:var(--text-secondary);">Select a backup point to restore your system. <strong class="text-danger">All current data will be overwritten!</strong></p>
            
            <div class="form-group">
                <label class="form-label">Select Backup</label>
                <select class="form-control" id="restoreBackupSelect">
                    <?php foreach ($backups as $backup): ?>
                    <option value="<?php echo $backup['filename']; ?>"><?php echo date('M j, Y — g:i A', strtotime($backup['created_at'])); ?> (<?php echo $backup['filesize']; ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Admin Password</label>
                <input type="password" class="form-control" id="restoreAdminPassword" placeholder="Confirm your password to restore">
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary modal-cancel">Cancel</button>
            <button class="btn btn-danger" id="confirmRestoreBtn"><i class="fa-solid fa-clock-rotate-left"></i> Restore</button>
        </div>
    </div>
</div>

<div class="modal-overlay" id="importModal">
    <div class="modal">
        <div class="modal-header">
            <h3>📥 Import Data</h3>
            <button class="modal-close">&times;</button>
        </div>
        <div class="modal-body">
            <p class="mb-16" style="font-size:0.85rem;color:var(--text-secondary);">Select the type of data and upload your CSV file.</p>
            
            <div class="form-group">
                <label class="form-label">Data Type</label>
                <select class="form-control" id="importType">
                    <option value="products">Products</option>
                    <option value="customers">Customers</option>
                    <option value="expenses">Expenses</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Select CSV File</label>
                <input type="file" class="form-control" id="importFile" accept=".csv">
            </div>

            <div class="mt-16 p-12 glass-card-static" style="background:var(--grey-50);">
                <div class="fw-600 mb-8" style="font-size:0.85rem;">Need a template?</div>
                <div class="d-flex gap-12">
                    <a href="#" class="text-teal download-template" data-type="products" style="font-size:0.75rem;"><i class="fa-solid fa-download"></i> Products</a>
                    <a href="#" class="text-teal download-template" data-type="customers" style="font-size:0.75rem;"><i class="fa-solid fa-download"></i> Customers</a>
                    <a href="#" class="text-teal download-template" data-type="expenses" style="font-size:0.75rem;"><i class="fa-solid fa-download"></i> Expenses</a>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary modal-cancel">Cancel</button>
            <button class="btn btn-primary" id="processImportBtn"><i class="fa-solid fa-file-import"></i> Process Import</button>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Toast helper
    window.showToast = function(title, message, icon = 'success') {
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer)
                toast.addEventListener('mouseleave', Swal.resumeTimer)
            }
        });
        Toast.fire({
            icon: icon,
            title: title,
            text: message
        });
    }

    // Helper to initialize DataTables
    function initDataTable(selector, options = {}) {
        if ($.fn.DataTable.isDataTable(selector)) {
            $(selector).DataTable().clear().destroy();
        }
        $(selector).DataTable($.extend({
            pageLength: 10,
            ordering: false,
            responsive: true,
            lengthChange: false,
            language: { search: "", searchPlaceholder: "Search..." }
        }, options));
    }

    // Initialize DataTable on the PHP-rendered rows
    initDataTable('#backupTable');

    // Load Backup History (AJAX refresh helper)
    function loadBackupHistory() {
        $.post('controllers/ToolsController.php', { action: 'get_backups' }, function(response) {
            if (response.status === 'success') {
                let html = '';
                let selectHtml = '';
                response.data.forEach((backup, index) => {
                    html += `
                        <tr>
                            <td>${index + 1}</td>
                            <td class="fw-600">${new Date(backup.created_at).toLocaleString()}</td>
                            <td><span class="badge badge-${backup.type === 'Auto' ? 'teal' : 'purple'}">${backup.type}</span></td>
                            <td>${backup.filesize}</td>
                            <td><span class="badge badge-${backup.status === 'Complete' ? 'success' : 'warning'}">${backup.status}</span></td>
                            <td>
                                <div class="d-flex gap-8">
                                    <a href="backups/${backup.filename}" download class="btn btn-sm btn-secondary"><i class="fa-solid fa-download"></i></a>
                                    <button class="btn btn-sm btn-secondary delete-backup" data-id="${backup.id}"><i class="fa-solid fa-trash"></i></button>
                                </div>
                            </td>
                        </tr>
                    `;
                    selectHtml += `<option value="${backup.filename}">${new Date(backup.created_at).toLocaleString()} (${backup.filesize})</option>`;
                });
                $('#backupHistoryBody').html(html);
                $('#restoreBackupSelect').html(selectHtml);
                
                // Update summary cards
                if (response.data.length > 0) {
                    const lastDate = new Date(response.data[0].created_at);
                    const options = { month: 'short', day: 'numeric', year: 'numeric', hour: 'numeric', minute: 'numeric', hour12: true };
                    // Format like "Mar 8, 2026 — 9:15 PM" to match PHP
                    const formatted = lastDate.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) + 
                                     ' — ' + 
                                     lastDate.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true });
                    $('#lastBackupText').text(formatted);
                } else {
                    $('#lastBackupText').text('Never');
                }
                $('#backupCountBadge').text(response.data.length);
                
                initDataTable('#backupTable');
            }
        }, 'json');
    }

    // Create Backup
    $('#startBackupBtn').click(function() {
        $(this).prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin"></i> Backing up...');
        $.post('controllers/ToolsController.php', { action: 'create_backup' }, function(response) {
            $('#startBackupBtn').prop('disabled', false).html('<i class="fa-solid fa-download"></i> Start Backup');
            if (response.status === 'success') {
                showToast('Success', response.message, 'success');
                $('#backupModal').fadeOut();
                loadBackupHistory();
            } else {
                showToast('Error', response.message, 'error');
            }
        }, 'json');
    });

    // Delete Backup
    $(document).on('click', '.delete-backup', function() {
        const id = $(this).data('id');
        Swal.fire({
            title: 'Are you sure?',
            text: "This backup will be permanently deleted!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#859694',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.post('controllers/ToolsController.php', { action: 'delete_backup', id: id }, function(response) {
                    if (response.status === 'success') {
                        showToast('Deleted!', response.message, 'success');
                        loadBackupHistory();
                    } else {
                        showToast('Error', response.message, 'error');
                    }
                }, 'json');
            }
        });
    });

    // Clear Cache
    $('.btn-clear-cache').click(function() {
        Swal.fire({
            title: 'Clear Cache?',
            text: "This will clear temporary system files and logs.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#1a8a7c',
            cancelButtonColor: '#859694',
            confirmButtonText: 'Yes, clear it'
        }).then((result) => {
            if (result.isConfirmed) {
                $.post('controllers/ToolsController.php', { action: 'clear_cache' }, function(response) {
                    if (response.status === 'success') {
                        showToast('Success', response.message, 'success');
                    } else {
                        showToast('Error', response.message, 'error');
                    }
                }, 'json');
            }
        });
    });

    // Confirm Restore
    $('#confirmRestoreBtn').click(function() {
        const filename = $('#restoreBackupSelect').val();
        const password = $('#restoreAdminPassword').val();

        if (!filename) {
            showToast('Error', 'Please select a backup file.', 'error');
            return;
        }
        if (!password) {
            showToast('Error', 'Please enter your admin password.', 'error');
            return;
        }

        Swal.fire({
            title: 'Restore Database?',
            text: "This will OVERWRITE all current data! This action cannot be undone.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#859694',
            confirmButtonText: 'Yes, restore it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $(this).prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin"></i> Restoring...');
                $.post('controllers/ToolsController.php', { 
                    action: 'restore_db', 
                    filename: filename,
                    password: password
                }, function(response) {
                    $('#confirmRestoreBtn').prop('disabled', false).html('<i class="fa-solid fa-clock-rotate-left"></i> Restore');
                    if (response.status === 'success') {
                        window.showToast('Restored!', response.message, 'success');
                        $('#restoreModal').fadeOut();
                        $('#restoreAdminPassword').val('');
                        setTimeout(() => location.reload(), 2000);
                    } else {
                        window.showToast('Error', response.message, 'error');
                    }
                }, 'json');
            }
        });
    });

    // Export Data (Multi-type)
    $('.export-btn').click(function() {
        const type = $(this).data('type');
        const form = $('<form>', {
            action: 'controllers/ToolsController.php',
            method: 'POST'
        }).append($('<input>', {
            type: 'hidden',
            name: 'action',
            value: 'export_csv'
        })).append($('<input>', {
            type: 'hidden',
            name: 'type',
            value: type
        }));
        
        $('body').append(form);
        form.submit();
        form.remove();
        showToast('Info', `Exporting ${type}...`, 'info');
    });

    // Download Template
    $('.download-template').click(function(e) {
        e.preventDefault();
        const type = $(this).data('type');
        const form = $('<form>', {
            action: 'controllers/ToolsController.php',
            method: 'POST'
        }).append($('<input>', {
            type: 'hidden',
            name: 'action',
            value: 'get_template'
        })).append($('<input>', {
            type: 'hidden',
            name: 'type',
            value: type
        }));
        
        $('body').append(form);
        form.submit();
        form.remove();
    });

    // Import Data (Multi-type)
    $('.tool-card:has(h4:contains("Import Data"))').attr('data-modal', 'importModal');

    $('#processImportBtn').click(function() {
        const fileInput = $('#importFile')[0];
        const importType = $('#importType').val();

        if (fileInput.files.length === 0) {
            showToast('Error', 'Please select a CSV file.', 'error');
            return;
        }

        const formData = new FormData();
        formData.append('action', 'import_csv');
        formData.append('import_type', importType);
        formData.append('import_file', fileInput.files[0]);

        $(this).prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin"></i> Importing...');
        
        $.ajax({
            url: 'controllers/ToolsController.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                $('#processImportBtn').prop('disabled', false).html('<i class="fa-solid fa-file-import"></i> Process Import');
                if (response.status === 'success') {
                    showToast('Success', response.message, 'success');
                    $('#importModal').fadeOut();
                    $('#importFile').val('');
                    // Reload the relevant tab if needed, but for now simple refresh is fine
                } else {
                    showToast('Error', response.message, 'error');
                }
            },
            dataType: 'json'
        });
    });

    // Handle standard modal behaviors
    $('[data-modal]').click(function() {
        const modalId = $(this).data('modal');
        $(`#${modalId}`).fadeIn();
    });

    $('.modal-close, .modal-cancel').click(function() {
        $(this).closest('.modal-overlay').fadeOut();
    });
});
</script>
