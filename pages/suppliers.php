<?php
require_once 'includes/queries.php';
$suppliers = getSuppliers();
$stats     = getSupplierStats();

$totalSuppliers = (int)$stats['total_suppliers'];
$totalPOValue   = (float)$stats['total_po_value'];
$pendingPOs     = (int)$stats['pending_pos'];
$totalPaid      = (float)$stats['total_paid'];
?>

<!-- Top Bar -->
<div class="top-bar">
    <div class="top-bar-left">
        <button class="mobile-menu-toggle" id="mobileMenuToggle"><i class="fa-solid fa-bars"></i></button>
        <h1>Suppliers</h1>
    </div>
    <div class="top-bar-right">
        <button class="btn btn-primary" id="addSupplierBtn">
            <i class="fa-solid fa-plus"></i> Add Supplier
        </button>
    </div>
</div>

<!-- ── Stats ── -->
<div class="stats-grid supp-stats-grid" style="margin-bottom:24px;">
    <div class="stat-card teal">
        <div class="stat-icon" style="background:rgba(26,138,124,.12);color:var(--primary-500)"><i class="fa-solid fa-truck"></i></div>
        <div class="stat-info">
            <h3 id="statTotalSuppliers"><?= $totalSuppliers ?></h3>
            <p>Total Suppliers</p>
            <span class="stat-change up"><i class="fa-solid fa-building"></i> Registered</span>
        </div>
    </div>
    <div class="stat-card purple">
        <div class="stat-icon purple"><i class="fa-solid fa-file-invoice-dollar"></i></div>
        <div class="stat-info">
            <h3>GH₵<?= number_format($totalPOValue, 0) ?></h3>
            <p>Total PO Value</p>
            <span class="stat-change up"><i class="fa-solid fa-arrow-trend-up"></i> All time</span>
        </div>
    </div>
    <div class="stat-card gold">
        <div class="stat-icon gold"><i class="fa-solid fa-clock"></i></div>
        <div class="stat-info">
            <h3 id="statPendingPOs"><?= $pendingPOs ?></h3>
            <p>Pending Orders</p>
            <span class="stat-change <?= $pendingPOs > 0 ? 'down' : 'up' ?>"><i class="fa-solid fa-hourglass-half"></i> Awaiting</span>
        </div>
    </div>
    <div class="stat-card green">
        <div class="stat-icon" style="background:var(--success-bg);color:var(--success)"><i class="fa-solid fa-circle-check"></i></div>
        <div class="stat-info">
            <h3>GH₵<?= number_format($totalPaid, 0) ?></h3>
            <p>Total Paid</p>
            <span class="stat-change up"><i class="fa-solid fa-money-bill-trend-up"></i> Paid out</span>
        </div>
    </div>
</div>

<!-- ── Main Card ── -->
<div class="glass-card-static">
    <div class="tab-nav">
        <button class="tab-btn active" data-tab="tab-supplist">Suppliers List</button>
        <button class="tab-btn" data-tab="tab-purchaseorders">Purchase Orders</button>
        <button class="tab-btn" data-tab="tab-supppayments">Payments</button>
    </div>

    <!-- ====== TAB 1: SUPPLIERS LIST ====== -->
    <div class="tab-content active" id="tab-supplist">
        <div class="d-flex align-center justify-between mb-16" style="flex-wrap:wrap;gap:10px;">
            <div class="search-bar">
                <i class="fa-solid fa-magnifying-glass search-icon"></i>
                <input type="text" id="supplierSearch" placeholder="Search suppliers...">
            </div>
            <div class="d-flex gap-8 align-center">
                <select class="form-control" id="statusFilter" style="width:auto;padding:8px 34px 8px 12px;font-size:0.82rem;">
                    <option value="">All Status</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
        </div>
        <div class="table-container">
            <table class="data-table" id="suppliersTable">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Supplier</th>
                        <th>Contact</th>
                        <th>Email</th>
                        <th>Purchase Orders</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                    $i = 1;
                    foreach ($suppliers as $s): ?>
                    <tr data-id="<?= $s['id'] ?>"
                        data-name="<?= htmlspecialchars(strtolower($s['name'])) ?>"
                        data-sname="<?= htmlspecialchars($s['name']) ?>"
                        data-category="<?= htmlspecialchars($s['category'] ?? '') ?>"
                        data-phone="<?= htmlspecialchars($s['phone'] ?? '') ?>"
                        data-email="<?= htmlspecialchars($s['email'] ?? '') ?>"
                        data-address="<?= htmlspecialchars($s['address'] ?? '') ?>"
                        data-notes="<?= htmlspecialchars($s['notes'] ?? '') ?>"
                        data-status="<?= $s['status'] ?>"
                        data-po-count="<?= (int)$s['po_count'] ?>"
                        data-po-total="<?= number_format((float)$s['po_total'], 2) ?>">
                        <td><?= $i++ ?></td>
                        <td>
                            <div class="d-flex align-center gap-10">
                                <div class="supp-avatar"><?= strtoupper(substr($s['name'], 0, 2)) ?></div>
                                <div>
                                    <div class="fw-600"><?= htmlspecialchars($s['name']) ?></div>
                                    <?php if ($s['category']): ?><div class="text-muted" style="font-size:0.72rem;"><?= htmlspecialchars($s['category']) ?></div><?php endif; ?>
                                </div>
                            </div>
                        </td>
                        <td><?= $s['phone'] ? htmlspecialchars($s['phone']) : '<span style="color:var(--text-muted)">—</span>' ?></td>
                        <td><?= $s['email'] ? htmlspecialchars($s['email']) : '<span style="color:var(--text-muted)">—</span>' ?></td>
                        <td>
                            <span class="fw-600"><?= (int)$s['po_count'] ?></span>
                            <span style="color:var(--text-muted);font-size:0.8rem;"> &nbsp;GH₵<?= number_format((float)$s['po_total'], 0) ?></span>
                        </td>
                        <td>
                            <span class="badge <?= $s['status'] === 'active' ? 'badge-success' : 'badge-secondary' ?>">
                                <?= ucfirst($s['status']) ?>
                            </span>
                        </td>
                        <td>
                            <div class="d-flex gap-6">
                                <button class="btn btn-sm btn-secondary view-supplier" title="View">
                                    <i class="fa-solid fa-eye" style="color:var(--info,#3b82f6);"></i>
                                </button>
                                <button class="btn btn-sm btn-secondary edit-supplier" title="Edit">
                                    <i class="fa-solid fa-pen-to-square" style="color:var(--primary-500);"></i>
                                </button>
                                <button class="btn btn-sm btn-secondary delete-supplier" data-id="<?= $s['id'] ?>" data-name="<?= htmlspecialchars($s['name']) ?>" title="Delete">
                                    <i class="fa-solid fa-trash" style="color:var(--danger);"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- ====== TAB 2: PURCHASE ORDERS ====== -->
    <div class="tab-content" id="tab-purchaseorders">
        <div class="d-flex align-center justify-between mb-16" style="flex-wrap:wrap;gap:10px;">
            <div class="d-flex gap-10 align-center" style="flex-wrap:wrap;">
                <div class="search-bar">
                    <i class="fa-solid fa-magnifying-glass search-icon"></i>
                    <input type="text" id="poSearch" placeholder="Search PO# or supplier...">
                </div>
                <select class="form-control" id="poSupplierFilter" style="width:auto;padding:8px 34px 8px 12px;font-size:0.82rem;">
                    <option value="0">All Suppliers</option>
                    <?php foreach ($suppliers as $s): ?>
                    <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <select class="form-control" id="poStatusFilter" style="width:auto;padding:8px 34px 8px 12px;font-size:0.82rem;">
                    <option value="">All Status</option>
                    <option value="pending">Pending</option>
                    <option value="received">Received</option>
                    <option value="cancelled">Cancelled</option>
                </select>
            </div>
            <button class="btn btn-primary btn-sm" id="addPOBtn">
                <i class="fa-solid fa-plus"></i> New Order
            </button>
        </div>
        <div class="table-container">
            <table class="data-table" id="poTable">
                <thead>
                    <tr><th>#</th><th>PO #</th><th>Supplier</th><th>Order Date</th><th>Expected</th><th>Items</th><th>Total</th><th>Status</th><th>Actions</th></tr>
                </thead>
                <tbody id="poTbody">
                    <tr><td colspan="9" style="text-align:center;padding:30px;color:var(--text-muted);"><i class="fa-solid fa-spinner fa-spin"></i> Loading...</td></tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- ====== TAB 3: PAYMENTS ====== -->
    <div class="tab-content" id="tab-supppayments">
        <div class="d-flex align-center justify-between mb-16" style="flex-wrap:wrap;gap:10px;">
            <div class="d-flex gap-10 align-center" style="flex-wrap:wrap;">
                <div class="search-bar">
                    <i class="fa-solid fa-magnifying-glass search-icon"></i>
                    <input type="text" id="paySearch" placeholder="Search supplier or PO#...">
                </div>
                <select class="form-control" id="paySupplierFilter" style="width:auto;padding:8px 34px 8px 12px;font-size:0.82rem;">
                    <option value="0">All Suppliers</option>
                    <?php foreach ($suppliers as $s): ?>
                    <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button class="btn btn-primary btn-sm" id="addPaymentBtn">
                <i class="fa-solid fa-money-bill"></i> Record Payment
            </button>
        </div>
        <div class="table-container">
            <table class="data-table" id="paymentsTable">
                <thead>
                    <tr><th>#</th><th>Date</th><th>Supplier</th><th>PO #</th><th>Amount</th><th>Method</th><th>Reference</th><th>Status</th><th>Actions</th></tr>
                </thead>
                <tbody id="payTbody">
                    <tr><td colspan="9" style="text-align:center;padding:30px;color:var(--text-muted);"><i class="fa-solid fa-spinner fa-spin"></i> Loading...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ==================== MODALS ==================== -->

<!-- View Supplier Modal -->
<div class="modal-overlay" id="viewSupplierModal">
    <div class="modal modal-lg">
        <div class="modal-header">
            <h3 id="viewSupplierTitle">🚚 Supplier Profile</h3>
            <button type="button" class="modal-close">&times;</button>
        </div>
        <div class="modal-body p-24">
            <div class="view-supp-layout">
                <div class="view-supp-icon-col">
                    <div class="view-supp-circle" id="viewSuppInitials">AB</div>
                    <div id="viewSuppStatusBadge"></div>
                    <div style="font-size:0.75rem;color:var(--text-muted);text-align:center;">Since<br><span id="viewSuppSince" class="fw-600 text-teal"></span></div>
                </div>
                <div class="view-product-info-col">
                    <div class="view-detail-grid">
                        <div class="view-detail-item">
                            <span class="vd-label"><i class="fa-solid fa-tag"></i> Category</span>
                            <span class="vd-value" id="vs-category">—</span>
                        </div>
                        <div class="view-detail-item">
                            <span class="vd-label"><i class="fa-solid fa-phone"></i> Phone</span>
                            <span class="vd-value" id="vs-phone">—</span>
                        </div>
                        <div class="view-detail-item">
                            <span class="vd-label"><i class="fa-solid fa-envelope"></i> Email</span>
                            <span class="vd-value" id="vs-email">—</span>
                        </div>
                        <div class="view-detail-item">
                            <span class="vd-label"><i class="fa-solid fa-file-invoice-dollar"></i> Purchase Orders</span>
                            <span class="vd-value fw-700 text-teal" id="vs-po-count">0</span>
                        </div>
                        <div class="view-detail-item" style="grid-column:1/-1;">
                            <span class="vd-label"><i class="fa-solid fa-location-dot"></i> Address</span>
                            <span class="vd-value" id="vs-address">—</span>
                        </div>
                        <div class="view-detail-item" style="grid-column:1/-1;">
                            <span class="vd-label"><i class="fa-solid fa-note-sticky"></i> Notes</span>
                            <span class="vd-value" id="vs-notes" style="white-space:pre-wrap;">—</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary modal-cancel">Close</button>
            <button type="button" class="btn btn-primary" id="viewToEditSuppBtn"><i class="fa-solid fa-pen-to-square"></i> Edit Supplier</button>
        </div>
    </div>
</div>

<!-- Add / Edit Supplier Modal -->
<div class="modal-overlay" id="supplierModal">
    <div class="modal">
        <form id="supplierForm">
            <input type="hidden" name="id" value="0">
            <input type="hidden" name="action" value="save_supplier">
            <div class="modal-header">
                <h3 id="supplierModalTitle">🚚 Add Supplier</h3>
                <button type="button" class="modal-close">&times;</button>
            </div>
            <div class="modal-body p-24">
                <div class="d-flex flex-column gap-16">
                    <div class="floating-group">
                        <input type="text" name="name" class="floating-control" required placeholder=" ">
                        <label class="floating-label">Company Name *</label>
                    </div>
                    <div class="floating-group">
                        <input type="text" name="category" class="floating-control" placeholder=" ">
                        <label class="floating-label">Category / Type</label>
                    </div>
                    <div class="d-grid gap-12" style="grid-template-columns:1fr 1fr;">
                        <div class="floating-group">
                            <input type="tel" name="phone" class="floating-control" placeholder=" ">
                            <label class="floating-label">Phone Number</label>
                        </div>
                        <div class="floating-group">
                            <input type="email" name="email" class="floating-control" placeholder=" ">
                            <label class="floating-label">Email Address</label>
                        </div>
                    </div>
                    <div class="floating-group">
                        <input type="text" name="address" class="floating-control" placeholder=" ">
                        <label class="floating-label">Address (Optional)</label>
                    </div>
                    <div class="floating-group">
                        <select name="status" class="floating-control">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                        <label class="floating-label">Status</label>
                    </div>
                    <div class="floating-group">
                        <textarea name="notes" class="floating-control" style="height:70px;" placeholder=" "></textarea>
                        <label class="floating-label">Notes (Optional)</label>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary modal-cancel">Cancel</button>
                <button type="submit" class="btn btn-primary" id="saveSupplierBtn">
                    <i class="fa-solid fa-check"></i> Save Supplier
                </button>
            </div>
        </form>
    </div>
</div>

<!-- New / Edit Purchase Order Modal -->
<div class="modal-overlay" id="poModal">
    <div class="modal">
        <form id="poForm">
            <input type="hidden" name="id" value="0">
            <input type="hidden" name="action" value="save_po">
            <div class="modal-header">
                <h3 id="poModalTitle">📋 New Purchase Order</h3>
                <button type="button" class="modal-close">&times;</button>
            </div>
            <div class="modal-body p-24">
                <div class="d-flex flex-column gap-16">
                    <div class="floating-group">
                        <select name="supplier_id" id="poSupplierSelect" class="floating-control" required>
                            <option value="">Select Supplier</option>
                            <?php foreach ($suppliers as $s): ?>
                            <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <label class="floating-label">Supplier *</label>
                    </div>
                    <div class="d-grid gap-12" style="grid-template-columns:1fr 1fr;">
                        <div class="floating-group">
                            <input type="date" name="order_date" class="floating-control has-value" value="<?= date('Y-m-d') ?>">
                            <label class="floating-label">Order Date</label>
                        </div>
                        <div class="floating-group">
                            <input type="date" name="expected_date" class="floating-control">
                            <label class="floating-label">Expected Date</label>
                        </div>
                    </div>
                    <div class="d-grid gap-12" style="grid-template-columns:1fr 1fr;">
                        <div class="floating-group">
                            <input type="number" name="items" class="floating-control" min="0" value="0" placeholder=" ">
                            <label class="floating-label">No. of Items</label>
                        </div>
                        <div class="floating-group">
                            <input type="number" step="0.01" name="total" class="floating-control" required placeholder=" ">
                            <label class="floating-label">Total Amount (GH₵) *</label>
                        </div>
                    </div>
                    <div class="floating-group" id="poStatusGroup" style="display:none;">
                        <select name="status" class="floating-control">
                            <option value="pending">Pending</option>
                            <option value="received">Received</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                        <label class="floating-label">Status</label>
                    </div>
                    <div class="floating-group">
                        <textarea name="notes" class="floating-control" style="height:70px;" placeholder=" "></textarea>
                        <label class="floating-label">Notes (Optional)</label>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary modal-cancel">Cancel</button>
                <button type="submit" class="btn btn-primary" id="savePOBtn">
                    <i class="fa-solid fa-check"></i> Create Order
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Record Payment Modal -->
<div class="modal-overlay" id="paymentModal">
    <div class="modal">
        <form id="paymentForm">
            <input type="hidden" name="action" value="save_payment">
            <div class="modal-header">
                <h3>💳 Record Supplier Payment</h3>
                <button type="button" class="modal-close">&times;</button>
            </div>
            <div class="modal-body p-24">
                <div class="d-flex flex-column gap-16">
                    <div class="floating-group">
                        <select name="supplier_id" id="paySupplierSelect" class="floating-control" required>
                            <option value="">Select Supplier</option>
                            <?php foreach ($suppliers as $s): ?>
                            <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <label class="floating-label">Supplier *</label>
                    </div>
                    <div class="floating-group">
                        <select name="purchase_order_id" id="payPOSelect" class="floating-control">
                            <option value="0">— No linked PO —</option>
                        </select>
                        <label class="floating-label">Link to Purchase Order (Optional)</label>
                    </div>
                    <div class="d-grid gap-12" style="grid-template-columns:1fr 1fr;">
                        <div class="floating-group">
                            <input type="number" step="0.01" name="amount" class="floating-control" required placeholder=" ">
                            <label class="floating-label">Amount (GH₵) *</label>
                        </div>
                        <div class="floating-group">
                            <input type="date" name="payment_date" class="floating-control has-value" value="<?= date('Y-m-d') ?>">
                            <label class="floating-label">Payment Date</label>
                        </div>
                    </div>
                    <div class="d-grid gap-12" style="grid-template-columns:1fr 1fr;">
                        <div class="floating-group">
                            <select name="method" class="floating-control">
                                <option>Cash</option>
                                <option>Bank Transfer</option>
                                <option>Check</option>
                                <option>Mobile Money</option>
                                <option>Other</option>
                            </select>
                            <label class="floating-label">Payment Method</label>
                        </div>
                        <div class="floating-group">
                            <select name="pm_status" class="floating-control">
                                <option value="paid">Paid</option>
                                <option value="pending">Pending</option>
                            </select>
                            <label class="floating-label">Status</label>
                        </div>
                    </div>
                    <div class="floating-group">
                        <input type="text" name="reference" class="floating-control" placeholder=" ">
                        <label class="floating-label">Reference / Cheque No. (Optional)</label>
                    </div>
                    <div class="floating-group">
                        <textarea name="notes" class="floating-control" style="height:60px;" placeholder=" "></textarea>
                        <label class="floating-label">Notes (Optional)</label>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary modal-cancel">Cancel</button>
                <button type="submit" class="btn btn-primary" id="savePaymentBtn">
                    <i class="fa-solid fa-circle-check"></i> Record Payment
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ==================== STYLES ==================== -->
<style>
/* ── Supplier Avatar ── */
.supp-avatar {
    width:40px;height:40px;border-radius:10px;
    background:linear-gradient(135deg,var(--primary-400),var(--primary-600));
    color:#fff;font-weight:700;font-size:0.82rem;
    display:flex;align-items:center;justify-content:center;
    flex-shrink:0;font-family:'Outfit',sans-serif;letter-spacing:.5px;
    box-shadow:0 3px 10px rgba(26,138,124,.25);
}

/* ── Stats grid ── */
.supp-stats-grid { grid-template-columns:repeat(4,1fr); }
@media(max-width:1100px){ .supp-stats-grid{ grid-template-columns:repeat(2,1fr); } }
@media(max-width:600px) { .supp-stats-grid{ grid-template-columns:1fr; } }

/* ── View Supplier modal layout ── */
.view-supp-layout { display:grid; grid-template-columns:160px 1fr; gap:28px; align-items:start; }
@media(max-width:640px){ .view-supp-layout{ grid-template-columns:1fr; } }
.view-supp-icon-col { display:flex; flex-direction:column; align-items:center; gap:14px; }
.view-supp-circle {
    width:130px;height:130px;border-radius:20px;
    background:linear-gradient(135deg,var(--primary-400),var(--primary-600));
    box-shadow:0 6px 24px rgba(26,138,124,.22);
    display:flex;align-items:center;justify-content:center;
    font-size:2.6rem;color:#fff;
    font-weight:800;font-family:'Outfit',sans-serif;
    flex-shrink:0;
}

/* ── View detail grid (shared with product/customer) ── */
.view-detail-grid { display:grid; grid-template-columns:1fr 1fr; gap:14px; }
.view-detail-item { display:flex; flex-direction:column; gap:4px; padding:12px 14px; background:rgba(26,138,124,0.03); border-radius:10px; border:1px solid rgba(26,138,124,0.07); }
.vd-label { font-size:0.72rem; font-weight:600; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.5px; display:flex; align-items:center; gap:5px; }
.vd-value { font-size:0.9rem; font-weight:500; color:var(--text-primary); }

/* ── Payment method badges ── */
.pm-badge { display:inline-flex;align-items:center;gap:4px;padding:3px 9px;border-radius:20px;font-size:0.76rem;font-weight:600; }
.pm-cash     { background:rgba(16,185,129,.1);color:#059669; }
.pm-bank     { background:rgba(59,130,246,.1);color:#2563eb; }
.pm-check    { background:rgba(139,92,246,.1);color:#6d28d9; }
.pm-momo     { background:rgba(245,158,11,.1);color:#b45309; }
.pm-other    { background:rgba(107,114,128,.1);color:#374151; }

/* ── PO status badges ── */
.badge-received   { background:rgba(16,185,129,.1);color:#059669; }
.badge-cancelled  { background:rgba(239,68,68,.1);color:#b91c1c; }
.badge-pending    { background:rgba(245,158,11,.12);color:#b45309; }
.badge-secondary  { background:rgba(107,114,128,.1);color:#374151; }

/* utils */
.text-teal { color:var(--primary-500)!important; }
.mb-16{ margin-bottom:16px; } .d-grid{ display:grid; } .gap-12{ gap:12px; }
.fw-600{ font-weight:600; } .fw-700{ font-weight:700; }
.p-24 { padding:24px; }
.flex-column{ flex-direction:column; }
.gap-16{ gap:16px; }
</style>

<!-- ==================== SCRIPTS ==================== -->
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

    initDataTable('#suppliersTable');

/* ── Floating select helper ── */
function checkSel(s){ s.val() ? s.addClass('has-value') : s.removeClass('has-value'); }
$('select.floating-control').each(function(){ checkSel($(this)); }).on('change',function(){ checkSel($(this)); });

/* ── Toast ── */
function toast(msg, type='success'){
    let t=$('<div>').addClass('toast toast-'+type).text(msg).appendTo('body');
    setTimeout(()=>t.addClass('show'),10);
    setTimeout(()=>{ t.removeClass('show'); setTimeout(()=>t.remove(),400); },3000);
}
if(!$('#toastStyle').length){
    $('<style id="toastStyle">').text(`
        .toast{position:fixed;bottom:24px;right:24px;padding:12px 20px;border-radius:12px;
            font-size:.88rem;font-weight:600;color:#fff;z-index:9999;
            transform:translateY(20px);opacity:0;transition:all .3s ease;pointer-events:none;max-width:340px;}
        .toast.show{transform:translateY(0);opacity:1;}
        .toast-success{background:linear-gradient(135deg,#10b981,#059669);}
        .toast-error  {background:linear-gradient(135deg,#ef4444,#b91c1c);}
        .toast-info   {background:linear-gradient(135deg,#3b82f6,#1d4ed8);}
    `).appendTo('head');
}

/* ── Tabs (with localStorage) ── */
const TAB_KEY = 'vendora_suppliers_tab';
function activateTab(id){
    $('.tab-btn').removeClass('active'); $('.tab-content').removeClass('active');
    $(`.tab-btn[data-tab="${id}"]`).addClass('active'); $(`#${id}`).addClass('active');
    localStorage.setItem(TAB_KEY, id);
    if(id === 'tab-purchaseorders') loadPOs();
    if(id === 'tab-supppayments')   loadPayments();
}
const savedTab = localStorage.getItem(TAB_KEY);
if(savedTab && $(`#${savedTab}`).length) activateTab(savedTab);
$('.tab-btn').on('click', function(){ activateTab($(this).data('tab')); });

/* ── Modals ── */
function openModal(id)  { $('#'+id).addClass('active'); }
function closeModal(id) { $('#'+id).removeClass('active'); }
$(document).on('click','.modal-close,.modal-cancel',function(){
    $(this).closest('.modal-overlay').removeClass('active');
});

/* ─────────────────────────────────────────────
   TAB 1 — SUPPLIERS
───────────────────────────────────────────── */

/* ── Search & filter (DataTables override) ── */
function filterSuppliers(){
    const q  = $('#supplierSearch').val();
    const st = $('#statusFilter').val();
    
    const table = $('#suppliersTable').DataTable();
    table.search(q).draw();

    $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
        if (settings.nTable.id !== 'suppliersTable') return true;
        if (!st) return true;
        const statusCol = data[4] || ''; // Status is in the 5th column
        return statusCol.toLowerCase().includes(st);
    });
    table.draw();
    $.fn.dataTable.ext.search.pop();
}
$('#supplierSearch').on('keyup', function() {
    $('#suppliersTable').DataTable().search($(this).val()).draw();
});
$('#statusFilter').on('change', filterSuppliers);

/* ── ADD SUPPLIER ── */
$('#addSupplierBtn,#addSuppEmptyLink').on('click',function(e){
    e.preventDefault();
    $('#supplierForm')[0].reset();
    $('#supplierForm input[name="id"]').val('0');
    $('#supplierModalTitle').text('🚚 Add Supplier');
    $('#saveSupplierBtn').html('<i class="fa-solid fa-check"></i> Save Supplier');
    $('select.floating-control').each(function(){ checkSel($(this)); });
    openModal('supplierModal');
});

/* ── VIEW SUPPLIER ── */
var _viewSuppId = 0;
$(document).on('click','.view-supplier',function(){
    const row = $(this).closest('tr');
    _viewSuppId = row.data('id');
    const name    = row.data('sname');
    const init    = name.substring(0,2).toUpperCase();
    const status  = row.data('status');
    const since   = row.find('td:last').data ? '' : '';

    $('#viewSupplierTitle').text('🚚 '+name);
    $('#viewSuppInitials').text(init);
    $('#viewSuppStatusBadge').html(`<span class="badge ${status==='active'?'badge-success':'badge-secondary'}">${status.charAt(0).toUpperCase()+status.slice(1)}</span>`);
    $('#viewSuppSince').text('—');
    $('#vs-category').text(row.data('category') || '—');
    $('#vs-phone').text(row.data('phone') || '—');
    $('#vs-email').text(row.data('email') || '—');
    $('#vs-po-count').text(row.data('po-count')+' orders (GH₵'+row.data('po-total')+')');
    $('#vs-address').text(row.data('address') || '—');
    $('#vs-notes').text(row.data('notes') || 'None');
    openModal('viewSupplierModal');
});
$('#viewToEditSuppBtn').on('click',function(){
    closeModal('viewSupplierModal');
    $(`#suppliersTable tr[data-id="${_viewSuppId}"]`).find('.edit-supplier').trigger('click');
});

/* ── EDIT SUPPLIER ── */
$(document).on('click','.edit-supplier',function(){
    const row = $(this).closest('tr');
    const f   = $('#supplierForm');
    f.find('[name="id"]').val(row.data('id'));
    f.find('[name="name"]').val(row.data('sname'));
    f.find('[name="category"]').val(row.data('category'));
    f.find('[name="phone"]').val(row.data('phone'));
    f.find('[name="email"]').val(row.data('email'));
    f.find('[name="address"]').val(row.data('address'));
    f.find('[name="notes"]').val(row.data('notes'));
    f.find('[name="status"]').val(row.data('status'));
    f.find('.floating-control').trigger('input');
    $('select.floating-control').each(function(){ checkSel($(this)); });
    $('#supplierModalTitle').text('✏️ Edit Supplier');
    $('#saveSupplierBtn').html('<i class="fa-solid fa-cloud-arrow-up"></i> Update Supplier');
    openModal('supplierModal');
});

/* ── SAVE SUPPLIER ── */
$('#supplierForm').on('submit',function(e){
    e.preventDefault();
    const btn = $('#saveSupplierBtn').prop('disabled',true).html('<i class="fa-solid fa-spinner fa-spin"></i> Saving...');
    $.post('controllers/SupplierController.php', $(this).serialize(), function(r){
        btn.prop('disabled',false).html('<i class="fa-solid fa-check"></i> Save Supplier');
        if(r.status === 'success'){
            Swal.fire({icon:'success',title:'Done!',text:r.message,timer:1800,showConfirmButton:false}).then(()=>location.reload());
            closeModal('supplierModal');
        } else {
            Swal.fire({icon:'error',title:'Error',text:r.message});
        }
    },'json').fail(()=>{ btn.prop('disabled',false); Swal.fire({icon:'error',title:'Network Error',text:'Could not reach server.'}); });
});

/* ── DELETE SUPPLIER ── */
$(document).on('click','.delete-supplier',function(){
    const id   = $(this).data('id');
    const name = $(this).data('name');
    Swal.fire({
        title:'Delete Supplier?',
        text:`"${name}" will be permanently removed.`,
        icon:'warning',
        showCancelButton:true,
        confirmButtonColor:'#d33',
        confirmButtonText:'Yes, Delete'
    }).then(result=>{
        if(!result.isConfirmed) return;
        $.post('controllers/SupplierController.php',{action:'delete_supplier',id},function(r){
            if(r.status==='success'){
                Swal.fire({icon:'success',title:'Deleted!',text:r.message,timer:1500,showConfirmButton:false}).then(()=>location.reload());
            } else {
                Swal.fire({icon:'error',title:'Cannot Delete',text:r.message});
            }
        },'json');
    });
});

/* ─────────────────────────────────────────────
   TAB 2 — PURCHASE ORDERS
───────────────────────────────────────────── */

function fmtDate(d){
    if(!d) return '—';
    const dt = new Date(d);
    return dt.toLocaleDateString('en-US',{month:'short',day:'numeric',year:'numeric'});
}
function poStatusBadge(s){
    const map={pending:'badge-pending',received:'badge-received',cancelled:'badge-cancelled'};
    return `<span class="badge ${map[s]||'badge-secondary'}">${s.charAt(0).toUpperCase()+s.slice(1)}</span>`;
}

var _allPOs = [];
function loadPOs(){
    const suppId = $('#poSupplierFilter').val()||0;
    $('#poTbody').html('<tr><td colspan="9" style="text-align:center;padding:30px;color:var(--text-muted);"><i class="fa-solid fa-spinner fa-spin"></i> Loading...</td></tr>');
    $.post('controllers/SupplierController.php',{action:'get_purchase_orders',supplier_id:suppId},function(r){
        _allPOs = r.data || [];
        renderPOs();
    },'json').fail(()=>$('#poTbody').html('<tr><td colspan="8" style="text-align:center;padding:20px;color:var(--danger);">Failed to load orders.</td></tr>'));
}
function renderPOs(){
    const q  = $('#poSearch').val().toLowerCase();
    const st = $('#poStatusFilter').val();
    let rows = _allPOs.filter(p=>{
        const matchQ  = !q || p.po_number.toLowerCase().includes(q) || (p.supplier_name||'').toLowerCase().includes(q);
        const matchSt = !st || p.status===st;
        return matchQ && matchSt;
    });
    if(!rows.length){
        $('#poTbody').html('<tr><td colspan="9" style="text-align:center;padding:40px;color:var(--text-muted);"><i class="fa-solid fa-file-circle-xmark" style="font-size:2rem;display:block;margin-bottom:12px;opacity:.25;"></i>No purchase orders found.</td></tr>');
        return;
    }
    let html='';
    rows.forEach((p, index) => {
        html+=`<tr data-id="${p.id}" data-status="${p.status}">
            <td>${index + 1}</td>
            <td class="fw-700">${p.po_number}</td>
            <td>${p.supplier_name||'—'}</td>
            <td>${fmtDate(p.order_date)}</td>
            <td>${fmtDate(p.expected_date)}</td>
            <td>${p.items}</td>
            <td class="fw-600 text-teal">GH₵${parseFloat(p.total).toLocaleString('en-US',{minimumFractionDigits:2})}</td>
            <td>${poStatusBadge(p.status)}</td>
            <td>
                <div class="d-flex gap-6">
                    ${p.status==='pending'?`<button class="btn btn-sm btn-secondary mark-received" data-id="${p.id}" title="Mark Received"><i class="fa-solid fa-circle-check" style="color:var(--success,#10b981);"></i></button>`:''}
                    <button class="btn btn-sm btn-secondary edit-po" data-id="${p.id}" title="Edit"><i class="fa-solid fa-pen-to-square" style="color:var(--primary-500);"></i></button>
                    <button class="btn btn-sm btn-secondary delete-po" data-id="${p.id}" data-num="${p.po_number}" title="Delete"><i class="fa-solid fa-trash" style="color:var(--danger);"></i></button>
                </div>
            </td>
        </tr>`;
    });
    $('#poTbody').html(html);
    initDataTable('#poTable');
}

// Override memory filter since DataTable has its own DOM now
$('#poSearch').on('keyup', function(){
    if ($.fn.DataTable.isDataTable('#poTable')) {
        $('#poTable').DataTable().search($(this).val()).draw();
    }
});
$('#poStatusFilter,#poSupplierFilter').on('change', loadPOs);

/* ── Add PO ── */
$('#addPOBtn').on('click',function(){
    $('#poForm')[0].reset();
    $('#poForm [name="id"]').val('0');
    $('#poForm [name="order_date"]').val('<?= date('Y-m-d') ?>').addClass('has-value');
    $('#poForm [name="expected_date"]').val('').removeClass('has-value');
    $('#poStatusGroup').hide();
    $('#poModalTitle').text('📋 New Purchase Order');
    $('#savePOBtn').html('<i class="fa-solid fa-check"></i> Create Order');
    $('select.floating-control').each(function(){ checkSel($(this)); });
    openModal('poModal');
});

/* ── Edit PO ── */
$(document).on('click','.edit-po',function(){
    const id = $(this).closest('tr').data('id');
    $.post('controllers/SupplierController.php',{action:'get_po',id},function(r){
        if(r.status!=='success'){ toast(r.message,'error'); return; }
        const p=r.data;
        const f=$('#poForm');
        f.find('[name="id"]').val(p.id);
        f.find('[name="supplier_id"]').val(p.supplier_id);
        f.find('[name="order_date"]').val(p.order_date).addClass('has-value');
        f.find('[name="expected_date"]').val(p.expected_date||'');
        if(p.expected_date) f.find('[name="expected_date"]').addClass('has-value');
        f.find('[name="items"]').val(p.items);
        f.find('[name="total"]').val(p.total);
        f.find('[name="status"]').val(p.status);
        f.find('[name="notes"]').val(p.notes||'');
        f.find('.floating-control').trigger('input');
        $('select.floating-control').each(function(){ checkSel($(this)); });
        $('#poStatusGroup').show();
        $('#poModalTitle').text('✏️ Edit PO — '+p.po_number);
        $('#savePOBtn').html('<i class="fa-solid fa-cloud-arrow-up"></i> Update Order');
        openModal('poModal');
    },'json');
});

/* ── Save PO ── */
$('#poForm').on('submit',function(e){
    e.preventDefault();
    const btn=$('#savePOBtn').prop('disabled',true).html('<i class="fa-solid fa-spinner fa-spin"></i> Saving...');
    $.post('controllers/SupplierController.php',$(this).serialize(),function(r){
        btn.prop('disabled',false);
        if(r.status==='success'){
            closeModal('poModal');
            Swal.fire({icon:'success',title:'Done!',text:r.message,timer:1800,showConfirmButton:false}).then(()=>loadPOs());
        } else {
            btn.html('<i class="fa-solid fa-check"></i> Save');
            Swal.fire({icon:'error',title:'Error',text:r.message});
        }
    },'json').fail(()=>{ btn.prop('disabled',false); Swal.fire({icon:'error',title:'Network Error',text:'Could not reach server.'}); });
});

/* ── Mark Received ── */
$(document).on('click','.mark-received',function(){
    const id=$(this).data('id');
    Swal.fire({title:'Mark as Received?',text:'This purchase order will be marked as received.',icon:'question',showCancelButton:true,confirmButtonText:'Yes, Mark Received'})
    .then(result=>{
        if(!result.isConfirmed) return;
        $.post('controllers/SupplierController.php',{action:'update_po_status',id,status:'received'},function(r){
            if(r.status==='success'){ Swal.fire({icon:'success',title:'Updated!',text:r.message,timer:1500,showConfirmButton:false}).then(()=>loadPOs()); }
            else Swal.fire({icon:'error',title:'Error',text:r.message});
        },'json');
    });
});

/* ── Delete PO ── */
$(document).on('click','.delete-po',function(){
    const id  = $(this).data('id');
    const num = $(this).data('num');
    Swal.fire({title:'Delete Purchase Order?',text:`${num} will be permanently deleted.`,icon:'warning',showCancelButton:true,confirmButtonColor:'#d33',confirmButtonText:'Yes, Delete'})
    .then(result=>{
        if(!result.isConfirmed) return;
        $.post('controllers/SupplierController.php',{action:'delete_po',id},function(r){
            if(r.status==='success'){ Swal.fire({icon:'success',title:'Deleted!',text:r.message,timer:1500,showConfirmButton:false}).then(()=>loadPOs()); }
            else Swal.fire({icon:'error',title:'Error',text:r.message});
        },'json');
    });
});

/* ─────────────────────────────────────────────
   TAB 3 — PAYMENTS
───────────────────────────────────────────── */

function pmethodBadge(m){
    m = (m||'').toLowerCase();
    if(m==='cash')           return `<span class="pm-badge pm-cash"><i class="fa-solid fa-money-bill-wave"></i> Cash</span>`;
    if(m==='bank transfer')  return `<span class="pm-badge pm-bank"><i class="fa-solid fa-building-columns"></i> Bank Transfer</span>`;
    if(m==='check')          return `<span class="pm-badge pm-check"><i class="fa-solid fa-money-check"></i> Check</span>`;
    if(m==='mobile money')   return `<span class="pm-badge pm-momo"><i class="fa-solid fa-mobile"></i> MoMo</span>`;
    return `<span class="pm-badge pm-other">${m||'—'}</span>`;
}

var _allPayments=[];
function loadPayments(){
    const suppId=$('#paySupplierFilter').val()||0;
    $('#payTbody').html('<tr><td colspan="9" style="text-align:center;padding:30px;color:var(--text-muted);"><i class="fa-solid fa-spinner fa-spin"></i> Loading...</td></tr>');
    $.post('controllers/SupplierController.php',{action:'get_payments',supplier_id:suppId},function(r){
        _allPayments=r.data||[];
        renderPayments();
    },'json').fail(()=>$('#payTbody').html('<tr><td colspan="8" style="text-align:center;padding:20px;color:var(--danger);">Failed to load payments.</td></tr>'));
}
function renderPayments(){
    const q=$('#paySearch').val().toLowerCase();
    let rows=_allPayments.filter(p=>{
        return !q || (p.supplier_name||'').toLowerCase().includes(q) || (p.po_number||'').toLowerCase().includes(q) || (p.reference||'').toLowerCase().includes(q);
    });
    if(!rows.length){
        $('#payTbody').html('<tr><td colspan="9" style="text-align:center;padding:40px;color:var(--text-muted);"><i class="fa-solid fa-receipt" style="font-size:2rem;display:block;margin-bottom:12px;opacity:.25;"></i>No payments recorded yet.</td></tr>');
        return;
    }
    let html='';
    rows.forEach((p, index) => {
        const isPaid = p.status==='paid';
        html+=`<tr>
            <td>${index + 1}</td>
            <td>${fmtDate(p.payment_date)}</td>
            <td class="fw-600">${p.supplier_name||'—'}</td>
            <td>${p.po_number?`<span class="fw-600" style="font-size:.82rem;">${p.po_number}</span>`:'<span style="color:var(--text-muted)">—</span>'}</td>
            <td class="fw-700 text-teal">GH₵${parseFloat(p.amount).toLocaleString('en-US',{minimumFractionDigits:2})}</td>
            <td>${pmethodBadge(p.method)}</td>
            <td style="font-size:.82rem;color:var(--text-muted);">${p.reference||'—'}</td>
            <td><span class="badge ${isPaid?'badge-success':'badge-pending'}">${isPaid?'Paid':'Pending'}</span></td>
            <td>
                <button class="btn btn-sm btn-secondary delete-payment" data-id="${p.id}" title="Delete">
                    <i class="fa-solid fa-trash" style="color:var(--danger);"></i>
                </button>
            </td>
        </tr>`;
    });
    $('#payTbody').html(html);
    initDataTable('#paymentsTable');
}

$('#paySearch').on('keyup', function(){
    if ($.fn.DataTable.isDataTable('#paymentsTable')) {
        $('#paymentsTable').DataTable().search($(this).val()).draw();
    }
});
$('#paySupplierFilter').on('change', loadPayments);

/* ── Populate PO dropdown when supplier changes in Payment modal ── */
$('#paySupplierSelect').on('change',function(){
    const suppId=$(this).val();
    const poSel=$('#payPOSelect').html('<option value="0">— No linked PO —</option>');
    if(!suppId) return;
    // Only fetch purchase orders that have NOT been received yet (pending only)
    $.post('controllers/SupplierController.php',{action:'get_purchase_orders',supplier_id:suppId},function(r){
        if(r.status==='success'){
            const pending = r.data.filter(p => p.status === 'pending');
            if(pending.length === 0){
                poSel.append('<option value="" disabled>— No pending orders for this supplier —</option>');
            } else {
                pending.forEach(p=>{
                    poSel.append(`<option value="${p.id}">${p.po_number} — GH₵${parseFloat(p.total).toLocaleString('en-US',{minimumFractionDigits:2})}</option>`);
                });
            }
            checkSel(poSel);
        }
    },'json');
});

/* ── Add Payment ── */
$('#addPaymentBtn').on('click',function(){
    $('#paymentForm')[0].reset();
    $('#payPOSelect').html('<option value="0">— No linked PO —</option>');
    $('#paymentForm [name="payment_date"]').val('<?= date('Y-m-d') ?>').addClass('has-value');
    $('select.floating-control').each(function(){ checkSel($(this)); });
    openModal('paymentModal');
});

/* ── Save Payment ── */
$('#paymentForm').on('submit',function(e){
    e.preventDefault();
    const btn=$('#savePaymentBtn').prop('disabled',true).html('<i class="fa-solid fa-spinner fa-spin"></i> Saving...');
    $.post('controllers/SupplierController.php',$(this).serialize(),function(r){
        btn.prop('disabled',false).html('<i class="fa-solid fa-circle-check"></i> Record Payment');
        if(r.status==='success'){
            closeModal('paymentModal');
            Swal.fire({icon:'success',title:'Payment Recorded!',text:r.message,timer:1800,showConfirmButton:false}).then(()=>loadPayments());
        } else {
            Swal.fire({icon:'error',title:'Error',text:r.message});
        }
    },'json').fail(()=>{ btn.prop('disabled',false); Swal.fire({icon:'error',title:'Network Error',text:'Could not reach server.'}); });
});

/* ── Delete Payment ── */
$(document).on('click','.delete-payment',function(){
    const id=$(this).data('id');
    Swal.fire({title:'Delete Payment?',text:'This payment record will be permanently removed.',icon:'warning',showCancelButton:true,confirmButtonColor:'#d33',confirmButtonText:'Yes, Delete'})
    .then(result=>{
        if(!result.isConfirmed) return;
        $.post('controllers/SupplierController.php',{action:'delete_payment',id},function(r){
            if(r.status==='success'){ Swal.fire({icon:'success',title:'Deleted!',text:r.message,timer:1500,showConfirmButton:false}).then(()=>loadPayments()); }
            else Swal.fire({icon:'error',title:'Error',text:r.message});
        },'json');
    });
});

/* ── Pre-load tabs that are active on load ── */
const activeTab = localStorage.getItem(TAB_KEY);
if(activeTab==='tab-purchaseorders') loadPOs();
else if(activeTab==='tab-supppayments') loadPayments();

});
</script>
