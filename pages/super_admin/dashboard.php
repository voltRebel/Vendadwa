<?php
/**
 * Super Admin Dashboard
 * Visually rich layout inspired by the requested reference.
 */
?>

<!-- Top Bar -->
<div class="top-bar">
    <div class="top-bar-left">
        <div class="top-bar-greeting">Welcome back, <span>Super Admin</span> 👋</div>
        <h1>Platform Overview</h1>
    </div>
    <div class="top-bar-right">
        <div class="search-bar" style="margin-bottom:0; max-width:260px;">
            <i class="fa-solid fa-magnifying-glass search-icon"></i>
            <input type="text" placeholder="Search companies...">
        </div>
        <button class="btn-icon has-badge"><i class="fa-solid fa-bell"></i></button>
        <button class="btn-icon"><i class="fa-solid fa-user-gear"></i></button>
    </div>
</div>

<!-- Stats Grid -->
<div class="stats-grid">
    <div class="stat-card pink">
        <div class="stat-icon pink"><i class="fa-solid fa-building"></i></div>
        <div class="stat-info">
            <h3>24</h3>
            <p>Total Companies</p>
            <span class="stat-change up">+3 new this month</span>
        </div>
    </div>
    <div class="stat-card purple">
        <div class="stat-icon purple"><i class="fa-solid fa-users"></i></div>
        <div class="stat-info">
            <h3>1.2k</h3>
            <p>Active Users</p>
            <span class="stat-change up">+12% vs last week</span>
        </div>
    </div>
    <div class="stat-card gold">
        <div class="stat-icon gold"><i class="fa-solid fa-circle-dollar-to-slot"></i></div>
        <div class="stat-info">
            <h3>$12.5k</h3>
            <p>Monthly Revenue</p>
            <span class="stat-change up">+8% growth</span>
        </div>
    </div>
    <div class="stat-card green">
        <div class="stat-icon green"><i class="fa-solid fa-server"></i></div>
        <div class="stat-info">
            <h3>99.9%</h3>
            <p>System Uptime</p>
            <span class="stat-change">Healthy</span>
        </div>
    </div>
</div>

<div class="content-grid">
    <!-- Left Column: System Health & Performance -->
    <div class="glass-card">
        <div class="d-flex align-center justify-between mb-24">
            <h3 style="font-size:1.1rem;">Platform Engagement</h3>
            <a href="#" class="text-teal" style="font-size:0.85rem;">View details</a>
        </div>
        
        <div style="margin-bottom:24px;">
            <div class="d-flex justify-between mb-8" style="font-size:0.85rem;">
                <span class="fw-600">Active Companies</span>
                <span class="text-muted">85% Capacity</span>
            </div>
            <div style="height:8px; background:rgba(26,138,124,0.1); border-radius:4px; overflow:hidden;">
                <div style="width:85%; height:100%; background:linear-gradient(90deg, var(--primary-300), var(--primary-500));"></div>
            </div>
        </div>

        <div style="margin-bottom:24px;">
            <div class="d-flex justify-between mb-8" style="font-size:0.85rem;">
                <span class="fw-600">Storage Usage</span>
                <span class="text-muted">42% Used</span>
            </div>
            <div style="height:8px; background:rgba(26,138,124,0.1); border-radius:4px; overflow:hidden;">
                <div style="width:42%; height:100%; background:linear-gradient(90deg, var(--secondary-300), var(--secondary-500));"></div>
            </div>
        </div>

        <div class="chart-placeholder">
            <i class="fa-solid fa-chart-line" style="font-size:2rem; margin-bottom:8px; opacity:0.3;"></i>
            <span>System Performance Chart</span>
        </div>
    </div>

    <!-- Right Column: Recent Enrollments -->
    <div class="glass-card">
        <div class="d-flex align-center justify-between mb-24">
            <h3 style="font-size:1.1rem;">Recent Companies</h3>
            <button class="btn btn-sm btn-primary">Add New</button>
        </div>
        
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Company</th>
                        <th>Admin</th>
                        <th>Plan</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>1</td>
                        <td class="fw-600">Ntɛm</td>
                        <td>Andrea Pirlo</td>
                        <td><span class="badge badge-teal">Premium</span></td>
                        <td><span class="badge badge-success">Active</span></td>
                    </tr>
                    <tr>
                        <td>2</td>
                        <td class="fw-600">Luxe Boutique</td>
                        <td>Sarah J.</td>
                        <td><span class="badge badge-teal">Basic</span></td>
                        <td><span class="badge badge-success">Active</span></td>
                    </tr>
                    <tr>
                        <td>3</td>
                        <td class="fw-600">TrendSetters</td>
                        <td>Marc G.</td>
                        <td><span class="badge badge-blue">Trial</span></td>
                        <td><span class="badge badge-warning">Expiring</span></td>
                    </tr>
                    <tr>
                        <td>4</td>
                        <td class="fw-600">Urban Styles</td>
                        <td>John Doe</td>
                        <td><span class="badge badge-teal">Premium</span></td>
                        <td><span class="badge badge-success">Active</span></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="content-grid three">
    <!-- Action Card 1 -->
    <div class="glass-card" style="padding:20px; text-align:center;">
        <div class="stat-icon pink" style="margin:0 auto 12px; width:40px; height:40px;"><i class="fa-solid fa-plus"></i></div>
        <h4 class="mb-8">New Company</h4>
        <p class="text-muted mb-16" style="font-size:0.75rem;">Onboard a new client system.</p>
        <button class="btn btn-sm btn-outline btn-block">Launch Setup</button>
    </div>
    
    <!-- Action Card 2 -->
    <div class="glass-card" style="padding:20px; text-align:center;">
        <div class="stat-icon purple" style="margin:0 auto 12px; width:40px; height:40px;"><i class="fa-solid fa-shield-halved"></i></div>
        <h4 class="mb-8">Audit Logs</h4>
        <p class="text-muted mb-16" style="font-size:0.75rem;">Review system-wide activities.</p>
        <button class="btn btn-sm btn-outline btn-block">View Logs</button>
    </div>

    <!-- Promotional Card (Special Format) -->
    <div class="glass-card" style="background: linear-gradient(135deg, var(--primary-500), var(--secondary-500)); color: white; border: none;">
        <h4 class="mb-8">Platform Update v1.2</h4>
        <p style="font-size:0.8rem; margin-bottom:16px; opacity:0.9;">New POS analytics features are now available for all premium tenants.</p>
        <button class="btn btn-sm" style="background:rgba(255,255,255,0.2); color:white; border:1px solid rgba(255,255,255,0.4);">
            What's New?
        </button>
    </div>
</div>
