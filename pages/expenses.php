<!-- Top Bar -->
<div class="top-bar">
    <div class="top-bar-left">
        <button class="mobile-menu-toggle" id="mobileMenuToggle"><i class="fa-solid fa-bars"></i></button>
        <h1>Expenses</h1>
    </div>
    <div class="top-bar-right">
        <button class="btn btn-primary" id="openAddExpenseBtn">
            <i class="fa-solid fa-plus"></i> Add Expense
        </button>
    </div>
</div>

<!-- Stats -->
<div class="stats-grid">
    <div class="stat-card pink">
        <div class="stat-icon pink"><i class="fa-solid fa-receipt"></i></div>
        <div class="stat-info"><h3 id="statTodayExpenses">GH₵0.00</h3><p>Today's Expenses</p></div>
    </div>
    <div class="stat-card purple">
        <div class="stat-icon purple"><i class="fa-solid fa-calendar"></i></div>
        <div class="stat-info"><h3 id="statMonthExpenses">GH₵0.00</h3><p>This Month</p></div>
    </div>
    <div class="stat-card gold">
        <div class="stat-icon gold"><i class="fa-solid fa-chart-line"></i></div>
        <div class="stat-info"><h3 id="statYearExpenses">GH₵0.00</h3><p>This Year</p></div>
    </div>
</div>

<div class="glass-card-static">
    <div class="tab-nav">
        <button class="tab-btn active" data-tab="tab-expenselist">Expenses</button>
        <button class="tab-btn" data-tab="tab-expensecats">Categories</button>
    </div>

    <!-- Expense List -->
    <div class="tab-content active" id="tab-expenselist">
        <div class="d-flex align-center justify-between mb-16" style="flex-wrap:wrap;gap:10px;">
            <div class="search-bar"><i class="fa-solid fa-magnifying-glass search-icon"></i><input type="text" id="expenseSearch" placeholder="Search expenses..."></div>
            <div class="d-flex gap-8" style="flex-wrap:wrap;">
                <select id="filterCategory" class="form-control" style="width:auto;padding:8px 34px 8px 12px;font-size:0.82rem;">
                    <option value="0">All Categories</option>
                </select>
                <input type="date" id="filterDate" class="form-control" style="width:auto;padding:8px 12px;font-size:0.82rem;">
            </div>
        </div>
        <div class="table-container">
            <table class="data-table">
                <thead><tr><th>#</th><th>Date</th><th>Description</th><th>Category</th><th>Amount</th><th>Payment</th><th>Actions</th></tr></thead>
                <tbody id="expenseTbody">
                </tbody>
            </table>
        </div>
    </div>

    <!-- Expense Categories -->
    <div class="tab-content" id="tab-expensecats">
        <div class="d-flex align-center justify-between mb-16">
            <h3>Expense Categories</h3>
            <button class="btn btn-sm btn-primary" id="openAddCategoryBtn"><i class="fa-solid fa-plus"></i> Add Category</button>
        </div>
        <div class="table-container">
            <table class="data-table">
                <thead><tr><th>#</th><th>Category</th><th>Expenses</th><th>Total Spent</th><th>Actions</th></tr></thead>
                <tbody id="categoryTbody">
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add/Edit Expense Modal -->
<div class="modal-overlay" id="addExpenseModal">
    <div class="modal">
        <form id="expenseForm">
            <input type="hidden" name="id" value="0">
            <div class="modal-header">
                <h3 id="expenseModalTitle">💰 Add Expense</h3>
                <button type="button" class="modal-close">&times;</button>
            </div>
            <div class="form-group"><label class="form-label">Description</label><input type="text" name="description" class="form-control" placeholder="What was this expense for?" required></div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                <div class="form-group"><label class="form-label">Amount (GH₵)</label><input type="number" name="amount" class="form-control" placeholder="0.00" step="0.01" min="0.01" required></div>
                <div class="form-group">
                    <label class="form-label">Category</label>
                    <div class="d-flex gap-8">
                        <select name="category_id" id="modalExpenseCategory" class="form-control" style="flex:1;"></select>
                        <button type="button" class="btn btn-sm btn-secondary" id="inlineAddCategoryBtn" title="Add new category" style="white-space:nowrap;"><i class="fa-solid fa-plus"></i></button>
                    </div>
                </div>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                <div class="form-group"><label class="form-label">Date</label><input type="date" name="expense_date" class="form-control" value="<?= date('Y-m-d') ?>" required></div>
                <div class="form-group"><label class="form-label">Payment Method</label><select name="payment_method" class="form-control">
                    <option value="Cash">Cash</option>
                    <option value="Bank Transfer">Bank Transfer</option>
                    <option value="Card">Card</option>
                    <option value="Mobile Money">Mobile Money</option>
                    <option value="Other">Other</option>
                </select></div>
            </div>
            <div class="form-group"><label class="form-label">Notes (Optional)</label><textarea name="notes" class="form-control" placeholder="Additional notes..." style="min-height:60px;"></textarea></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary modal-cancel">Cancel</button>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-check"></i> Save Expense</button>
            </div>
        </form>
    </div>
</div>

<!-- View Expense Modal -->
<div class="modal-overlay" id="viewExpenseModal">
    <div class="modal">
        <div class="modal-header">
            <h3>📋 Expense Details</h3>
            <button type="button" class="modal-close">&times;</button>
        </div>
        <div style="display:grid;gap:14px;padding:4px 0;">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                <div><label class="form-label" style="font-size:0.75rem;color:var(--text-muted);margin-bottom:2px;">Date</label><p id="viewDate" class="fw-600" style="margin:0;"></p></div>
                <div><label class="form-label" style="font-size:0.75rem;color:var(--text-muted);margin-bottom:2px;">Amount</label><p id="viewAmount" class="fw-600" style="margin:0;font-size:1.2rem;color:var(--danger-500);"></p></div>
            </div>
            <div><label class="form-label" style="font-size:0.75rem;color:var(--text-muted);margin-bottom:2px;">Description</label><p id="viewDescription" class="fw-600" style="margin:0;"></p></div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                <div><label class="form-label" style="font-size:0.75rem;color:var(--text-muted);margin-bottom:2px;">Category</label><p id="viewCategory" style="margin:0;"></p></div>
                <div><label class="form-label" style="font-size:0.75rem;color:var(--text-muted);margin-bottom:2px;">Payment Method</label><p id="viewPayment" style="margin:0;"></p></div>
            </div>
            <div><label class="form-label" style="font-size:0.75rem;color:var(--text-muted);margin-bottom:2px;">Notes</label><p id="viewNotes" style="margin:0;color:var(--text-secondary);font-style:italic;"></p></div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary modal-cancel">Close</button>
            <button type="button" class="btn btn-primary" id="viewEditBtn"><i class="fa-solid fa-pen"></i> Edit</button>
        </div>
    </div>
</div>

<!-- Add/Edit Category Modal -->
<div class="modal-overlay" id="addCategoryModal">
    <div class="modal">
        <form id="categoryForm">
            <input type="hidden" name="id" value="0">
            <div class="modal-header">
                <h3 id="catModalTitle">📂 Add Expense Category</h3>
                <button type="button" class="modal-close">&times;</button>
            </div>
            <div class="form-group"><label class="form-label">Category Name</label><input type="text" name="name" class="form-control" placeholder="e.g. Rent, Utilities" required></div>
            <div class="form-group"><label class="form-label">Description (Optional)</label><textarea name="description" class="form-control" placeholder="What kind of expenses fall under this?" style="min-height:60px;"></textarea></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary modal-cancel">Cancel</button>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-check"></i> Save Category</button>
            </div>
        </form>
    </div>
</div>

<script>
$(function(){
    var _allExpenses = []; // Cache for view/edit lookups
    var _inlineMode = false; // Track if category modal was opened from expense modal

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
    
    // For tabs that may be hidden
    let initializedTabs = { 'tab-expenselist': true };

    // Tab switching
    $('.tab-btn').on('click', function(){
        $('.tab-btn, .tab-content').removeClass('active');
        $(this).addClass('active');
        $('#' + $(this).data('tab')).addClass('active');
        
        let tabId = $(this).data('tab');
        if (!initializedTabs[tabId]) {
            if (tabId === 'tab-expensecats') initDataTable('#tab-expensecats .data-table');
            initializedTabs[tabId] = true;
        }

        if(tabId === 'tab-expensecats') loadCategories();
    });

    // =============================
    // LOAD DATA
    // =============================
    function loadExpenses() {
        $.post('controllers/ExpenseController.php', {
            action: 'get_expenses',
            search: $('#expenseSearch').val(),
            category_id: $('#filterCategory').val(),
            date: $('#filterDate').val()
        }, function(r){
            if(r.status === 'success'){
                _allExpenses = r.data;
                let html = '';
                if(r.data.length === 0){
                    html = '<tr><td colspan="7" style="text-align:center;padding:40px;color:var(--text-muted);">No expenses found.</td></tr>';
                } else {
                    r.data.forEach((x, index) => {
                        html += `<tr>
                            <td>${index + 1}</td>
                            <td>${x.expense_date}</td>
                            <td class="fw-600">${x.description}</td>
                            <td><span class="badge badge-outline">${x.category_name || 'Uncategorized'}</span></td>
                            <td class="fw-600">GH₵${parseFloat(x.amount).toFixed(2)}</td>
                            <td><span class="badge badge-purple">${x.payment_method}</span></td>
                            <td>
                                <div class="d-flex gap-8">
                                    <button class="btn btn-sm btn-secondary view-expense" data-id="${x.id}" title="View"><i class="fa-solid fa-eye"></i></button>
                                    <button class="btn btn-sm btn-secondary edit-expense" data-id="${x.id}" title="Edit"><i class="fa-solid fa-pen"></i></button>
                                    <button class="btn btn-sm btn-secondary delete-expense" data-id="${x.id}" title="Delete"><i class="fa-solid fa-trash"></i></button>
                                </div>
                            </td>
                        </tr>`;
                    });
                }
                $('#expenseTbody').html(html);
                initDataTable('#tab-expenselist .data-table');
            }
        }, 'json');
    }

    function loadCategories() {
        $.post('controllers/ExpenseController.php', {action: 'get_categories'}, function(r){
            if(r.status === 'success'){
                let html = '';
                if(r.data.length === 0){
                    html = '<tr><td colspan="5" style="text-align:center;padding:40px;color:var(--text-muted);">No categories yet. Add one to get started!</td></tr>';
                }
                let options = '<option value="0">All Categories</option>';
                let modalOptions = '<option value="0">Uncategorized</option>';
                
                r.data.forEach((x, index) => {
                    const desc = (x.description || '').replace(/"/g, '&quot;');
                    html += `<tr>
                        <td>${index + 1}</td>
                        <td class="fw-600">${x.name}</td>
                        <td>${x.expense_count}</td>
                        <td class="fw-600">GH₵${parseFloat(x.total_spent).toFixed(2)}</td>
                        <td>
                            <div class="d-flex gap-8">
                                <button class="btn btn-sm btn-secondary edit-cat" data-id="${x.id}" data-name="${x.name}" data-desc="${desc}" title="Edit"><i class="fa-solid fa-pen"></i></button>
                                <button class="btn btn-sm btn-secondary delete-cat" data-id="${x.id}" title="Delete"><i class="fa-solid fa-trash"></i></button>
                            </div>
                        </td>
                    </tr>`;
                    options += `<option value="${x.id}">${x.name}</option>`;
                    modalOptions += `<option value="${x.id}">${x.name}</option>`;
                });
                $('#categoryTbody').html(html);
                $('#filterCategory').html(options);
                $('#modalExpenseCategory').html(modalOptions);
                
                // Re-initialize DataTable if the tab is already active/initialized
                if (initializedTabs['tab-expensecats']) {
                    initDataTable('#tab-expensecats .data-table');
                }
            }
        }, 'json');
    }

    function loadStats() {
        $.post('controllers/ExpenseController.php', {action: 'get_expense_stats'}, function(r){
            if(r.status === 'success'){
                $('#statTodayExpenses').text('GH₵' + parseFloat(r.data.today).toFixed(2));
                $('#statMonthExpenses').text('GH₵' + parseFloat(r.data.month).toFixed(2));
                $('#statYearExpenses').text('GH₵' + parseFloat(r.data.year).toFixed(2));
            }
        }, 'json');
    }

    // Initial load
    loadExpenses();
    loadCategories();
    loadStats();

    // Filters
    $('#expenseSearch').on('keyup', function(){
        if($.fn.DataTable.isDataTable('#tab-expenselist .data-table')) {
            $('#tab-expenselist .data-table').DataTable().search($(this).val()).draw();
        }
    });
    $('#filterCategory, #filterDate').on('change', loadExpenses);

    // =============================
    // EXPENSE CRUD
    // =============================

    // ADD
    $('#openAddExpenseBtn').on('click', function(){
        $('#expenseForm')[0].reset();
        $('#expenseForm input[name="id"]').val(0);
        $('#expenseForm input[name="expense_date"]').val(new Date().toISOString().split('T')[0]);
        $('#expenseModalTitle').text('💰 Add Expense');
        $('#addExpenseModal').addClass('active');
    });

    // VIEW
    $(document).on('click', '.view-expense', function(){
        const id = $(this).data('id');
        const exp = _allExpenses.find(x => x.id == id);
        if(!exp) return;
        
        $('#viewDate').text(exp.expense_date);
        $('#viewAmount').text('GH₵' + parseFloat(exp.amount).toFixed(2));
        $('#viewDescription').text(exp.description);
        $('#viewCategory').html('<span class="badge badge-outline">' + (exp.category_name || 'Uncategorized') + '</span>');
        $('#viewPayment').html('<span class="badge badge-purple">' + exp.payment_method + '</span>');
        $('#viewNotes').text(exp.notes || 'No notes.');
        $('#viewEditBtn').data('id', id);
        $('#viewExpenseModal').addClass('active');
    });

    // VIEW -> EDIT bridge
    $('#viewEditBtn').on('click', function(){
        $('#viewExpenseModal').removeClass('active');
        const id = $(this).data('id');
        openEditExpense(id);
    });

    // EDIT
    $(document).on('click', '.edit-expense', function(){
        openEditExpense($(this).data('id'));
    });

    function openEditExpense(id) {
        const exp = _allExpenses.find(x => x.id == id);
        if(!exp) return;
        const f = $('#expenseForm');
        f.find('input[name="id"]').val(exp.id);
        f.find('input[name="description"]').val(exp.description);
        f.find('input[name="amount"]').val(exp.amount);
        f.find('select[name="category_id"]').val(exp.category_id || 0);
        f.find('input[name="expense_date"]').val(exp.expense_date);
        f.find('select[name="payment_method"]').val(exp.payment_method);
        f.find('textarea[name="notes"]').val(exp.notes || '');
        $('#expenseModalTitle').text('📝 Edit Expense');
        $('#addExpenseModal').addClass('active');
    }

    // SAVE (Add/Edit)
    $('#expenseForm').on('submit', function(e){
        e.preventDefault();
        const data = $(this).serialize() + '&action=save_expense';
        const btn = $(this).find('button[type="submit"]');
        btn.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin"></i> Saving...');
        
        $.post('controllers/ExpenseController.php', data, function(r){
            btn.prop('disabled', false).html('<i class="fa-solid fa-check"></i> Save Expense');
            if(r.status === 'success'){
                $('#addExpenseModal').removeClass('active');
                Swal.fire({icon:'success', title:'Success', text: r.message, timer:1500, showConfirmButton:false});
                loadExpenses();
                loadStats();
                loadCategories();
            } else {
                Swal.fire('Error', r.message, 'error');
            }
        }, 'json').fail(function(){
            btn.prop('disabled', false).html('<i class="fa-solid fa-check"></i> Save Expense');
            Swal.fire('Error', 'Network error. Please try again.', 'error');
        });
    });

    // DELETE
    $(document).on('click', '.delete-expense', function(){
        const id = $(this).data('id');
        Swal.fire({
            title: 'Delete Expense?',
            text: "This action cannot be undone.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            confirmButtonText: 'Yes, Delete'
        }).then((result) => {
            if (result.isConfirmed) {
                $.post('controllers/ExpenseController.php', {action: 'delete_expense', id: id}, function(r){
                    if(r.status === 'success'){
                        Swal.fire({icon:'success', title:'Deleted', text: r.message, timer:1500, showConfirmButton:false});
                        loadExpenses();
                        loadStats();
                        loadCategories();
                    } else {
                        Swal.fire('Error', r.message, 'error');
                    }
                }, 'json');
            }
        });
    });

    // =============================
    // INLINE CATEGORY CREATION
    // =============================
    $('#inlineAddCategoryBtn').on('click', function(){
        _inlineMode = true;
        $('#categoryForm')[0].reset();
        $('#categoryForm input[name="id"]').val(0);
        $('#catModalTitle').text('📂 Quick Add Category');
        $('#addCategoryModal').addClass('active');
    });

    // =============================
    // CATEGORY CRUD
    // =============================

    // ADD (from Categories tab)
    $('#openAddCategoryBtn').on('click', function(){
        _inlineMode = false;
        $('#categoryForm')[0].reset();
        $('#categoryForm input[name="id"]').val(0);
        $('#catModalTitle').text('📂 Add Expense Category');
        $('#addCategoryModal').addClass('active');
    });

    // EDIT
    $(document).on('click', '.edit-cat', function(){
        _inlineMode = false;
        const f = $('#categoryForm');
        f.find('input[name="id"]').val($(this).data('id'));
        f.find('input[name="name"]').val($(this).data('name'));
        f.find('textarea[name="description"]').val($(this).data('desc'));
        $('#catModalTitle').text('📂 Edit Category');
        $('#addCategoryModal').addClass('active');
    });

    // SAVE (Add/Edit)
    $('#categoryForm').on('submit', function(e){
        e.preventDefault();
        const data = $(this).serialize() + '&action=save_category';
        const btn = $(this).find('button[type="submit"]');
        btn.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin"></i> Saving...');

        $.post('controllers/ExpenseController.php', data, function(r){
            btn.prop('disabled', false).html('<i class="fa-solid fa-check"></i> Save Category');
            if(r.status === 'success'){
                $('#addCategoryModal').removeClass('active');
                Swal.fire({icon:'success', title:'Success', text: r.message, timer:1500, showConfirmButton:false});
                // Reload categories in dropdowns
                loadCategories();
                // If inline mode, re-show the expense modal (it was behind this one)
                if(_inlineMode){
                    _inlineMode = false;
                    // Quick re-fetch categories, then set the new one as selected
                    $.post('controllers/ExpenseController.php', {action:'get_categories'}, function(cr){
                        if(cr.status === 'success' && cr.data.length > 0){
                            // Select the most recently added category
                            const lastCat = cr.data[cr.data.length - 1];
                            setTimeout(function(){
                                $('#modalExpenseCategory').val(lastCat.id);
                            }, 300);
                        }
                    }, 'json');
                }
            } else {
                Swal.fire('Error', r.message, 'error');
            }
        }, 'json').fail(function(){
            btn.prop('disabled', false).html('<i class="fa-solid fa-check"></i> Save Category');
            Swal.fire('Error', 'Network error. Please try again.', 'error');
        });
    });

    // DELETE CATEGORY
    $(document).on('click', '.delete-cat', function(){
        const id = $(this).data('id');
        Swal.fire({
            title: 'Delete Category?',
            text: "Categories with linked expenses cannot be deleted.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            confirmButtonText: 'Yes, Delete'
        }).then((result) => {
            if (result.isConfirmed) {
                $.post('controllers/ExpenseController.php', {action: 'delete_category', id: id}, function(r){
                    if(r.status === 'success'){
                        Swal.fire({icon:'success', title:'Deleted', text: r.message, timer:1500, showConfirmButton:false});
                        loadCategories();
                    } else {
                        Swal.fire('Error', r.message, 'error');
                    }
                }, 'json');
            }
        });
    });

    // =============================
    // MODAL CLOSE HANDLERS
    // =============================
    $(document).on('click', '.modal-close, .modal-cancel', function(){
        $(this).closest('.modal-overlay').removeClass('active');
    });
    // Close on overlay click
    $(document).on('click', '.modal-overlay', function(e){
        if(e.target === this) $(this).removeClass('active');
    });
});
</script>
