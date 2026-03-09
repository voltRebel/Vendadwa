<!-- Top Bar -->
<div class="top-bar">
    <div class="top-bar-left">
        <button class="mobile-menu-toggle" id="mobileMenuToggle"><i class="fa-solid fa-bars"></i></button>
        <h1>Reports</h1>
    </div>
    <div class="top-bar-right">
        <div class="d-flex gap-8 align-center" style="flex-wrap:wrap;">
            <input type="date" id="startDate" class="form-control" style="width:auto;padding:8px 12px;font-size:0.82rem;">
            <span class="text-muted">to</span>
            <input type="date" id="endDate" class="form-control" style="width:auto;padding:8px 12px;font-size:0.82rem;">
            <button class="btn btn-primary btn-sm" id="filterBtn"><i class="fa-solid fa-filter"></i> Filter</button>
        </div>
    </div>
</div>

<!-- Report Selection Cards -->
<div class="report-grid mb-24">
    <div class="report-card" onclick="document.querySelector('[data-tab=tab-salesreport]').click()">
        <div class="report-icon" style="background:var(--primary-50);color:var(--primary-500);"><i class="fa-solid fa-chart-bar"></i></div>
        <h4>Sales Report</h4>
        <p>Revenue, items sold, payment breakdown</p>
    </div>
    <div class="report-card" onclick="document.querySelector('[data-tab=tab-daily]').click()">
        <div class="report-icon" style="background:var(--secondary-50);color:var(--secondary-500);"><i class="fa-solid fa-calendar-day"></i></div>
        <h4>Daily Summary</h4>
        <p>Z-Report / End of Day</p>
    </div>
    <div class="report-card" onclick="document.querySelector('[data-tab=tab-inventory]').click()">
        <div class="report-icon" style="background:var(--accent-gold-lighter);color:var(--accent-gold);"><i class="fa-solid fa-boxes-stacked"></i></div>
        <h4>Inventory Report</h4>
        <p>Stock levels & valuation</p>
    </div>
    <div class="report-card" onclick="document.querySelector('[data-tab=tab-profit]').click()">
        <div class="report-icon" style="background:var(--success-bg);color:var(--success);"><i class="fa-solid fa-money-bill-trend-up"></i></div>
        <h4>Profit & Loss</h4>
        <p>Income vs expenses</p>
    </div>
    <div class="report-card" onclick="document.querySelector('[data-tab=tab-cashier]').click()">
        <div class="report-icon" style="background:var(--info-bg);color:var(--info);"><i class="fa-solid fa-user-clock"></i></div>
        <h4>Cashier Report</h4>
        <p>Per-user sales performance</p>
    </div>
</div>

<!-- Report Detail Tabs -->
<div class="glass-card-static">
    <div class="tab-nav">
        <button class="tab-btn active" data-tab="tab-salesreport">Sales</button>
        <button class="tab-btn" data-tab="tab-daily">Daily Summary</button>
        <button class="tab-btn" data-tab="tab-inventory">Inventory</button>
        <button class="tab-btn" data-tab="tab-profit">Profit & Loss</button>
        <button class="tab-btn" data-tab="tab-cashier">Cashier</button>
    </div>

<!-- ============================== -->
<!-- SALES REPORT TAB -->
<!-- ============================== -->
<div class="tab-content active" id="tab-salesreport">
    <div class="stats-grid mb-16">
        <div class="stat-card pink">
            <div class="stat-icon pink"><i class="fa-solid fa-dollar-sign"></i></div>
            <div class="stat-info"><h3 id="reportTotalRevenue">GH₵0.00</h3><p>Total Revenue</p></div>
        </div>
        <div class="stat-card purple">
            <div class="stat-icon purple"><i class="fa-solid fa-receipt"></i></div>
            <div class="stat-info"><h3 id="reportTotalTransactions">0</h3><p>Transactions</p></div>
        </div>
        <div class="stat-card green">
            <div class="stat-icon green"><i class="fa-solid fa-arrow-trend-up"></i></div>
            <div class="stat-info"><h3 id="reportAvgSale">GH₵0.00</h3><p>Avg. Transaction</p></div>
        </div>
    </div>
    <div class="d-flex justify-between align-center mb-16" style="flex-wrap:wrap;gap:10px;">
        <div class="search-bar"><i class="fa-solid fa-magnifying-glass search-icon"></i><input type="text" id="salesSearch" placeholder="Search receipt # or customer..."></div>
        <div class="d-flex gap-8">
            <select id="salesPaymentFilter" class="form-control" style="width:auto;padding:8px 34px 8px 12px;font-size:0.82rem;">
                <option value="">All Payments</option>
                <option value="Cash">Cash</option>
                <option value="Card">Card</option>
                <option value="Mobile Money">Mobile Money</option>
            </select>
        </div>
    </div>
    <div class="table-container">
        <table class="data-table">
            <thead><tr><th>#</th><th>Receipt #</th><th>Customer</th><th>Items</th><th>Payment</th><th>Date</th><th>Amount</th></tr></thead>
            <tbody id="salesBreakdownTable">
            </tbody>
        </table>
    </div>
</div>

<!-- ============================== -->
<!-- DAILY SUMMARY (Z-REPORT) TAB -->
<!-- ============================== -->
<div class="tab-content" id="tab-daily">
    <div class="d-flex justify-between align-center mb-16" style="flex-wrap:wrap;gap:10px;">
        <h3>Daily Z-Report — <span id="zReportDateLabel"><?= date('M d, Y') ?></span></h3>
        <div class="d-flex gap-8">
            <input type="date" id="zReportDate" class="form-control" style="width:auto;padding:8px 12px;font-size:0.82rem;" value="<?= date('Y-m-d') ?>">
            <button class="btn btn-sm btn-primary" onclick="window.print()"><i class="fa-solid fa-print"></i> Print</button>
        </div>
    </div>

    <!-- Summary Banner Cards -->
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-bottom:24px;">
        <div style="background:linear-gradient(135deg, var(--primary-50), var(--secondary-50)); border-radius:var(--radius-md); padding:24px; text-align:center;">
            <div style="font-size:0.7rem;font-weight:600;text-transform:uppercase;letter-spacing:1px;color:var(--primary-400);margin-bottom:6px;">Net Sales Today</div>
            <div style="font-size:1.75rem;font-weight:800;color:var(--primary-600);" id="zNetSales">GH₵0.00</div>
        </div>
        <div style="background:linear-gradient(135deg, #fef2f2, #fecdd3); border-radius:var(--radius-md); padding:24px; text-align:center;">
            <div style="font-size:0.7rem;font-weight:600;text-transform:uppercase;letter-spacing:1px;color:#ef4444;margin-bottom:6px;">Total Deductions</div>
            <div style="font-size:1.75rem;font-weight:800;color:#ef4444;" id="zTotalDeductions">GH₵0.00</div>
        </div>
        <div style="background:linear-gradient(135deg, var(--success-bg), #d1fae5); border-radius:var(--radius-md); padding:24px; text-align:center;">
            <div style="font-size:0.7rem;font-weight:600;text-transform:uppercase;letter-spacing:1px;color:var(--success);margin-bottom:6px;">Expected Cash in Till</div>
            <div style="font-size:1.75rem;font-weight:800;color:var(--success);" id="zExpectedCash">GH₵0.00</div>
        </div>
    </div>

    <!-- Two-Column Detail -->
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
        <!-- Left: Payment Breakdown -->
        <div style="border:1px solid var(--border-light, rgba(255,255,255,0.08)); border-radius:var(--radius-md); padding:20px;">
            <h4 style="margin:0 0 16px 0;font-size:0.88rem;display:flex;align-items:center;gap:8px;"><i class="fa-solid fa-money-bills" style="color:var(--success);"></i>Payment Breakdown</h4>
            <div class="table-container" style="margin:0;">
                <table class="data-table" style="margin:0;">
                    <tbody>
                        <tr><td>Cash Sales</td><td style="text-align:right;" class="fw-600 text-success" id="zCashSales">GH₵0.00</td></tr>
                        <tr><td>Card Sales</td><td style="text-align:right;" class="fw-600" id="zCardSales">GH₵0.00</td></tr>
                        <tr><td>Mobile Money</td><td style="text-align:right;" class="fw-600" id="zMobileSales">GH₵0.00</td></tr>
                        <tr><td>Other Payments</td><td style="text-align:right;" class="fw-600" id="zOtherSales">GH₵0.00</td></tr>
                        <tr><td>Manual Purchases</td><td style="text-align:right;" class="fw-600" id="zManualSales">GH₵0.00</td></tr>
                    </tbody>
                    <tfoot>
                        <tr style="border-top:2px solid var(--border-light, rgba(255,255,255,0.15));">
                            <td class="fw-700">Gross Sales</td>
                            <td style="text-align:right;" class="fw-700" id="zGrossSales">GH₵0.00</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Right: Deductions & Activity -->
        <div style="border:1px solid var(--border-light, rgba(255,255,255,0.08)); border-radius:var(--radius-md); padding:20px;">
            <h4 style="margin:0 0 16px 0;font-size:0.88rem;display:flex;align-items:center;gap:8px;"><i class="fa-solid fa-receipt" style="color:#ef4444;"></i>Deductions & Activity</h4>
            <div class="table-container" style="margin:0;">
                <table class="data-table" style="margin:0;">
                    <tbody>
                        <tr><td>Refunds <span class="badge badge-danger" style="font-size:0.65rem;padding:2px 6px;" id="zRefundCount">0</span></td><td style="text-align:right;" class="fw-600 text-danger" id="zRefunds">-GH₵0.00</td></tr>
                        <tr><td>Expenses <span class="badge badge-warning" style="font-size:0.65rem;padding:2px 6px;" id="zExpenseCount">0</span></td><td style="text-align:right;" class="fw-600 text-danger" id="zExpenses">-GH₵0.00</td></tr>
                    </tbody>
                    <tfoot>
                        <tr style="border-top:2px solid var(--border-light, rgba(255,255,255,0.15));">
                            <td class="fw-700">Transactions</td>
                            <td style="text-align:right;" class="fw-700" id="zTransactionCount">0</td>
                        </tr>
                        <tr>
                            <td class="fw-700">Items Sold</td>
                            <td style="text-align:right;" class="fw-700" id="zItemsSold">0</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- ============================== -->
<!-- INVENTORY REPORT TAB -->
<!-- ============================== -->
<div class="tab-content" id="tab-inventory">
    <div class="stats-grid mb-16">
        <div class="stat-card pink">
            <div class="stat-icon pink"><i class="fa-solid fa-boxes-stacked"></i></div>
            <div class="stat-info"><h3 id="invTotalItems">0</h3><p>Total Units in Stock</p></div>
        </div>
        <div class="stat-card purple">
            <div class="stat-icon purple"><i class="fa-solid fa-coins"></i></div>
            <div class="stat-info"><h3 id="invTotalValue">GH₵0.00</h3><p>Inventory Value (Cost)</p></div>
        </div>
        <div class="stat-card gold">
            <div class="stat-icon gold"><i class="fa-solid fa-triangle-exclamation"></i></div>
            <div class="stat-info"><h3 id="invLowStockCount">0</h3><p>Low Stock Items</p></div>
        </div>
    </div>
    <div class="d-flex justify-between align-center mb-16">
        <h3>Stock Levels</h3>
        <div class="d-flex gap-8">
            <select id="invFilter" class="form-control" style="width:auto;padding:8px 34px 8px 12px;font-size:0.82rem;">
                <option value="all">All Items</option>
                <option value="low">Low Stock Only</option>
                <option value="out">Out of Stock</option>
            </select>
        </div>
    </div>
    <div class="table-container">
        <table class="data-table">
            <thead><tr><th>#</th><th>Product</th><th>Category</th><th>In Stock</th><th>Min Level</th><th>Cost Price</th><th>Stock Value</th><th>Status</th></tr></thead>
            <tbody id="inventoryReportTable">
            </tbody>
        </table>
    </div>
</div>

<!-- ============================== -->
<!-- PROFIT & LOSS TAB -->
<!-- ============================== -->
<div class="tab-content" id="tab-profit">
    <!-- Summary Banner Cards -->
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-bottom:28px;">
        <div style="background:linear-gradient(135deg, var(--success-bg), #d1fae5); border-radius:var(--radius-md); padding:24px; text-align:center;">
            <div style="font-size:0.7rem;font-weight:600;text-transform:uppercase;letter-spacing:1px;color:var(--success);margin-bottom:6px;">Gross Revenue</div>
            <div style="font-size:1.75rem;font-weight:800;color:var(--success);" id="plGrossRevenue">GH₵0.00</div>
        </div>
        <div style="background:linear-gradient(135deg, #fef2f2, #fecdd3); border-radius:var(--radius-md); padding:24px; text-align:center;">
            <div style="font-size:0.7rem;font-weight:600;text-transform:uppercase;letter-spacing:1px;color:#ef4444;margin-bottom:6px;">Total Costs</div>
            <div style="font-size:1.75rem;font-weight:800;color:#ef4444;" id="plTotalCosts">GH₵0.00</div>
        </div>
        <div style="border-radius:var(--radius-md); padding:24px; text-align:center;" id="plNetProfitCard">
            <div style="font-size:0.7rem;font-weight:600;text-transform:uppercase;letter-spacing:1px;margin-bottom:6px;" id="plNetProfitLabel">Net Profit</div>
            <div style="font-size:1.75rem;font-weight:800;" id="plNetProfit">GH₵0.00</div>
        </div>
    </div>

    <!-- Financial Statement -->
    <div style="border:1px solid var(--border-light, rgba(255,255,255,0.08)); border-radius:var(--radius-md); overflow:hidden;">

        <!-- INCOME SECTION HEADER -->
        <div style="background:linear-gradient(135deg, rgba(16,185,129,0.08), rgba(16,185,129,0.03)); padding:14px 24px; border-bottom:1px solid var(--border-light, rgba(255,255,255,0.08)); display:flex; align-items:center; gap:10px;">
            <i class="fa-solid fa-arrow-trend-up" style="color:var(--success);font-size:0.9rem;"></i>
            <span style="font-weight:700;font-size:0.88rem;color:var(--success);">Revenue & Income</span>
        </div>
        <div style="padding:0 24px;">
            <div style="display:flex;justify-content:space-between;align-items:center;padding:14px 0;border-bottom:1px solid var(--border-light, rgba(255,255,255,0.05));">
                <span>Gross Revenue</span>
                <span class="fw-600" id="plGrossRev2">GH₵0.00</span>
            </div>
            <div style="display:flex;justify-content:space-between;align-items:center;padding:14px 0;border-bottom:1px solid var(--border-light, rgba(255,255,255,0.05));">
                <span style="padding-left:16px;color:var(--text-secondary);">Less: Refunds & Returns</span>
                <span class="fw-600 text-danger" id="plRefunds">-GH₵0.00</span>
            </div>
            <div style="display:flex;justify-content:space-between;align-items:center;padding:14px 0;border-bottom:1px solid var(--border-light, rgba(255,255,255,0.05));background:rgba(16,185,129,0.03);margin:0 -24px;padding-left:24px;padding-right:24px;">
                <span class="fw-700">Net Revenue</span>
                <span class="fw-700" id="plNetRevenue">GH₵0.00</span>
            </div>
            <div style="display:flex;justify-content:space-between;align-items:center;padding:14px 0;border-bottom:1px solid var(--border-light, rgba(255,255,255,0.05));">
                <span style="padding-left:16px;color:var(--text-secondary);">Less: Cost of Goods Sold (COGS)</span>
                <span class="fw-600 text-danger" id="plCogs">-GH₵0.00</span>
            </div>
            <div style="display:flex;justify-content:space-between;align-items:center;padding:16px 0;background:rgba(16,185,129,0.06);margin:0 -24px;padding-left:24px;padding-right:24px;border-bottom:1px solid var(--border-light, rgba(255,255,255,0.08));">
                <span class="fw-700" style="font-size:0.95rem;color:var(--success);">💰 Gross Profit</span>
                <span class="fw-700" style="font-size:1.05rem;color:var(--success);" id="plGrossProfit">GH₵0.00</span>
            </div>
        </div>

        <!-- EXPENSES SECTION HEADER -->
        <div style="background:linear-gradient(135deg, rgba(239,68,68,0.08), rgba(239,68,68,0.03)); padding:14px 24px; border-bottom:1px solid var(--border-light, rgba(255,255,255,0.08)); display:flex; align-items:center; gap:10px;">
            <i class="fa-solid fa-arrow-trend-down" style="color:#ef4444;font-size:0.9rem;"></i>
            <span style="font-weight:700;font-size:0.88rem;color:#ef4444;">Operating Expenses</span>
        </div>
        <div style="padding:0 24px;" id="plExpenseBreakdown">
            <div style="display:flex;justify-content:center;padding:20px 0;color:var(--text-muted);font-style:italic;font-size:0.85rem;">No expenses recorded.</div>
        </div>
        <div style="display:flex;justify-content:space-between;align-items:center;padding:14px 24px;background:rgba(239,68,68,0.05);border-top:1px solid var(--border-light, rgba(255,255,255,0.08));border-bottom:1px solid var(--border-light, rgba(255,255,255,0.08));">
            <span class="fw-700">Total Operating Expenses</span>
            <span class="fw-700" style="color:#ef4444;" id="plTotalExpenses">GH₵0.00</span>
        </div>

        <!-- NET PROFIT / LOSS BOTTOM BAR -->
        <div id="plNetProfitBar" style="display:flex;justify-content:space-between;align-items:center;padding:18px 24px;background:linear-gradient(135deg, rgba(16,185,129,0.1), rgba(16,185,129,0.04));">
            <span style="font-weight:800;font-size:1rem;" id="plNetProfitBarLabel">📊 NET PROFIT</span>
            <span style="font-weight:800;font-size:1.2rem;" id="plNetProfitBarValue">GH₵0.00</span>
        </div>
    </div>
</div>

<!-- ============================== -->
<!-- CASHIER REPORT TAB -->
<!-- ============================== -->
<div class="tab-content" id="tab-cashier">
    <div class="d-flex justify-between align-center mb-16">
        <h3>Cashier Performance</h3>
    </div>
    <div class="table-container">
        <table class="data-table">
            <thead><tr><th>#</th><th>Cashier</th><th>Transactions</th><th>Cash Sales</th><th>Other Sales</th><th>Avg. Sale</th><th>Total Sales</th></tr></thead>
            <tbody id="cashierReportTable">
            </tbody>
        </table>
    </div>
</div>

</div> <!-- end glass-card-static -->

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

    let initializedTabs = { 'tab-salesreport': true };

    // Set default dates
    const today = new Date().toISOString().split('T')[0];
    const firstDay = new Date(new Date().getFullYear(), new Date().getMonth(), 1).toISOString().split('T')[0];
    $('#startDate').val(firstDay);
    $('#endDate').val(today);

    // Tab switching
    $('.tab-btn').on('click', function(){
        $('.tab-btn, .tab-content').removeClass('active');
        $(this).addClass('active');
        const tabId = $(this).data('tab');
        $('#' + tabId).addClass('active');
        
        if (!initializedTabs[tabId]) {
            if (tabId === 'tab-inventory') initDataTable('#tab-inventory .data-table');
            if (tabId === 'tab-cashier') initDataTable('#tab-cashier .data-table');
            initializedTabs[tabId] = true;
        }
    });

    // =============================
    // LOAD ALL REPORTS
    // =============================
    function loadAllReports(start, end) {
        loadSalesReport(start || '', end || '');
        loadDailySummary();
        loadInventoryReport();
        loadProfitLoss(start || '', end || '');
        loadCashierReport(start || '', end || '');
    }

    // =============================
    // 1. SALES REPORT
    // =============================
    function loadSalesReport(start, end) {
        $.post('controllers/AnalyticsController.php', {
            action: 'get_all_sales',
            start_date: start,
            end_date: end,
            search: $('#salesSearch').val(),
            payment_method: $('#salesPaymentFilter').val()
        }, function(r){
            if(r.status === 'success'){
                const totals = r.totals;
                $('#reportTotalRevenue').text('GH₵' + parseFloat(totals.total_revenue).toFixed(2));
                $('#reportTotalTransactions').text(totals.total_transactions);
                $('#reportAvgSale').text('GH₵' + parseFloat(totals.avg_transaction).toFixed(2));

                let html = '';
                if(r.data.length === 0){
                    html = '<tr><td colspan="7" style="text-align:center; padding:30px;color:var(--text-muted);">No sales found.</td></tr>';
                } else {
                    r.data.forEach((s, index) => {
                        const date = new Date(s.created_at).toLocaleDateString('en-US', {month:'short', day:'numeric', year:'numeric'});
                        const time = new Date(s.created_at).toLocaleTimeString('en-US', {hour:'2-digit', minute:'2-digit'});
                        const payBadge = s.payment_method === 'Cash' ? 'badge-success' :
                                         s.payment_method === 'Card' ? 'badge-info' :
                                         s.payment_method === 'Mobile Money' ? 'badge-purple' : 'badge-warning';
                        html += `<tr>
                            <td>${index + 1}</td>
                            <td class="fw-600">${s.receipt_no}</td>
                            <td>${s.customer_name || 'Walk-in'}</td>
                            <td><span class="badge badge-teal">${s.items_count} items</span></td>
                            <td><span class="badge ${payBadge}">${s.payment_method}</span></td>
                            <td><div>${date}</div><div class="text-muted" style="font-size:0.72rem;">${time}</div></td>
                            <td class="fw-700 text-pink">GH₵${parseFloat(s.total_amount).toFixed(2)}</td>
                        </tr>`;
                    });
                }
                $('#salesBreakdownTable').html(html);
                if (initializedTabs['tab-salesreport']) {
                    initDataTable('#tab-salesreport .data-table');
                }
            }
        }, 'json');
    }

    // Sales search and filter
    $('#salesSearch').on('keyup', function(){
        if ($.fn.DataTable.isDataTable('#tab-salesreport .data-table')) {
            $('#tab-salesreport .data-table').DataTable().search($(this).val()).draw();
        }
    });
    $('#salesPaymentFilter').on('change', function(){
        loadSalesReport($('#startDate').val(), $('#endDate').val());
    });

    // =============================
    // 2. DAILY SUMMARY (Z-REPORT)
    // =============================
    function loadDailySummary() {
        const date = $('#zReportDate').val();
        const d = new Date(date + 'T00:00:00');
        $('#zReportDateLabel').text(d.toLocaleDateString('en-US', { month:'short', day:'numeric', year:'numeric' }));

        $.post('controllers/AnalyticsController.php', { action: 'get_daily_summary', date: date }, function(r){
            if(r.status === 'success'){
                const d = r.data;
                const gross = d.total_sales + d.manual_sales;
                const deductions = d.total_refunds + d.total_expenses;

                $('#zNetSales').text('GH₵' + d.net_sales.toFixed(2));
                $('#zCashSales').text('GH₵' + d.cash_sales.toFixed(2));
                $('#zCardSales').text('GH₵' + d.card_sales.toFixed(2));
                $('#zMobileSales').text('GH₵' + d.mobile_sales.toFixed(2));
                $('#zOtherSales').text('GH₵' + d.other_sales.toFixed(2));
                $('#zManualSales').text('GH₵' + d.manual_sales.toFixed(2));
                $('#zGrossSales').text('GH₵' + gross.toFixed(2));

                $('#zTotalDeductions').text('GH₵' + deductions.toFixed(2));
                $('#zRefunds').text('-GH₵' + d.total_refunds.toFixed(2));
                $('#zRefundCount').text(d.refund_count);
                $('#zExpenses').text('-GH₵' + d.total_expenses.toFixed(2));
                $('#zExpenseCount').text(d.expense_count);
                $('#zTransactionCount').text(d.transaction_count);
                $('#zItemsSold').text(d.items_sold);
                $('#zExpectedCash').text('GH₵' + d.cash_sales.toFixed(2));
            }
        }, 'json');
    }

    $('#zReportDate').on('change', loadDailySummary);

    // =============================
    // 3. INVENTORY REPORT
    // =============================
    var _inventoryData = [];

    function loadInventoryReport() {
        $.post('controllers/AnalyticsController.php', { action: 'get_inventory_report' }, function(r){
            if(r.status === 'success'){
                _inventoryData = r.data.report;
                const summary = r.data.summary;
                $('#invTotalItems').text(summary.total_items || 0);
                $('#invTotalValue').text('GH₵' + parseFloat(summary.total_value || 0).toFixed(2));

                const lowCount = _inventoryData.filter(p => p.stock_quantity <= p.min_stock_level).length;
                $('#invLowStockCount').text(lowCount);

                renderInventoryTable('all');
            }
        }, 'json');
    }

    function renderInventoryTable(filter) {
        let data = _inventoryData;
        if(filter === 'low') data = data.filter(p => p.stock_quantity <= p.min_stock_level && p.stock_quantity > 0);
        if(filter === 'out') data = data.filter(p => p.stock_quantity <= 0);

        let html = '';
        if(data.length === 0){
            html = '<tr><td colspan="8" style="text-align:center; padding:30px;color:var(--text-muted);">No items match this filter.</td></tr>';
        } else {
            data.forEach((p, index) => {
                let status, statusClass;
                if(p.stock_quantity <= 0){
                    status = '<span class="badge badge-danger">Out of Stock</span>';
                    statusClass = 'text-danger fw-600';
                } else if(p.stock_quantity <= p.min_stock_level){
                    status = '<span class="badge badge-warning">Low Stock</span>';
                    statusClass = 'text-danger fw-600';
                } else {
                    status = '<span class="badge badge-success">OK</span>';
                    statusClass = '';
                }
                const stockVal = (p.stock_quantity * p.cost_price).toFixed(2);
                html += `<tr>
                    <td>${index + 1}</td>
                    <td class="fw-600">${p.name}</td>
                    <td>${p.category_name || 'Uncategorized'}</td>
                    <td class="${statusClass}">${p.stock_quantity}</td>
                    <td>${p.min_stock_level}</td>
                    <td>GH₵${parseFloat(p.cost_price).toFixed(2)}</td>
                    <td class="fw-600">GH₵${stockVal}</td>
                    <td>${status}</td>
                </tr>`;
            });
        }
        $('#inventoryReportTable').html(html);
        if (initializedTabs['tab-inventory']) {
            initDataTable('#tab-inventory .data-table');
        }
    }

    $('#invFilter').on('change', function(){
        renderInventoryTable($(this).val());
    });

    // =============================
    // 4. PROFIT & LOSS
    // =============================
    function loadProfitLoss(start, end) {
        $.post('controllers/AnalyticsController.php', { action: 'get_profit_loss', start_date: start, end_date: end }, function(r){
            if(r.status === 'success'){
                const d = r.data;
                const totalCosts = d.cogs + d.expenses + d.refunds;

                $('#plGrossRevenue').text('GH₵' + d.gross_revenue.toFixed(2));
                $('#plTotalCosts').text('GH₵' + totalCosts.toFixed(2));

                // Net profit top card
                if(d.net_profit >= 0){
                    $('#plNetProfitCard').css('background', 'linear-gradient(135deg, var(--success-bg), #d1fae5)');
                    $('#plNetProfit').css('color', 'var(--success)').text('GH₵' + d.net_profit.toFixed(2));
                    $('#plNetProfitLabel').css('color', 'var(--success)').text('Net Profit');
                } else {
                    $('#plNetProfitCard').css('background', 'linear-gradient(135deg, #fef2f2, #fecdd3)');
                    $('#plNetProfit').css('color', '#ef4444').text('-GH₵' + Math.abs(d.net_profit).toFixed(2));
                    $('#plNetProfitLabel').css('color', '#ef4444').text('Net Loss');
                }

                // Income side
                $('#plGrossRev2').text('GH₵' + d.gross_revenue.toFixed(2));
                $('#plRefunds').text('-GH₵' + d.refunds.toFixed(2));
                $('#plNetRevenue').text('GH₵' + d.net_revenue.toFixed(2));
                $('#plCogs').text('-GH₵' + d.cogs.toFixed(2));
                $('#plGrossProfit').text('GH₵' + d.gross_profit.toFixed(2));

                // Expense breakdown (div rows)
                let ebHtml = '';
                if(d.expense_breakdown.length === 0){
                    ebHtml = '<div style="display:flex;justify-content:center;padding:20px 0;color:var(--text-muted);font-style:italic;font-size:0.85rem;">No expenses recorded.</div>';
                } else {
                    d.expense_breakdown.forEach(e => {
                        ebHtml += `<div style="display:flex;justify-content:space-between;align-items:center;padding:12px 0;border-bottom:1px solid var(--border-light, rgba(255,255,255,0.05));">
                            <span style="padding-left:16px;color:var(--text-secondary);">${e.category_name || 'Uncategorized'}</span>
                            <span class="fw-600">GH₵${parseFloat(e.total).toFixed(2)}</span>
                        </div>`;
                    });
                }
                $('#plExpenseBreakdown').html(ebHtml);
                $('#plTotalExpenses').text('GH₵' + d.expenses.toFixed(2));

                // Bottom bar
                if(d.net_profit >= 0){
                    $('#plNetProfitBar').css('background', 'linear-gradient(135deg, rgba(16,185,129,0.12), rgba(16,185,129,0.04))');
                    $('#plNetProfitBarLabel').css('color', 'var(--success)').text('📊 NET PROFIT');
                    $('#plNetProfitBarValue').css('color', 'var(--success)').text('GH₵' + d.net_profit.toFixed(2));
                } else {
                    $('#plNetProfitBar').css('background', 'linear-gradient(135deg, rgba(239,68,68,0.12), rgba(239,68,68,0.04))');
                    $('#plNetProfitBarLabel').css('color', '#ef4444').text('📊 NET LOSS');
                    $('#plNetProfitBarValue').css('color', '#ef4444').text('-GH₵' + Math.abs(d.net_profit).toFixed(2));
                }
            }
        }, 'json');
    }

    // =============================
    // 5. CASHIER REPORT
    // =============================
    function loadCashierReport(start, end) {
        $.post('controllers/AnalyticsController.php', { action: 'get_cashier_report', start_date: start, end_date: end }, function(r){
            if(r.status === 'success'){
                let html = '';
                if(r.data.length === 0){
                    html = '<tr><td colspan="7" style="text-align:center; padding:30px;color:var(--text-muted);">No cashier activity for this period.</td></tr>';
                } else {
                    r.data.forEach((c, index) => {
                        html += `<tr>
                            <td>${index + 1}</td>
                            <td><div class="d-flex align-center gap-8">
                                <div class="sidebar-user-avatar" style="width:32px;height:32px;font-size:0.7rem;">${c.cashier_name.split(' ').map(n=>n[0]).join('').toUpperCase()}</div>
                                <span class="fw-600">${c.cashier_name}</span>
                            </div></td>
                            <td>${c.transactions}</td>
                            <td class="fw-600">GH₵${parseFloat(c.cash_sales).toFixed(2)}</td>
                            <td class="fw-600">GH₵${parseFloat(c.other_sales).toFixed(2)}</td>
                            <td>GH₵${parseFloat(c.avg_sale).toFixed(2)}</td>
                            <td class="fw-700 text-pink">GH₵${parseFloat(c.total_sales).toFixed(2)}</td>
                        </tr>`;
                    });
                }
                $('#cashierReportTable').html(html);
                if (initializedTabs['tab-cashier']) {
                    initDataTable('#tab-cashier .data-table');
                }
            }
        }, 'json');
    }

    // =============================
    // FILTER BUTTON
    // =============================
    $('#filterBtn').on('click', function(){
        loadAllReports($('#startDate').val(), $('#endDate').val());
    });

    // Initial Load — no date filter, show everything
    loadAllReports();
});
</script>
