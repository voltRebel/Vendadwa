<!-- Top Bar -->
<div class="top-bar">
    <div class="top-bar-left">
        <button class="mobile-menu-toggle" id="mobileMenuToggle"><i class="fa-solid fa-bars"></i></button>
        <h1>Users & Roles</h1>
    </div>
    <div class="top-bar-right">
        <button class="btn btn-primary" id="openAddUserModal">
            <i class="fa-solid fa-user-plus"></i> Add User
        </button>
    </div>
</div>

<!-- Moment.js for date formatting in activity logs -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>

<!-- Stats row -->
<div style="display:grid;grid-template-columns:repeat(3, 1fr);gap:16px;margin-bottom:24px;">
    <div class="stat-card pink" style="margin:0;">
        <div class="stat-icon pink"><i class="fa-solid fa-users"></i></div>
        <div class="stat-info"><h3 id="statTotalUsers">0</h3><p>Total Users</p></div>
    </div>
    <div class="stat-card success" style="margin:0;">
        <div class="stat-icon success"><i class="fa-solid fa-user-check"></i></div>
        <div class="stat-info"><h3 id="statActiveUsers">0</h3><p>Active Users</p></div>
    </div>
    <div class="stat-card warning" style="margin:0;">
        <div class="stat-icon warning"><i class="fa-solid fa-user-slash"></i></div>
        <div class="stat-info"><h3 id="statInactiveUsers">0</h3><p>Inactive / Disabled</p></div>
    </div>
</div>

<div class="glass-card-static">
    <div class="tab-nav">
        <button class="tab-btn active" data-tab="tab-userlist">Users</button>
        <button class="tab-btn" data-tab="tab-roles">Roles & Permissions</button>
        <button class="tab-btn" data-tab="tab-activitylog">Activity Logs</button>
    </div>

    <!-- Users List -->
    <div class="tab-content active" id="tab-userlist">
        <div class="table-container">
            <table class="data-table" id="usersTable">
                <thead><tr><th>#</th><th>User</th><th>Username</th><th>Email</th><th>Role</th><th>Last Login</th><th>Status</th><th>Actions</th></tr></thead>
                <tbody id="userTableBody">
                </tbody>
            </table>
        </div>
    </div>

    <!-- Roles & Permissions -->
    <div class="tab-content" id="tab-roles">
        <div class="d-flex justify-between align-center mb-16">
            <h3>Roles & Permissions</h3>
            <button class="btn btn-primary btn-sm" id="openAddRoleModal"><i class="fa-solid fa-plus"></i> Add Role</button>
        </div>
        <div class="table-container">
            <table class="data-table" id="rolesTable">
                <thead><tr><th>#</th><th>Role</th><th>Dashboard</th><th>POS</th><th>Products</th><th>Customers</th><th>Suppliers</th><th>Returns</th><th>Expenses</th><th>Reports</th><th>Users</th><th>Settings</th><th>Actions</th></tr></thead>
                <tbody id="rolesTableBody">
                    <!-- Loaded via JS -->
                </tbody>
            </table>
        </div>
    </div>

    <!-- Activity Logs -->
    <div class="tab-content" id="tab-activitylog">
        <div class="d-flex justify-between align-center mb-16">
            <h3>Activity Logs</h3>
            <div class="d-flex gap-8">
                <select id="logUserFilter" class="form-control" style="width:auto;padding:8px 34px 8px 12px;font-size:0.82rem;">
                    <option value="">All Users</option>
                </select>
                <button class="btn btn-secondary btn-sm" id="refreshLogs"><i class="fa-solid fa-rotate"></i></button>
            </div>
        </div>
        <div class="table-container">
            <table class="data-table" id="activityLogsTable">
                <thead><tr><th>#</th><th>Time</th><th>User</th><th>Action</th><th>Details</th></tr></thead>
                <tbody id="activityLogTableBody">
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- User Modal (Add/Edit) -->
<div class="modal-overlay" id="userModal">
    <div class="modal">
        <div class="modal-header">
            <h3 id="modalTitle">👤 Add New User</h3>
            <button class="modal-close">&times;</button>
        </div>
        <form id="userForm">
            <input type="hidden" name="id" id="userId">
            <input type="hidden" name="action" value="save_user">
            
            <div class="form-group">
                <label class="form-label">Full Name</label>
                <input type="text" name="name" id="userName" class="form-control" placeholder="e.g. Jane Doe" required>
            </div>
            
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                <div class="form-group">
                    <label class="form-label">Username</label>
                    <input type="text" name="username" id="userUsername" class="form-control" placeholder="jane.doe" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Email (Optional)</label>
                    <input type="email" name="email" id="userEmail" class="form-control" placeholder="jane@example.com">
                </div>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                <div class="form-group">
                    <label class="form-label">Role</label>
                    <select name="role" id="userRole" class="form-control">
                        <option value="admin">Admin</option>
                        <option value="manager">Manager</option>
                        <option value="cashier">Cashier</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Status</label>
                    <select name="status" id="userStatus" class="form-control">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive / Disabled</option>
                    </select>
                </div>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                <div class="form-group">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" id="userPassword" class="form-control" placeholder="••••••••">
                    <small class="text-muted" id="passwordHint" style="display:none;">Leave blank to keep current password</small>
                </div>
                <div class="form-group">
                    <label class="form-label">Confirm Password</label>
                    <input type="password" id="userConfirmPassword" class="form-control" placeholder="••••••••">
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary modal-cancel">Cancel</button>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-check"></i> Save User</button>
            </div>
        </form>
    </div>
</div>

<!-- Role Modal (Add/Edit) -->
<div class="modal-overlay" id="roleModal">
    <div class="modal" style="max-width:600px;">
        <div class="modal-header">
            <h3 id="roleModalTitle">🛡️ Add New Role</h3>
            <button class="modal-close">&times;</button>
        </div>
        <form id="roleForm">
            <input type="hidden" name="id" id="roleId">
            <input type="hidden" name="action" value="save_role">
            
            <div class="form-group">
                <label class="form-label">Role Name</label>
                <input type="text" name="name" id="roleName" class="form-control" placeholder="e.g. Supervisor" required>
            </div>
            
            <div class="form-group">
                <label class="form-label">Permissions</label>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;background:rgba(255,255,255,0.05);padding:15px;border-radius:8px;">
                    <label class="d-flex align-center gap-8"><input type="checkbox" name="permissions[dashboard]" value="1"> Dashboard</label>
                    <label class="d-flex align-center gap-8"><input type="checkbox" name="permissions[pos]" value="1"> POS Access</label>
                    <label class="d-flex align-center gap-8"><input type="checkbox" name="permissions[products]" value="1"> Products</label>
                    <label class="d-flex align-center gap-8"><input type="checkbox" name="permissions[customers]" value="1"> Customers</label>
                    <label class="d-flex align-center gap-8"><input type="checkbox" name="permissions[suppliers]" value="1"> Suppliers</label>
                    <label class="d-flex align-center gap-8"><input type="checkbox" name="permissions[returns]" value="1"> Returns</label>
                    <label class="d-flex align-center gap-8"><input type="checkbox" name="permissions[expenses]" value="1"> Expenses</label>
                    <label class="d-flex align-center gap-8"><input type="checkbox" name="permissions[reports]" value="1"> Reports</label>
                    <label class="d-flex align-center gap-8"><input type="checkbox" name="permissions[users]" value="1"> Users & Roles</label>
                    <label class="d-flex align-center gap-8"><input type="checkbox" name="permissions[settings]" value="1"> Settings</label>
                    <label class="d-flex align-center gap-8"><input type="checkbox" name="permissions[tools]" value="1"> Tools</label>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary modal-cancel">Cancel</button>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-check"></i> Save Role</button>
            </div>
        </form>
    </div>
</div>

<script>
$(document).ready(function() {
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

    // 1. Load All Data
    loadUsers();
    loadRoles();
    loadActivityLogs();
    loadUserStats();

    // 2. Tab Switching
    $('.tab-btn').on('click', function() {
        const tabId = $(this).data('tab');
        $('.tab-btn').removeClass('active');
        $(this).addClass('active');
        $('.tab-content').removeClass('active');
        $('#' + tabId).addClass('active');
    });

    // 3. User Functions
    function loadUsers() {
        $.post('controllers/UserController.php', { action: 'get_users' }, function(r) {
            if (r.status === 'success') {
                let html = '';
                let userOptions = '<option value="">All Users</option>';
                
                r.data.forEach((user, index) => {
                    const initials = user.name.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();
                    const statusBadge = user.status === 'active' ? 'success' : 'danger';
                    const lastLogin = user.last_login ? moment(user.last_login).fromNow() : 'Never';
                    
                    let roleBadge = 'badge-purple';
                    if(user.role === 'admin') roleBadge = 'badge-pink';
                    else if(user.role === 'manager') roleBadge = 'badge-info';

                    html += `
                        <tr data-id="${user.id}">
                            <td>${index + 1}</td>
                            <td>
                                <div class="d-flex align-center gap-8">
                                    <div class="sidebar-user-avatar" style="width:34px;height:34px;font-size:0.75rem;">${initials}</div>
                                    <div><div class="fw-600">${user.name}</div></div>
                                </div>
                            </td>
                            <td>${user.username}</td>
                            <td>${user.email || '<span class="text-muted">--</span>'}</td>
                            <td><span class="badge ${roleBadge}">${user.role.charAt(0).toUpperCase() + user.role.slice(1)}</span></td>
                            <td class="text-muted" style="font-size:0.8rem;">${lastLogin}</td>
                            <td>
                                <span class="badge badge-${statusBadge} status-toggle" style="cursor:pointer;" data-id="${user.id}">
                                    ${user.status.charAt(0).toUpperCase() + user.status.slice(1)}
                                </span>
                            </td>
                            <td>
                                <div class="d-flex gap-8">
                                    <button class="btn btn-sm btn-secondary edit-user" data-user='${JSON.stringify(user).replace(/'/g, "&apos;")}'>
                                        <i class="fa-solid fa-pen"></i>
                                    </button>
                                    <button class="btn btn-sm btn-secondary delete-user" data-id="${user.id}">
                                        <i class="fa-solid fa-trash text-danger"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    `;
                    userOptions += `<option value="${user.id}">${user.name}</option>`;
                });
                $('#userTableBody').html(html || '<tr><td colspan="8" class="text-center p-20">No users found</td></tr>');
                $('#logUserFilter').html(userOptions);
                initDataTable('#usersTable');
            }
        });
    }

    function loadUserStats() {
        $.post('controllers/UserController.php', { action: 'get_user_stats' }, function(r) {
            if (r.status === 'success') {
                $('#statTotalUsers').text(r.data.total_users);
                $('#statActiveUsers').text(r.data.active_users);
                $('#statInactiveUsers').text(r.data.inactive_users);
            }
        });
    }

    function loadRoles() {
        $.post('controllers/UserController.php', { action: 'get_roles' }, function(r) {
            if (r.status === 'success') {
                let html = '';
                let selectOptions = '';
                let i = 1;
                for (const [key, data] of Object.entries(r.data)) {
                    html += `<tr><td>${i++}</td><td><span class="badge ${data.badge}">${data.label}</span></td>`;
                    const perms = ['dashboard', 'pos', 'products', 'customers', 'suppliers', 'returns', 'expenses', 'reports', 'users', 'settings'];
                    perms.forEach(p => {
                        const allowed = data.permissions && data.permissions[p];
                        html += `<td><i class="fa-solid ${allowed ? 'fa-check text-success' : 'fa-xmark text-danger'}"></i></td>`;
                    });
                    html += `
                        <td>
                            <div class="d-flex gap-8">
                                <button class="btn btn-sm btn-secondary edit-role" data-role='${JSON.stringify({name: key, id: data.id, permissions: data.permissions}).replace(/'/g, "&apos;")}'>
                                    <i class="fa-solid fa-pen"></i>
                                </button>
                                <button class="btn btn-sm btn-secondary delete-role" data-id="${data.id}">
                                    <i class="fa-solid fa-trash text-danger"></i>
                                </button>
                            </div>
                        </td>
                    </tr>`;
                    selectOptions += `<option value="${key}">${data.label}</option>`;
                }
                $('#rolesTableBody').html(html);
                $('#userRole').html(selectOptions);
                initDataTable('#rolesTable');
            }
        });
    }

    function loadActivityLogs() {
        const userId = $('#logUserFilter').val();
        $.post('controllers/UserController.php', { action: 'get_activity_logs', user_id: userId }, function(r) {
            if (r.status === 'success') {
                let html = '';
                r.data.forEach((log, index) => {
                    const time = moment(log.created_at).format('hh:mm A, MMM Do');
                    let tagClass = 'badge-secondary';
                    if(log.action_type.includes('create')) tagClass = 'badge-success';
                    else if(log.action_type.includes('delete')) tagClass = 'badge-danger';
                    else if(log.action_type.includes('update')) tagClass = 'badge-info';
                    else if(log.action_type.includes('login')) tagClass = 'badge-pink';

                    html += `
                        <tr>
                            <td>${index + 1}</td>
                            <td class="text-muted" style="font-size:0.75rem;">${time}</td>
                            <td class="fw-600">${log.user_name}</td>
                            <td><span class="badge ${tagClass}">${log.action_type.replace('_', ' ').toUpperCase()}</span></td>
                            <td style="font-size:0.85rem;">${log.details}</td>
                        </tr>
                    `;
                });
                $('#activityLogTableBody').html(html || '<tr><td colspan="5" class="text-center p-20">No logs found</td></tr>');
                initDataTable('#activityLogsTable');
            } else {
                $('#activityLogTableBody').html(`<tr><td colspan="5" class="text-center p-20 text-danger">${r.message || 'Error loading logs'}</td></tr>`);
            }
        }, 'json').fail(function(xhr) {
            console.error(xhr.responseText);
            $('#activityLogTableBody').html('<tr><td colspan="5" class="text-center p-20 text-danger">Server error while loading logs</td></tr>');
        });
    }

    // Refresh logs
    $('#refreshLogs, #logUserFilter').on('click change', function() {
        loadActivityLogs();
    });

    // 4. Modal Handling
    $('#openAddUserModal').on('click', function() {
        $('#userName').val('');
        $('#userUsername').val('');
        $('#userEmail').val('');
        $('#userId').val('0');
        $('#userPassword').val('');
        $('#userConfirmPassword').val('');
        $('#userPassword').attr('required', true);
        $('#passwordHint').hide();
        $('#modalTitle').text('👤 Add New User');
        $('#userModal').fadeIn(200).css('display', 'flex');
    });

    $(document).on('click', '.edit-user', function() {
        const user = $(this).data('user');
        $('#userId').val(user.id);
        $('#userName').val(user.name);
        $('#userUsername').val(user.username);
        $('#userEmail').val(user.email || '');
        $('#userRole').val(user.role);
        $('#userStatus').val(user.status);
        $('#userPassword').val('').removeAttr('required');
        $('#userConfirmPassword').val('');
        $('#passwordHint').show();
        $('#modalTitle').text('✏️ Edit User');
        $('#userModal').fadeIn(200).css('display', 'flex');
    });

    $('.modal-close, .modal-cancel').on('click', function() {
        $(this).closest('.modal-overlay').fadeOut(200);
    });

    // 5. Form Submission
    $('#userForm').on('submit', function(e) {
        e.preventDefault();
        
        const pass = $('#userPassword').val();
        const conf = $('#userConfirmPassword').val();
        
        if (pass !== conf) {
            Swal.fire('Error', 'Passwords do not match.', 'error');
            return;
        }

        $.post('controllers/UserController.php', $(this).serialize(), function(r) {
            if (r.status === 'success') {
                Swal.fire('Success', r.message, 'success');
                $('#userModal').fadeOut(200);
                loadUsers();
                loadUserStats();
                loadActivityLogs();
            } else {
                Swal.fire('Error', r.message, 'error');
            }
        });
    });

    // 6. Delete & Toggle Status
    $(document).on('click', '.delete-user', function() {
        const id = $(this).data('id');
        Swal.fire({
            title: 'Are you sure?',
            text: "This will permanently remove the user.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ff4d6d',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.post('controllers/UserController.php', { action: 'delete_user', id: id }, function(r) {
                    if (r.status === 'success') {
                        Swal.fire('Deleted!', r.message, 'success');
                        loadUsers();
                        loadUserStats();
                        loadActivityLogs();
                    } else {
                        Swal.fire('Error', r.message, 'error');
                    }
                });
            }
        });
    });

    $(document).on('click', '.status-toggle', function() {
        const id = $(this).data('id');
        $.post('controllers/UserController.php', { action: 'toggle_status', id: id }, function(r) {
            if (r.status === 'success') {
                loadUsers();
                loadUserStats();
                loadActivityLogs();
            } else {
                Swal.fire('Error', r.message, 'error');
            }
        });
    });
    // Role Modal Handling
    $('#openAddRoleModal').on('click', function() {
        $('#roleId').val('0');
        $('#roleName').val('');
        $('#roleForm input[type="checkbox"]').prop('checked', false);
        $('#roleModalTitle').text('🛡️ Add New Role');
        $('#roleModal').fadeIn(200).css('display', 'flex');
    });

    $(document).on('click', '.edit-role', function() {
        const role = $(this).data('role');
        $('#roleId').val(role.id);
        $('#roleName').val(role.name);
        $('#roleForm input[type="checkbox"]').prop('checked', false);
        if (role.permissions) {
            for (const [p, val] of Object.entries(role.permissions)) {
                if (val) $(`#roleForm input[name="permissions[${p}]"]`).prop('checked', true);
            }
        }
        $('#roleModalTitle').text('✏️ Edit Role');
        $('#roleModal').fadeIn(200).css('display', 'flex');
    });

    $('#roleForm').on('submit', function(e) {
        e.preventDefault();
        $.post('controllers/UserController.php', $(this).serialize(), function(r) {
            if (r.status === 'success') {
                Swal.fire('Success', r.message, 'success');
                $('#roleModal').fadeOut(200);
                loadRoles();
                loadActivityLogs();
            } else {
                Swal.fire('Error', r.message, 'error');
            }
        });
    });

    $(document).on('click', '.delete-role', function() {
        const id = $(this).data('id');
        Swal.fire({
            title: 'Delete Role?',
            text: "This will remove the role configuration.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ff4d6d',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.post('controllers/UserController.php', { action: 'delete_role', id: id }, function(r) {
                    if (r.status === 'success') {
                        Swal.fire('Deleted!', r.message, 'success');
                        loadRoles();
                        loadActivityLogs();
                    } else {
                        Swal.fire('Error', r.message, 'error');
                    }
                });
            }
        });
    });
});
</script>
