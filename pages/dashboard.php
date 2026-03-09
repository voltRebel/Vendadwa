<!-- Top Bar -->
<div class="top-bar">
    <div class="top-bar-left">
        <?php
        $hour = date('H');
        $greeting = 'Good Morning';
        if ($hour >= 12 && $hour < 17) $greeting = 'Good Afternoon';
        elseif ($hour >= 17) $greeting = 'Good Evening';
        ?>
        <div class="top-bar-greeting"><?= $greeting ?>, <span><?= htmlspecialchars($_SESSION['user_name'] ?? 'Admin') ?></span> 🌸</div>
        <h1>Dashboard Overview</h1>
    </div>
    <div class="top-bar-right">
        <button class="mobile-menu-toggle" id="mobileMenuToggle">
            <i class="fa-solid fa-bars"></i>
        </button>
        <div class="search-bar" style="max-width:240px;">
            <i class="fa-solid fa-magnifying-glass search-icon"></i>
            <input type="text" placeholder="Search...">
        </div>
        <button class="btn-icon has-badge" title="Notifications">
            <i class="fa-solid fa-bell"></i>
        </button>
        <button class="btn-icon" title="Settings">
            <i class="fa-solid fa-gear"></i>
        </button>
    </div>
</div>

<!-- Stats Grid -->
<div class="stats-grid">
    <div class="stat-card pink">
        <div class="stat-icon pink">
            <i class="fa-solid fa-dollar-sign"></i>
        </div>
        <div class="stat-info">
            <h3 id="statTodaySales">GH₵0.00</h3>
            <p>Today's Sales</p>
            <span class="stat-change up">Live Updates</span>
        </div>
    </div>

    <div class="stat-card purple">
        <div class="stat-icon purple">
            <i class="fa-solid fa-cart-shopping"></i>
        </div>
        <div class="stat-info">
            <h3 id="statTodayTransactions">0</h3>
            <p>Today's Transactions</p>
            <span class="stat-change up">Real-time</span>
        </div>
    </div>

    <div class="stat-card gold">
        <div class="stat-icon gold">
            <i class="fa-solid fa-wallet"></i>
        </div>
        <div class="stat-info">
            <h3 id="statLifetimeRevenue">GH₵0.00</h3>
            <p>Cash Balance</p>
            <span class="stat-change up">Total Revenue</span>
        </div>
    </div>

    <div class="stat-card green">
        <div class="stat-icon green">
            <i class="fa-solid fa-triangle-exclamation"></i>
        </div>
        <div class="stat-info">
            <h3 id="statLowStockCount">0</h3>
            <p>Low Stock Alerts</p>
            <span class="stat-change" id="statLowStockMsg">Checking Stock...</span>
        </div>
    </div>
</div>

<!-- Main Content Grid -->
<div class="content-grid">
    <!-- Recent Sales Table -->
    <div class="glass-card wide">
        <div class="d-flex align-center justify-between mb-16">
            <h3>Recent Sales</h3>
            <a href="?page=reports" class="btn btn-sm btn-outline">View All</a>
        </div>
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Receipt #</th>
                        <th>Customer</th>
                        <th>Total</th>
                        <th>Payment</th>
                        <th>Status</th>
                        <th>Time</th>
                    </tr>
                </thead>
                <tbody id="recentSalesTable">
                    <tr><td colspan="7" style="text-align:center; padding:20px;">Loading real-time sales...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="content-grid">
    <!-- Sales Chart placeholder -->
    <div class="glass-card">
        <div class="d-flex align-center justify-between mb-16">
            <h3>Weekly Sales</h3>
            <select class="form-control" style="width:auto; padding:6px 32px 6px 12px; font-size:0.78rem;">
                <option>This Week</option>
            </select>
        </div>
        <div class="chart-container" style="height: 250px; position: relative;">
            <canvas id="weeklySalesChart"></canvas>
        </div>
    </div>

    <!-- Low Stock Alerts -->
    <div class="glass-card">
        <div class="d-flex align-center justify-between mb-16">
            <h3>Low Stock Alerts</h3>
            <a href="?page=products" class="btn btn-sm btn-outline">View All</a>
        </div>
        <ul class="low-stock-list" id="lowStockList">
            <li class="loading" style="text-align:center; padding:20px; color:var(--text-muted);">Analyzing inventory...</li>
        </ul>
    </div>
</div>

<script>
$(document).ready(function() {

    // Helper to initialize DataTables
    function initDataTable(selector, options = {}) {
        if ($.fn.DataTable.isDataTable(selector)) {
            $(selector).DataTable().clear().destroy();
        }
        $(selector).DataTable($.extend({
            pageLength: 10, // Show 10 on dashboard
            ordering: false,
            responsive: true,
            lengthChange: false, // Hide the "show X entries" dropdown on dashboard
            language: { search: "", searchPlaceholder: "Search..." }
        }, options));
    }

    function loadDashboardData() {
        $.post('controllers/AnalyticsController.php', { action: 'get_dashboard_stats' }, function(response) {
            if (response.status === 'success') {
                const data = response.data;
                
                // Update Stats
                $('#statTodaySales').text('GH₵' + data.today_sales.toFixed(2));
                $('#statTodayTransactions').text(data.today_transactions);
                $('#statLifetimeRevenue').text('GH₵' + data.lifetime_revenue.toLocaleString('en-US', {minimumFractionDigits: 2}));
                $('#statLowStockCount').text(data.low_stock_count);
                
                if (data.low_stock_count > 0) {
                    $('#statLowStockMsg').addClass('text-danger').text('↑ Needs attention');
                } else {
                    $('#statLowStockMsg').removeClass('text-danger').addClass('text-success').text('Stock healthy');
                }

                // Update Recent Sales Table
                let salesHtml = '';
                if (data.recent_sales.length === 0) {
                    salesHtml = '<tr><td colspan="7" style="text-align:center; padding:20px;">No sales today.</td></tr>';
                } else {
                    data.recent_sales.forEach((s, index) => {
                        const time = new Date(s.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
                        const typeBadge = s.type === 'manual' ? '<span class="badge badge-outline">Manual</span>' : '';
                        salesHtml += `
                            <tr>
                                <td>${index + 1}</td>
                                <td>${s.receipt_no} ${typeBadge}</td>
                                <td>${s.customer_name || 'Walk-in Customer'}</td>
                                <td class="fw-600 text-pink">GH₵${parseFloat(s.total).toFixed(2)}</td>
                                <td><span class="badge ${s.payment_method === 'Cash' ? 'badge-purple' : 'badge-pink'}">${s.payment_method}</span></td>
                                <td><span class="badge badge-success">Completed</span></td>
                                <td class="text-muted">${time}</td>
                            </tr>
                        `;
                    });
                }
                $('#recentSalesTable').html(salesHtml);
                initDataTable('.data-table');

                // Update Low Stock List
                let stockHtml = '';
                if (data.low_stock_items.length === 0) {
                    stockHtml = '<li style="text-align:center; padding:20px; color:var(--text-muted);">All items in stock!</li>';
                } else {
                    data.low_stock_items.forEach(p => {
                        stockHtml += `
                            <li class="low-stock-item">
                                <div>
                                    <div class="item-name">${p.name}</div>
                                    <div class="text-muted" style="font-size:0.72rem;">SKU: ${p.sku || 'N/A'}</div>
                                </div>
                                <span class="item-qty critical">${p.stock_quantity} left</span>
                            </li>
                        `;
                    });
                }
                $('#lowStockList').html(stockHtml);

                // Update Recent Activity
                let activityHtml = '';
                if (data.recent_activity.length === 0) {
                    activityHtml = '<li style="text-align:center; padding:20px; color:var(--text-muted);">No recent activity.</li>';
                } else {
                    data.recent_activity.forEach(log => {
                        const colors = {
                            'login': 'teal',
                            'sale_created': 'teal',
                            'backup_created': 'purple',
                            'profile_update': 'blue',
                            'stock_update': 'orange',
                            'deleted': 'pink'
                        };
                        const dotColor = colors[log.action_type] || 'grey';
                        const time = new Date(log.created_at);
                        const diff = Math.floor((new Date() - time) / 60000);
                        let timeStr = diff < 1 ? 'Just now' : (diff < 60 ? diff + 'm ago' : Math.floor(diff/60) + 'h ago');
                        
                        activityHtml += `
                            <li class="activity-item">
                                <span class="activity-dot ${dotColor}"></span>
                                <div>
                                    <div class="activity-text"><strong>${log.user_name}</strong> ${log.details}</div>
                                    <div class="activity-time">${timeStr}</div>
                                </div>
                            </li>
                        `;
                    });
                }
                $('#activityList').html(activityHtml);

                // Update Weekly Sales Chart
                renderSalesChart(data.weekly_sales);

            } else {
                console.error('Dashboard error:', response.message);
            }
        }, 'json');
    }

    let salesChart = null;
    function renderSalesChart(weeklyData) {
        const ctx = document.getElementById('weeklySalesChart').getContext('2d');
        const labels = weeklyData.map(d => {
            const date = new Date(d.date);
            return date.toLocaleDateString('en-US', { weekday: 'short' });
        });
        const values = weeklyData.map(d => d.total);

        if (salesChart) salesChart.destroy();

        salesChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Sales (GH₵)',
                    data: values,
                    borderColor: '#1a8a7c',
                    backgroundColor: 'rgba(26, 138, 124, 0.1)',
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#1a8a7c',
                    pointBorderColor: '#fff',
                    pointHoverRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        callbacks: {
                            label: function(context) {
                                return 'GH₵ ' + context.parsed.y.toFixed(2);
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { borderDash: [5, 5], color: '#f0f0f0' },
                        ticks: { font: { size: 10 } }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { font: { size: 10 } }
                    }
                }
            }
        });
    }

    // Initial load
    loadDashboardData();
    
    // Refresh every 30 seconds for live feeling
    setInterval(loadDashboardData, 30000);
});
</script>

<div class="content-grid">
    <!-- Recent Activity -->
    <div class="glass-card">
        <h3 class="mb-16">Recent Activity</h3>
        <ul class="activity-list" id="activityList">
            <li class="loading" style="text-align:center; padding:20px; color:var(--text-muted);">Fetching logs...</li>
        </ul>
    </div>

    <!-- Quick Actions -->
    <div class="glass-card">
        <h3 class="mb-16">Quick Actions</h3>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
            <a href="?page=pos" class="btn btn-primary" style="padding:16px; flex-direction:column; gap:6px;">
                <i class="fa-solid fa-cash-register" style="font-size:1.3rem;"></i>
                New Sale
            </a>
            <a href="?page=products" class="btn btn-secondary" style="padding:16px; flex-direction:column; gap:6px;">
                <i class="fa-solid fa-plus" style="font-size:1.3rem;"></i>
                Add Product
            </a>
            <a href="?page=customers" class="btn btn-secondary" style="padding:16px; flex-direction:column; gap:6px;">
                <i class="fa-solid fa-user-plus" style="font-size:1.3rem;"></i>
                Add Customer
            </a>
            <a href="?page=reports" class="btn btn-outline" style="padding:16px; flex-direction:column; gap:6px;">
                <i class="fa-solid fa-chart-bar" style="font-size:1.3rem;"></i>
                View Reports
            </a>
        </div>

        <div class="divider"></div>

        <!-- Don't Forget Widget -->
        <div style="background: linear-gradient(135deg, var(--primary-50), var(--secondary-50)); border-radius: var(--radius-md); padding: 20px; text-align: center;">
            <div style="font-size: 0.72rem; font-weight: 600; text-transform: uppercase; letter-spacing: 1px; color: var(--primary-400); margin-bottom: 8px;">Don't Forget</div>
            <h4 style="font-size: 1rem; margin-bottom: 4px;">End of Day Report</h4>
            <p style="font-size: 0.8rem; color: var(--text-muted); margin-bottom: 12px;">Generate your Z-Report before closing</p>
            <a href="?page=reports" class="btn btn-sm btn-primary">Generate Report</a>
        </div>
    </div>
</div>
