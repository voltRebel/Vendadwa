<!-- Top Bar -->
<div class="top-bar">
    <div class="top-bar-left">
        <button class="mobile-menu-toggle" id="mobileMenuToggle"><i class="fa-solid fa-bars"></i></button>
        <h1>Returns & Refunds</h1>
    </div>
    <div class="top-bar-right">
        <button class="btn btn-primary" id="btnNewReturn">
            <i class="fa-solid fa-plus"></i> New Return
        </button>
    </div>
</div>

<!-- Stats -->
<div class="stats-grid">
    <div class="stat-card pink">
        <div class="stat-icon pink"><i class="fa-solid fa-rotate-left"></i></div>
        <div class="stat-info"><h3 id="statTotalReturns">0</h3><p>Total Returns (Today)</p></div>
    </div>
    <div class="stat-card purple">
        <div class="stat-icon purple"><i class="fa-solid fa-hand-holding-dollar"></i></div>
        <div class="stat-info"><h3 id="statTodayRefunded">GH₵0.00</h3><p>Today Refunded</p></div>
    </div>
    <div class="stat-card gold">
        <div class="stat-icon gold"><i class="fa-solid fa-clock-rotate-left"></i></div>
        <div class="stat-info"><h3 id="statPendingRefunds">0</h3><p>Pending Refunds</p></div>
    </div>
</div>

<div class="glass-card-static">
    <div class="tab-nav">
        <button class="tab-btn active" data-tab="tab-returns">Sales Returns</button>
        <button class="tab-btn" data-tab="tab-refunds">Refund Processing</button>
        <button class="tab-btn" data-tab="tab-void">Void Transactions</button>
    </div>

    <!-- Sales Returns -->
    <div class="tab-content active" id="tab-returns">
        <div class="d-flex align-center justify-between mb-16">
            <div class="search-bar"><i class="fa-solid fa-magnifying-glass search-icon"></i><input type="text" id="returnSearch" placeholder="Search returns..."></div>
        </div>
        <div class="table-container">
            <table class="data-table" id="returnsTable">
                <thead><tr><th>#</th><th>Return #</th><th>Original Receipt</th><th>Customer</th><th>Items</th><th>Amount</th><th>Reason</th><th>Status</th><th>Date</th></tr></thead>
                <tbody id="returnsTbody">
                    <tr><td colspan="9" style="text-align:center;padding:30px;color:var(--text-muted);"><i class="fa-solid fa-spinner fa-spin"></i> Loading...</td></tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Refund Processing -->
    <div class="tab-content" id="tab-refunds">
        <div class="d-flex align-center justify-between mb-16">
            <h3>Process Refund</h3>
        </div>
        <div style="max-width:500px;">
            <div class="form-group"><label class="form-label">Return Number</label>
                <div class="d-flex gap-8">
                    <input type="text" id="refundReturnNo" class="form-control" placeholder="e.g. RTN-000000">
                    <button class="btn btn-secondary" id="findReturnBtn">Find</button>
                </div>
            </div>
            
            <div id="refundDetails" style="display:none; margin-top:16px;">
                <div class="alert alert-info mb-16">
                    <div class="fw-600" id="refundTargetName">Customer Name</div>
                    <div style="font-size:0.85rem;">Pending Amount: <strong class="text-danger" id="refundTargetAmount">GH₵0.00</strong></div>
                </div>

                <div class="form-group"><label class="form-label">Refund Method</label>
                    <div class="d-flex gap-8" id="refundMethodGroup">
                        <button type="button" class="btn btn-outline flex-1 method-btn active" data-method="Cash"><i class="fa-solid fa-money-bill"></i> Cash</button>
                        <button type="button" class="btn btn-outline flex-1 method-btn" data-method="Mobile Money"><i class="fa-solid fa-mobile-screen"></i> MoMo</button>
                        <button type="button" class="btn btn-outline flex-1 method-btn" data-method="Store Credit"><i class="fa-solid fa-ticket"></i> Credit</button>
                    </div>
                </div>
                <div class="form-group"><label class="form-label">Confirm Amount</label>
                    <input type="number" id="refundFinalAmount" class="form-control" step="0.01">
                </div>
                <button class="btn btn-primary w-100" id="confirmRefundBtn"><i class="fa-solid fa-check"></i> Process Refund</button>
            </div>
        </div>
    </div>

    <!-- Void Transactions -->
    <div class="tab-content" id="tab-void">
        <div class="d-flex align-center justify-between mb-16">
            <h3>Void Transaction</h3>
        </div>
        <form id="voidForm">
            <div class="alert alert-warning mb-16">
                <i class="fa-solid fa-triangle-exclamation"></i>
                Voiding a transaction will permanently cancel the sale. This action requires admin approval.
            </div>
            <div style="max-width:500px;">
                <div class="form-group"><label class="form-label">Transaction / Receipt Number</label><input type="text" name="receipt_no" class="form-control" placeholder="e.g. REC-2024..." required></div>
                <div class="form-group"><label class="form-label">Reason for Void</label><textarea name="reason" class="form-control" placeholder="Why is this transaction being voided?" required></textarea></div>
                <div class="form-group"><label class="form-label">Admin Password</label><input type="password" name="admin_password" class="form-control" placeholder="Enter admin password to authorize" required></div>
                <button type="submit" class="btn btn-danger"><i class="fa-solid fa-ban"></i> Void Transaction</button>
            </div>
        </form>
    </div>
</div>

<!-- New Return Modal -->
<div class="modal-overlay" id="newReturnModal">
    <div class="modal modal-lg">
        <div class="modal-header">
            <h3>🔄 Process New Return</h3>
            <button class="modal-close">&times;</button>
        </div>
        <div class="modal-body p-24">
            <div class="d-flex gap-12 mb-20">
                <div class="flex-1">
                    <label class="form-label">Find Original Receipt</label>
                    <div class="d-flex gap-8">
                        <input type="text" id="findReceiptNo" class="form-control" placeholder="REC-...">
                        <button class="btn btn-primary" id="searchReceiptBtn">Search</button>
                    </div>
                </div>
            </div>

            <div id="returnPrepArea" style="display:none;">
                <div class="alert alert-info mb-16 d-flex justify-between align-center">
                    <div>
                        <span class="fw-700" id="retSaleReceipt"></span> — 
                        <span id="retSaleCustomer"></span>
                    </div>
                    <div class="fw-600" id="retSaleTotal"></div>
                </div>

                <div class="table-container mb-16" style="max-height:300px;overflow-y:auto;">
                    <table class="data-table" id="returnItemsTable">
                        <thead><tr><th>#</th><th>Item</th><th>Price</th><th>Qty Sold</th><th>Qty to Return</th><th>Condition</th></tr></thead>
                        <tbody></tbody>
                    </table>
                </div>

                <div class="form-group">
                    <label class="form-label">Return Reason</label>
                    <textarea id="returnReason" class="form-control" placeholder="Why is the customer returning these items?"></textarea>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary modal-cancel">Cancel</button>
            <button class="btn btn-primary" id="submitReturnBtn" style="display:none;"><i class="fa-solid fa-check"></i> Complete Return</button>
        </div>
    </div>
</div>

<script>
$(function(){

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

    // Tab switching
    $('.tab-btn').on('click', function(){
        $('.tab-btn, .tab-content').removeClass('active');
        $(this).addClass('active');
        $('#' + $(this).data('tab')).addClass('active');
        if($(this).data('tab') === 'tab-returns') loadReturnsHistory();
    });

    function loadReturnsHistory() {
        $.post('controllers/ReturnsController.php', {action: 'get_returns_history'}, function(r){
            if(r.status === 'success'){
                let html = '';
                if(r.data.length === 0){
                    html = '<tr><td colspan="9" style="text-align:center;padding:40px;color:var(--text-muted);">No returns found.</td></tr>';
                } else {
                    r.data.forEach((x, index) => {
                        const date = new Date(x.created_at).toLocaleDateString();
                        const statusBadge = x.status === 'refunded' ? 'badge-success' : 'badge-warning';
                        html += `<tr>
                            <td>${index + 1}</td>
                            <td class="fw-600">${x.return_number}</td>
                            <td>${x.receipt_no}</td>
                            <td>${x.customer_name || 'Walk-in'}</td>
                            <td><span class="badge badge-teal">${x.items_count} items</span></td>
                            <td class="fw-600 text-danger">-GH₵${parseFloat(x.total_amount).toFixed(2)}</td>
                            <td style="font-size:0.8rem;">${x.reason}</td>
                            <td><span class="badge ${statusBadge}">${x.status}</span></td>
                            <td>${date}</td>
                        </tr>`;
                    });
                }
                $('#returnsTbody').html(html);
                initDataTable('#returnsTable');
            }
        },'json');
    }

    $('#returnSearch').on('keyup', function(){
        if($.fn.DataTable.isDataTable('#returnsTable')) {
            $('#returnsTable').DataTable().search($(this).val()).draw();
        }
    });

    function loadStats() {
        $.post('controllers/ReturnsController.php', {action: 'get_return_stats'}, function(r){
            if(r.status === 'success'){
                $('#statTotalReturns').text(r.data.total_returns);
                $('#statTodayRefunded').text('GH₵' + parseFloat(r.data.today_refunded).toFixed(2));
                $('#statPendingRefunds').text(r.data.pending_refunds);
            }
        }, 'json');
    }

    loadReturnsHistory();
    loadStats();

    // New Return Flow
    $('#openNewReturnBtn').on('click', function(){
        $('#returnPrepArea').hide();
        $('#submitReturnBtn').hide();
        $('#findReceiptNo').val('');
        $('#newReturnModal').addClass('active');
    });

    var _currentReturnSale = null;

    $('#searchReceiptBtn').on('click', function(){
        const rno = $('#findReceiptNo').val();
        if(!rno) return;
        $(this).prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin"></i>');
        
        $.post('controllers/ReturnsController.php', {action: 'get_sale_details', receipt_no: rno}, function(r){
            $('#searchReceiptBtn').prop('disabled', false).text('Search');
            if(r.status === 'success'){
                _currentReturnSale = r.sale;
                $('#retSaleReceipt').text(r.sale.receipt_no);
                $('#retSaleCustomer').text(r.sale.customer_name || 'Walk-in');
                $('#retSaleTotal').text('GH₵' + parseFloat(r.sale.total_amount).toFixed(2));
                
                let html = '';
                r.items.forEach((it, index) => {
                    html += `<tr class="ret-row" data-pid="${it.product_id}" data-price="${it.unit_price}">
                        <td>${index + 1}</td>
                        <td class="fw-600">${it.product_name}</td>
                        <td>GH₵${parseFloat(it.unit_price).toFixed(2)}</td>
                        <td>${it.qty}</td>
                        <td style="width:100px;"><input type="number" class="form-control ret-qty" value="0" min="0" max="${it.qty}"></td>
                        <td>
                            <select class="form-control ret-cond" style="padding:4px 8px; font-size:0.8rem;">
                                <option value="restock">Restock</option>
                                <option value="damaged">Damage</option>
                                <option value="used">Used</option>
                            </select>
                        </td>
                    </tr>`;
                });
                $('#returnItemsTable tbody').html(html);
                $('#returnPrepArea').fadeIn();
                $('#submitReturnBtn').show();
            } else {
                Swal.fire({icon:'error', title:'Not Found', text: r.message});
            }
        },'json');
    });

    $('#submitReturnBtn').on('click', function(){
        const items = [];
        $('.ret-row').each(function(){
            const q = parseInt($(this).find('.ret-qty').val());
            if(q > 0){
                items.push({
                    product_id: $(this).data('pid'),
                    price: $(this).data('price'),
                    qty: q,
                    condition: $(this).find('.ret-cond').val()
                });
            }
        });

        if(items.length === 0){
            Swal.fire({icon:'warning', title:'Empty Return', text:'Please specify at least 1 item to return.'});
            return;
        }

        const data = {
            action: 'process_return',
            sale_id: _currentReturnSale.id,
            reason: $('#returnReason').val(),
            items: JSON.stringify(items)
        };

        $(this).prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin"></i> Processing...');
        
        $.post('controllers/ReturnsController.php', data, function(r){
            $('#submitReturnBtn').prop('disabled', false).html('<i class="fa-solid fa-check"></i> Complete Return');
            if(r.status === 'success'){
                $('#newReturnModal').removeClass('active');
                Swal.fire({icon:'success', title:'Return Processed!', text: 'Return Number: ' + r.return_number}).then(() => {
                    loadReturnsHistory();
                    location.reload();
                });
            } else {
                Swal.fire({icon:'error', title:'Error', text: r.message});
            }
        },'json');
    });

    // Refund Processing
    var _currentRefundReturn = null;
    $('#findReturnBtn').on('click', function(){
        const rno = $('#refundReturnNo').val();
        if(!rno) return;
        // Since we don't have a single lookup, we'll search the history data we have or just rely on a new check in controller
        // For simplicity, let's keep it robust and fetch via AJAX
        $.post('controllers/ReturnsController.php', {action: 'get_returns_history'}, function(r){
            if(r.status === 'success'){
                const found = r.data.find(x => x.return_number === rno);
                if(found && found.status === 'pending'){
                    _currentRefundReturn = found;
                    $('#refundTargetName').text(found.customer_name || 'Walk-in Customer');
                    $('#refundTargetAmount').text('GH₵' + parseFloat(found.total_amount).toFixed(2));
                    $('#refundFinalAmount').val(found.total_amount);
                    $('#refundDetails').fadeIn();
                } else if(found && found.status === 'refunded'){
                    Swal.fire({icon:'info', title:'Already Refunded', text:'This return has already been processed.'});
                } else {
                    Swal.fire({icon:'error', title:'Not Found', text:'Invalid return number or return already processed.'});
                }
            }
        },'json');
    });

    $('#refundMethodGroup .method-btn').on('click', function(){
        $('#refundMethodGroup .method-btn').removeClass('active');
        $(this).addClass('active');
    });

    $('#confirmRefundBtn').on('click', function(){
        const data = {
            action: 'process_refund',
            return_id: _currentRefundReturn.id,
            refund_method: $('#refundMethodGroup .method-btn.active').data('method'),
            amount: $('#refundFinalAmount').val()
        };

        $(this).prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin"></i> Processing...');
        $.post('controllers/ReturnsController.php', data, function(r){
            $('#confirmRefundBtn').prop('disabled', false).html('<i class="fa-solid fa-check"></i> Process Refund');
            if(r.status === 'success'){
                Swal.fire({icon:'success', title:'Refund Complete', text: 'Payment has been recorded.'}).then(() => location.reload());
            } else {
                Swal.fire({icon:'error', title:'Error', text: r.message});
            }
        },'json');
    });

    // Void Transaction
    $('#voidForm').on('submit', function(e){
        e.preventDefault();
        const data = $(this).serialize() + '&action=void_transaction';
        
        Swal.fire({
            title: 'Are you sure?',
            text: "This will permanently void the sale and restore stock. This CANNOT be undone.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            confirmButtonText: 'Yes, Void Sale'
        }).then((result) => {
            if (result.isConfirmed) {
                const btn = $(this).find('button[type="submit"]');
                btn.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin"></i> Voiding...');
                
                $.post('controllers/ReturnsController.php', data, function(r){
                    btn.prop('disabled', false).html('<i class="fa-solid fa-ban"></i> Void Transaction');
                    if(r.status === 'success'){
                        Swal.fire({icon:'success', title:'Voided!', text: r.message}).then(() => location.reload());
                    } else {
                        Swal.fire({icon:'error', title:'Unauthorized', text: r.message});
                    }
                },'json');
            }
        });
    });

    // Close Modals
    $(document).on('click', '.modal-close, .modal-cancel', function(){
        $('.modal-overlay').removeClass('active');
    });
});
</script>

<style>
.method-btn.active {
    background: var(--primary-500);
    color: #fff;
    border-color: var(--primary-500);
}
.method-btn {
    transition: all 0.2s;
    font-size: 0.85rem;
    padding: 12px 6px;
}
.method-btn i {
    display: block;
    font-size: 1.2rem;
    margin-bottom: 6px;
}
</style>

