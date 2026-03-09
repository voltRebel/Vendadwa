<?php
require_once 'includes/queries.php';
$customers = getCustomers();

$totalCustomers = count($customers);
$totalPoints    = array_sum(array_column($customers, 'loyalty_points'));
$totalSpend     = array_sum(array_column($customers, 'total_purchases'));
$goldTier       = count(array_filter($customers, fn($c) => $c['loyalty_points'] >= 200));

// Helper: tier data from points
function getTier(int $pts): array {
    if ($pts >= 500) return ['label'=>'Platinum','cls'=>'tier-platinum','icon'=>'fa-gem','next'=>null,'nextPts'=>0];
    if ($pts >= 200) return ['label'=>'Gold',    'cls'=>'tier-gold',    'icon'=>'fa-crown','next'=>'Platinum','nextPts'=>500];
    if ($pts >= 50)  return ['label'=>'Silver',  'cls'=>'tier-silver',  'icon'=>'fa-medal','next'=>'Gold','nextPts'=>200];
    return               ['label'=>'Bronze',  'cls'=>'tier-bronze',  'icon'=>'fa-medal','next'=>'Silver','nextPts'=>50];
}
function tierProgress(int $pts): int {
    if ($pts >= 500) return 100;
    if ($pts >= 200) return (int)(($pts-200)/3);
    if ($pts >= 50)  return (int)(($pts-50)/1.5);
    return (int)($pts*2);
}
?>

<!-- Top Bar -->
<div class="top-bar">
    <div class="top-bar-left">
        <button class="mobile-menu-toggle" id="mobileMenuToggle"><i class="fa-solid fa-bars"></i></button>
        <h1>Customers</h1>
    </div>
    <div class="top-bar-right">
        <button class="btn btn-primary" id="addCustomerBtn">
            <i class="fa-solid fa-user-plus"></i> Add Customer
        </button>
    </div>
</div>

<!-- ── Stats ── -->
<div class="stats-grid cust-stats-grid" style="margin-bottom:24px;">
    <div class="stat-card pink">
        <div class="stat-icon pink"><i class="fa-solid fa-users"></i></div>
        <div class="stat-info">
            <h3><?= $totalCustomers ?></h3>
            <p>Total Customers</p>
            <span class="stat-change up"><i class="fa-solid fa-user-check"></i> Registered</span>
        </div>
    </div>
    <div class="stat-card purple">
        <div class="stat-icon purple"><i class="fa-solid fa-star"></i></div>
        <div class="stat-info">
            <h3><?= number_format($totalPoints) ?></h3>
            <p>Points Issued</p>
            <span class="stat-change up"><i class="fa-solid fa-award"></i> Active pts</span>
        </div>
    </div>
    <div class="stat-card gold">
        <div class="stat-icon gold"><i class="fa-solid fa-crown"></i></div>
        <div class="stat-info">
            <h3><?= $goldTier ?></h3>
            <p>Gold+ Members</p>
            <span class="stat-change up"><i class="fa-solid fa-trophy"></i> 200+ pts</span>
        </div>
    </div>
    <div class="stat-card green">
        <div class="stat-icon" style="background:var(--success-bg);color:var(--success)"><i class="fa-solid fa-circle-dollar-to-slot"></i></div>
        <div class="stat-info">
            <h3>GH₵<?= number_format($totalSpend, 0) ?></h3>
            <p>Total Revenue from Customers</p>
            <span class="stat-change up"><i class="fa-solid fa-arrow-trend-up"></i> All time</span>
        </div>
    </div>
</div>

<!-- ── Main Card ── -->
<div class="glass-card-static">
    <div class="tab-nav">
        <button class="tab-btn active" data-tab="tab-custlist">Customer List</button>
        <button class="tab-btn" data-tab="tab-custhistory">Purchase History</button>
        <button class="tab-btn" data-tab="tab-loyalty">Loyalty Points</button>
    </div>

    <!-- ====== TAB: CUSTOMER LIST ====== -->
    <div class="tab-content active" id="tab-custlist">
        <div class="d-flex align-center justify-between mb-16" style="flex-wrap:wrap;gap:10px;">
            <div class="search-bar">
                <i class="fa-solid fa-magnifying-glass search-icon"></i>
                <input type="text" id="customerSearch" placeholder="Search customers...">
            </div>
            <div class="d-flex gap-8 align-center">
                <select class="form-control" id="tierFilter" style="width:auto;padding:8px 34px 8px 12px;font-size:0.82rem;">
                    <option value="">All Tiers</option>
                    <option value="Platinum">Platinum</option>
                    <option value="Gold">Gold</option>
                    <option value="Silver">Silver</option>
                    <option value="Bronze">Bronze</option>
                </select>
            </div>
        </div>
        <div class="table-container">
            <table class="data-table" id="customersTable">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Customer</th>
                        <th>Contact</th>
                        <th>Total Spend</th>
                        <th>Points</th>
                        <th>Tier</th>
                        <th>Member Since</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                    $i = 1;
                    foreach ($customers as $c):
                    $pts  = (int)$c['loyalty_points'];
                    $tier = getTier($pts);
                    $prog = tierProgress($pts);
                    $initials = strtoupper(implode('', array_map(fn($w)=>$w[0], explode(' ', trim($c['name'])))));
                    $initials = substr($initials, 0, 2);
                ?>
                    <tr data-name="<?= htmlspecialchars(strtolower($c['name'])) ?>"
                        data-tier="<?= $tier['label'] ?>"
                        data-id="<?= $c['id'] ?>"
                        data-cname="<?= htmlspecialchars($c['name']) ?>"
                        data-phone="<?= htmlspecialchars($c['phone'] ?? '') ?>"
                        data-email="<?= htmlspecialchars($c['email'] ?? '') ?>"
                        data-address="<?= htmlspecialchars($c['address'] ?? '') ?>"
                        data-notes="<?= htmlspecialchars($c['notes'] ?? '') ?>"
                        data-points="<?= $pts ?>"
                        data-spend="<?= number_format($c['total_purchases'], 2) ?>"
                        data-since="<?= date('M d, Y', strtotime($c['created_at'])) ?>">
                        <td><?= $i++ ?></td>
                        <td>
                            <div class="d-flex align-center gap-10">
                                <div class="cust-avatar <?= $tier['cls'] ?>"><?= $initials ?></div>
                                <div>
                                    <div class="fw-600"><?= htmlspecialchars($c['name']) ?></div>
                                    <div class="cust-progress-mini">
                                        <div class="cust-progress-bar" style="width:<?= $prog ?>%"></div>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div style="font-size:0.83rem;">
                                <?php if ($c['phone']): ?><div><i class="fa-solid fa-phone" style="opacity:.5;width:14px;"></i> <?= htmlspecialchars($c['phone']) ?></div><?php endif; ?>
                                <?php if ($c['email']): ?><div style="color:var(--text-muted);"><i class="fa-solid fa-envelope" style="opacity:.5;width:14px;"></i> <?= htmlspecialchars($c['email']) ?></div><?php endif; ?>
                                <?php if (!$c['phone'] && !$c['email']): ?><span style="color:var(--text-muted);">—</span><?php endif; ?>
                            </div>
                        </td>
                        <td class="fw-600 text-teal">GH₵<?= number_format($c['total_purchases'], 2) ?></td>
                        <td><span class="fw-700"><?= number_format($pts) ?></span> <span style="color:var(--text-muted);font-size:0.8rem;">pts</span></td>
                        <td>
                            <span class="badge-tier <?= $tier['cls'] ?>">
                                <i class="fa-solid <?= $tier['icon'] ?>"></i> <?= $tier['label'] ?>
                            </span>
                        </td>
                        <td style="color:var(--text-muted);font-size:0.82rem;"><?= date('M Y', strtotime($c['created_at'])) ?></td>
                        <td>
                            <div class="d-flex gap-6">
                                <button class="btn btn-sm btn-secondary view-customer" title="View Profile">
                                    <i class="fa-solid fa-eye" style="color:var(--info,#3b82f6);"></i>
                                </button>
                                <button class="btn btn-sm btn-secondary edit-customer" title="Edit">
                                    <i class="fa-solid fa-pen-to-square" style="color:var(--primary-500);"></i>
                                </button>
                                <button class="btn btn-sm btn-secondary delete-customer"
                                    data-id="<?= $c['id'] ?>" data-name="<?= htmlspecialchars($c['name']) ?>" title="Delete">
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

    <!-- ====== TAB: PURCHASE HISTORY ====== -->
    <div class="tab-content" id="tab-custhistory">
        <div class="d-flex align-center justify-between mb-16" style="flex-wrap:wrap;gap:10px;">
            <h3 style="margin:0;">Purchase History</h3>
            <div class="d-flex gap-10">
                <select class="form-control" id="historyCustomerFilter" style="width:auto;padding:8px 34px 8px 12px;font-size:0.82rem;">
                    <option value="0">All Customers</option>
                    <?php foreach ($customers as $c): ?>
                    <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <button class="btn btn-primary btn-sm" id="addPurchaseBtn">
                    <i class="fa-solid fa-plus"></i> Add Purchase
                </button>
            </div>
        </div>
        <div class="table-container">
            <table class="data-table" id="purchaseTable">
                <thead>
                    <tr><th>#</th><th>Date</th><th>Receipt #</th><th>Customer</th><th>Items</th><th>Total</th><th>Payment</th><th>Points</th></tr>
                </thead>
                <tbody id="purchaseTbody">
                    <tr><td colspan="8" style="text-align:center;padding:30px;color:var(--text-muted);"><i class="fa-solid fa-spinner fa-spin"></i> Loading...</td></tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- ====== TAB: LOYALTY POINTS ====== -->
    <div class="tab-content" id="tab-loyalty">
        <div class="d-flex align-center justify-between mb-16">
            <h3 style="margin:0;">Loyalty Program</h3>
            <span class="badge badge-teal">1 point per GH₵10 spent</span>
        </div>

        <!-- Tier legend -->
        <div class="tier-legend mb-16">
            <div class="tier-legend-item" style="--tc:#cd7f32;"><i class="fa-solid fa-medal"></i> Bronze <span>0–49 pts</span></div>
            <div class="tier-legend-item" style="--tc:#9ca3af;"><i class="fa-solid fa-medal"></i> Silver <span>50–199 pts</span></div>
            <div class="tier-legend-item" style="--tc:#f59e0b;"><i class="fa-solid fa-crown"></i> Gold <span>200–499 pts</span></div>
            <div class="tier-legend-item" style="--tc:#7c3aed;"><i class="fa-solid fa-gem"></i> Platinum <span>500+ pts</span></div>
        </div>

        <div class="table-container">
            <table class="data-table" id="loyaltyTable">
                <thead>
                    <tr><th>#</th><th>Customer</th><th>Points</th><th>Progress</th><th>Total Spend</th><th>Tier</th><th>Since</th><th>Actions</th></tr>
                </thead>
                <tbody>
                <?php 
                    $sorted = $customers;
                    usort($sorted, fn($a,$b) => $b['loyalty_points'] - $a['loyalty_points']);
                    $j = 1;
                    foreach ($sorted as $c):
                        $pts  = (int)$c['loyalty_points'];
                        $tier = getTier($pts);
                        $prog = tierProgress($pts);
                        $initials = strtoupper(implode('', array_map(fn($w)=>$w[0], explode(' ', trim($c['name'])))));
                        $initials = substr($initials, 0, 2);
                ?>
                <tr>
                    <td><?= $j++ ?></td>
                    <td>
                        <div class="d-flex align-center gap-10">
                            <div class="cust-avatar sm <?= $tier['cls'] ?>"><?= $initials ?></div>
                            <div class="fw-600"><?= htmlspecialchars($c['name']) ?></div>
                        </div>
                    </td>
                    <td><span class="fw-700 text-teal"><?= number_format($pts) ?></span> pts</td>
                    <td style="min-width:120px;">
                        <div class="loyalty-progress">
                            <div class="loyalty-bar" style="width:<?= $prog ?>%"></div>
                        </div>
                        <?php if ($tier['next']): ?>
                        <div style="font-size:0.72rem;color:var(--text-muted);margin-top:4px;"><?= $tier['nextPts']-$pts ?> pts to <?= $tier['next'] ?></div>
                        <?php endif; ?>
                    </td>
                    <td class="fw-600">GH₵<?= number_format($c['total_purchases'], 2) ?></td>
                    <td><span class="badge-tier <?= $tier['cls'] ?>"><i class="fa-solid <?= $tier['icon'] ?>"></i> <?= $tier['label'] ?></span></td>
                    <td style="color:var(--text-muted);font-size:0.82rem;"><?= date('M d, Y', strtotime($c['created_at'])) ?></td>
                    <td>
                        <button class="btn btn-sm btn-primary adjust-points"
                            data-id="<?= $c['id'] ?>"
                            data-name="<?= htmlspecialchars($c['name']) ?>"
                            data-points="<?= $pts ?>">
                            <i class="fa-solid fa-sliders"></i> Adjust
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ==================== MODALS ==================== -->

<!-- View Customer Modal -->
<div class="modal-overlay" id="viewCustomerModal">
    <div class="modal modal-lg">
        <div class="modal-header">
            <h3 id="viewCustomerTitle">👤 Customer Profile</h3>
            <button type="button" class="modal-close">&times;</button>
        </div>
        <div class="modal-body p-24">
            <div class="view-cust-layout">
                <!-- Avatar col -->
                <div class="view-cust-img-col">
                    <div class="view-cust-circle" id="viewCustAvatar">JD</div>
                    <div class="view-cust-badges">
                        <div id="viewCustTierBadge"></div>
                        <div style="font-size:0.75rem;color:var(--text-muted);text-align:center;">Member since<br><span id="viewCustSince" class="fw-600 text-teal"></span></div>
                    </div>
                </div>
                <!-- Info col -->
                <div class="view-product-info-col">
                    <div class="view-detail-grid">
                        <div class="view-detail-item">
                            <span class="vd-label"><i class="fa-solid fa-phone"></i> Phone</span>
                            <span class="vd-value" id="vc-phone">—</span>
                        </div>
                        <div class="view-detail-item">
                            <span class="vd-label"><i class="fa-solid fa-envelope"></i> Email</span>
                            <span class="vd-value" id="vc-email">—</span>
                        </div>
                        <div class="view-detail-item">
                            <span class="vd-label"><i class="fa-solid fa-star"></i> Loyalty Points</span>
                            <span class="vd-value fw-700 text-teal" id="vc-points">0 pts</span>
                        </div>
                        <div class="view-detail-item">
                            <span class="vd-label"><i class="fa-solid fa-circle-dollar-to-slot"></i> Total Spend</span>
                            <span class="vd-value fw-700" id="vc-spend">GH₵0.00</span>
                        </div>
                        <div class="view-detail-item" style="grid-column:1/-1;">
                            <span class="vd-label"><i class="fa-solid fa-location-dot"></i> Address</span>
                            <span class="vd-value" id="vc-address">—</span>
                        </div>
                        <div class="view-detail-item" style="grid-column:1/-1;">
                            <span class="vd-label"><i class="fa-solid fa-note-sticky"></i> Notes</span>
                            <span class="vd-value" id="vc-notes" style="white-space:pre-wrap;">—</span>
                        </div>
                    </div>
                    <!-- Mini purchase history -->
                    <div class="mini-history-wrap">
                        <div class="mini-history-header">
                            <span><i class="fa-solid fa-clock-rotate-left"></i> Recent Purchases</span>
                            <span class="badge badge-teal" id="miniPurchaseCount">—</span>
                        </div>
                        <div class="mini-history-body" id="miniPurchaseList">
                            <div style="text-align:center;padding:16px;color:var(--text-muted);font-size:0.82rem;"><i class="fa-solid fa-spinner fa-spin"></i></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary modal-cancel">Close</button>
            <button type="button" class="btn btn-primary" id="viewToEditCustBtn"><i class="fa-solid fa-pen-to-square"></i> Edit Customer</button>
        </div>
    </div>
</div>

<!-- Add/Edit Customer Modal -->
<div class="modal-overlay" id="customerModal">
    <div class="modal">
        <form id="customerForm">
            <input type="hidden" name="id" value="0">
            <input type="hidden" name="action" value="save_customer">
            <div class="modal-header">
                <h3 id="customerModalTitle">👤 Add Customer</h3>
                <button type="button" class="modal-close">&times;</button>
            </div>
            <div class="modal-body p-24">
                <div class="d-flex flex-column gap-16">
                    <div class="floating-group">
                        <input type="text" name="name" class="floating-control" required placeholder=" ">
                        <label class="floating-label">Full Name *</label>
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
                        <textarea name="notes" class="floating-control" style="height:80px;" placeholder=" "></textarea>
                        <label class="floating-label">Notes (Optional)</label>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary modal-cancel">Cancel</button>
                <button type="submit" class="btn btn-primary" id="saveCustomerBtn">
                    <i class="fa-solid fa-check"></i> Save Customer
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Add Purchase Modal -->
<div class="modal-overlay" id="addPurchaseModal">
    <div class="modal">
        <form id="addPurchaseForm">
            <input type="hidden" name="action" value="add_purchase">
            <div class="modal-header">
                <h3>🧾 Record Purchase</h3>
                <button type="button" class="modal-close">&times;</button>
            </div>
            <div class="modal-body p-24">
                <div class="d-flex flex-column gap-16">
                    <div class="floating-group">
                        <select name="customer_id" id="purchaseCustomerSelect" class="floating-control" required>
                            <option value="">Select Customer</option>
                            <?php foreach ($customers as $c): ?>
                            <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <label class="floating-label">Customer *</label>
                    </div>
                    <div class="d-grid gap-12" style="grid-template-columns:1fr 1fr;">
                        <div class="floating-group">
                            <input type="text" name="receipt_no" class="floating-control" placeholder=" ">
                            <label class="floating-label">Receipt # (auto if blank)</label>
                        </div>
                        <div class="floating-group">
                            <input type="date" name="purchase_date" class="floating-control has-value" value="<?= date('Y-m-d') ?>">
                            <label class="floating-label">Date</label>
                        </div>
                    </div>
                    <div class="d-grid gap-12" style="grid-template-columns:1fr 1fr;">
                        <div class="floating-group">
                            <input type="number" name="items" class="floating-control" min="1" value="1" placeholder=" ">
                            <label class="floating-label">No. of Items</label>
                        </div>
                        <div class="floating-group">
                            <input type="number" step="0.01" name="total" id="purchaseTotalInput" class="floating-control" required placeholder=" ">
                            <label class="floating-label">Total Amount (GH₵) *</label>
                        </div>
                    </div>
                    <div class="floating-group">
                        <select name="payment_method" class="floating-control">
                            <option>Cash</option>
                            <option>Card</option>
                            <option>Mobile Money</option>
                            <option>Bank Transfer</option>
                            <option>Other</option>
                        </select>
                        <label class="floating-label">Payment Method</label>
                    </div>
                    <div class="floating-group">
                        <textarea name="notes" class="floating-control" style="height:70px;" placeholder=" "></textarea>
                        <label class="floating-label">Notes (Optional)</label>
                    </div>
                    <div class="points-preview" id="pointsPreview" style="display:none;">
                        <i class="fa-solid fa-star"></i>
                        Customer will earn <strong id="pointsEarnedPreview">0</strong> loyalty points.
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary modal-cancel">Cancel</button>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-circle-check"></i> Record Purchase</button>
            </div>
        </form>
    </div>
</div>

<!-- Adjust Points Modal -->
<div class="modal-overlay" id="adjustPointsModal">
    <div class="modal">
        <form id="adjustPointsForm">
            <input type="hidden" name="action" value="adjust_points">
            <input type="hidden" name="customer_id" value="0">
            <div class="modal-header">
                <h3>⭐ Adjust Points — <span id="adjustPointsName"></span></h3>
                <button type="button" class="modal-close">&times;</button>
            </div>
            <div class="modal-body p-24">
                <div class="d-flex flex-column gap-16">
                    <div class="points-balance-display">
                        Current Balance<strong id="currentPointsDisplay">0</strong>
                    </div>
                    <div class="floating-group">
                        <select name="type" id="adjustType" class="floating-control">
                            <option value="add">➕ Add Points</option>
                            <option value="redeem">➖ Redeem Points</option>
                        </select>
                        <label class="floating-label">Action</label>
                    </div>
                    <div class="floating-group">
                        <input type="number" name="points" class="floating-control" required min="1" placeholder=" ">
                        <label class="floating-label">Points Amount *</label>
                    </div>
                    <div class="floating-group">
                        <input type="text" name="reason" class="floating-control" placeholder=" ">
                        <label class="floating-label">Reason (Optional)</label>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary modal-cancel">Cancel</button>
                <button type="submit" class="btn btn-primary" id="adjustPointsBtn">
                    <i class="fa-solid fa-plus"></i> Add Points
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ==================== STYLES ==================== -->
<style>
/* ── Avatar ── */
.cust-avatar {
    width:40px;height:40px;border-radius:50%;
    color:#fff;font-weight:700;font-size:0.82rem;
    display:flex;align-items:center;justify-content:center;
    flex-shrink:0;font-family:'Outfit',sans-serif;letter-spacing:.5px;
}
.cust-avatar.sm { width:32px;height:32px;font-size:0.72rem; }

/* tier colours for avatar */
.tier-bronze  { background:linear-gradient(135deg,#c97b2e,#f0a04b); box-shadow:0 2px 8px rgba(201,123,46,.3); }
.tier-silver  { background:linear-gradient(135deg,#6b7280,#9ca3af); box-shadow:0 2px 8px rgba(107,114,128,.3); }
.tier-gold    { background:linear-gradient(135deg,#d97706,#fbbf24); box-shadow:0 2px 8px rgba(217,119,6,.3); }
.tier-platinum{ background:linear-gradient(135deg,#6d28d9,#a78bfa); box-shadow:0 2px 8px rgba(109,40,217,.3); }

/* tier badge (table) */
.badge-tier {
    display:inline-flex;align-items:center;gap:5px;
    padding:4px 10px;border-radius:20px;font-size:0.76rem;font-weight:600;
}
.badge-tier.tier-bronze  { background:rgba(201,123,46,.12); color:#92400e; }
.badge-tier.tier-silver  { background:rgba(107,114,128,.12); color:#374151; }
.badge-tier.tier-gold    { background:rgba(217,119,6,.12);  color:#92400e; }
.badge-tier.tier-platinum{ background:rgba(109,40,217,.12); color:#5b21b6; }

/* ── Mini progress bar (in table row) ── */
.cust-progress-mini {
    width:100%;height:3px;border-radius:10px;
    background:var(--border-color,#e5e7eb);margin-top:5px;overflow:hidden;
}
.cust-progress-bar {
    height:100%;border-radius:10px;
    background:linear-gradient(90deg,var(--primary-300),var(--primary-500));
    transition:width .6s ease;
}

/* ── Stats grid ── */
.cust-stats-grid { grid-template-columns:repeat(4,1fr); }
@media(max-width:1100px){ .cust-stats-grid{ grid-template-columns:repeat(2,1fr); } }
@media(max-width:600px) { .cust-stats-grid{ grid-template-columns:1fr; } }

/* ── Profile modal layout (matches product view style) ── */
.view-cust-layout { display:grid; grid-template-columns:180px 1fr; gap:28px; align-items:start; }
@media(max-width:640px){ .view-cust-layout{ grid-template-columns:1fr; } }
.view-cust-img-col { display:flex; flex-direction:column; align-items:center; gap:14px; }
.view-cust-circle {
    width:150px; height:150px;
    border-radius:50%;
    background:linear-gradient(135deg,var(--primary-50),var(--primary-100));
    border:3px solid var(--primary-200);
    box-shadow:0 6px 24px rgba(26,138,124,0.18);
    display:flex; align-items:center; justify-content:center;
    font-size:2.8rem; color:var(--primary-400);
    font-weight:800; font-family:'Outfit',sans-serif;
    flex-shrink:0;
}
/* Override when tier class is applied — keep the gradient from tier colours */
.view-cust-circle.tier-bronze,
.view-cust-circle.tier-silver,
.view-cust-circle.tier-gold,
.view-cust-circle.tier-platinum { color:#fff; }
.view-cust-badges { display:flex; flex-direction:column; align-items:center; gap:8px; }
.text-teal { color:var(--primary-500)!important; }

/* ── View detail grid (shared with product modal style) ── */
.view-detail-grid { display:grid; grid-template-columns:1fr 1fr; gap:14px; }
.view-detail-item { display:flex; flex-direction:column; gap:4px; padding:12px 14px; background:rgba(26,138,124,0.03); border-radius:10px; border:1px solid rgba(26,138,124,0.07); }
.vd-label { font-size:0.72rem; font-weight:600; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.5px; display:flex; align-items:center; gap:5px; }
.vd-value { font-size:0.9rem; font-weight:500; color:var(--text-primary); }

/* ── Loyalty progress bar (full width) ── */
.loyalty-progress {
    width:100%;height:6px;border-radius:10px;
    background:var(--border-color,#e5e7eb);overflow:hidden;
}
.loyalty-bar {
    height:100%;border-radius:10px;
    background:linear-gradient(90deg,var(--primary-300),var(--primary-500));
    transition:width .7s ease;
}

/* ── Mini purchase history in view modal ── */
.mini-history-wrap {
    margin-top:20px;border-radius:12px;overflow:hidden;
    border:1px solid var(--border-color,#e5e7eb);
}
.mini-history-header {
    display:flex;justify-content:space-between;align-items:center;
    padding:10px 16px;
    background:var(--sidebar-bg,#f9fafb);
    font-size:0.82rem;font-weight:600;color:var(--text-secondary);
    border-bottom:1px solid var(--border-color,#e5e7eb);
}
.mini-history-body { max-height:180px;overflow-y:auto; }
.mini-purchase-row {
    display:grid;grid-template-columns:1fr auto auto;gap:8px;align-items:center;
    padding:9px 16px;border-bottom:1px solid var(--border-color,#f3f4f6);
    font-size:0.81rem;transition:background .15s;
}
.mini-purchase-row:last-child { border-bottom:none; }
.mini-purchase-row:hover { background:var(--hover-bg,#f9fafb); }

/* ── Tier legend ── */
.tier-legend { display:flex;gap:14px;flex-wrap:wrap;padding:14px 18px;border-radius:12px;background:rgba(0,0,0,.02);border:1px solid var(--border-color,#e5e7eb); }
.tier-legend-item { display:flex;align-items:center;gap:7px;font-size:0.82rem;font-weight:600;color:var(--tc,#666); }
.tier-legend-item span { font-weight:400;color:var(--text-muted); }

/* ── Points preview ── */
.points-preview {
    padding:12px 16px;border-radius:10px;
    background:linear-gradient(135deg,rgba(26,138,124,.06),rgba(26,138,124,.02));
    border:1px solid rgba(26,138,124,.12);
    font-size:0.85rem;color:var(--text-secondary);
    display:flex;align-items:center;gap:10px;
}
.points-preview i { color:#f59e0b;font-size:1.1rem; }

/* ── Points balance display ── */
.points-balance-display {
    padding:16px 20px;border-radius:12px;
    background:linear-gradient(135deg,rgba(26,138,124,.06),rgba(26,138,124,.02));
    border:1px solid rgba(26,138,124,.1);
    font-size:0.88rem;color:var(--text-secondary);
    text-align:center;
}
.points-balance-display strong { font-size:1.6rem;color:var(--primary-600,#1a8a7c);display:block;margin-top:4px;font-weight:800; }

/* ── payment badges ── */
.pay-badge { display:inline-flex;align-items:center;gap:4px;padding:3px 9px;border-radius:20px;font-size:0.76rem;font-weight:600; }
.pay-cash  { background:rgba(16,185,129,.1);color:#059669; }
.pay-card  { background:rgba(59,130,246,.1);color:#2563eb; }
.pay-momo  { background:rgba(245,158,11,.1);color:#b45309; }
.pay-other { background:rgba(107,114,128,.1);color:#374151; }

/* utils */
.mb-16{margin-bottom:16px;} .d-grid{display:grid;} .gap-12{gap:12px;} .fw-700{font-weight:700;}
.badge-warning{background:rgba(245,158,11,.12);color:#b45309;}
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

    initDataTable('#customersTable');
    initDataTable('#loyaltyTable');

/* ── Floating select helper ── */
function checkSel(s){ s.val() ? s.addClass('has-value') : s.removeClass('has-value'); }
$('select.floating-control').each(function(){ checkSel($(this)); }).on('change',function(){ checkSel($(this)); });

/* ── Tabs ── */
const TAB_KEY = 'vendora_customers_tab';
function activateTab(id){
    $('.tab-btn').removeClass('active'); $('.tab-content').removeClass('active');
    $(`.tab-btn[data-tab="${id}"]`).addClass('active'); $(`#${id}`).addClass('active');
    localStorage.setItem(TAB_KEY,id);
    if(id==='tab-custhistory') loadPurchaseHistory();
}
const saved=localStorage.getItem(TAB_KEY);
if(saved && $(`#${saved}`).length) activateTab(saved);
$('.tab-btn').on('click',function(){ activateTab($(this).data('tab')); });

/* ── Modals ── */
function openModal(id){  $('#'+id).addClass('active'); }
function closeModal(id){ $('#'+id).removeClass('active'); }
$(document).on('click','.modal-close,.modal-cancel',function(){
    $(this).closest('.modal-overlay').removeClass('active');
});

/* ── Search & filter (DataTables override) ── */
function filterTable(){
    const q = $('#customerSearch').val();
    const t = $('#tierFilter').val();
    
    // Use DataTables built-in search for the input box
    const table = $('#customersTable').DataTable();
    table.search(q).draw();

    // Custom filtering for the tier (since it's not a simple column search)
    $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
        if (settings.nTable.id !== 'customersTable') return true;
        
        if (!t) return true; // Show all tiers
        
        // The tier name is in the fifth column (index 4)
        const tierCol = data[4] || '';
        return tierCol.includes(t);
    });
    table.draw();
    $.fn.dataTable.ext.search.pop(); // Clean up so it doesn't affect other tables
}
$('#customerSearch').on('keyup', function() {
    $('#customersTable').DataTable().search($(this).val()).draw();
});
$('#tierFilter').on('change', filterTable);

/* ── Tier helpers (JS) ── */
function jsTier(pts){
    if(pts>=500) return {label:'Platinum',cls:'tier-platinum',icon:'fa-gem',badgeCls:'badge-tier tier-platinum',next:null,nextPts:0};
    if(pts>=200) return {label:'Gold',    cls:'tier-gold',    icon:'fa-crown',badgeCls:'badge-tier tier-gold',   next:'Platinum',nextPts:500};
    if(pts>=50)  return {label:'Silver',  cls:'tier-silver',  icon:'fa-medal',badgeCls:'badge-tier tier-silver', next:'Gold',    nextPts:200};
    return            {label:'Bronze',  cls:'tier-bronze',  icon:'fa-medal',badgeCls:'badge-tier tier-bronze', next:'Silver',  nextPts:50};
}
function jsProg(pts){
    if(pts>=500) return 100;
    if(pts>=200) return Math.min(100,Math.round((pts-200)/3));
    if(pts>=50)  return Math.min(100,Math.round((pts-50)/1.5));
    return Math.min(100,pts*2);
}
function payBadge(m){
    const v=(m||'').toLowerCase();
    if(v.includes('cash'))  return `<span class="pay-badge pay-cash"><i class="fa-solid fa-money-bill-wave"></i> Cash</span>`;
    if(v.includes('card'))  return `<span class="pay-badge pay-card"><i class="fa-solid fa-credit-card"></i> Card</span>`;
    if(v.includes('mobile')||v.includes('momo')) return `<span class="pay-badge pay-momo"><i class="fa-solid fa-mobile"></i> MoMo</span>`;
    return `<span class="pay-badge pay-other">${m||'—'}</span>`;
}

/* ── VIEW CUSTOMER ── */
var _viewCustId=0, _viewCustData={};
$(document).on('click','.view-customer',function(){
    const row = $(this).closest('tr');
    _viewCustId  = row.data('id');
    _viewCustData= {
        name:    row.data('cname'),
        phone:   row.data('phone'),
        email:   row.data('email'),
        address: row.data('address'),
        notes:   row.data('notes'),
        points:  parseInt(row.data('points'))||0,
        spend:   row.data('spend'),
        since:   row.data('since')
    };
    const d=_viewCustData, pts=d.points;
    const tier=jsTier(pts);
    const init=d.name.split(' ').map(w=>w[0]).join('').substr(0,2).toUpperCase();

    $('#viewCustomerTitle').text('👤 '+d.name);
    $('#viewCustAvatar').text(init).attr('class','view-cust-circle '+tier.cls);
    $('#viewCustTierBadge').html(`<span class="${tier.badgeCls}"><i class="fa-solid ${tier.icon}"></i> ${tier.label}</span>`);
    $('#viewCustSince').text(d.since||'—');
    $('#vc-phone').text(d.phone||'N/A');
    $('#vc-email').text(d.email||'N/A');
    $('#vc-points').text(pts.toLocaleString()+' pts');
    $('#vc-spend').text('GH₵'+d.spend);
    $('#vc-address').text(d.address||'N/A');
    $('#vc-notes').text(d.notes||'None');
    $('#miniPurchaseList').html('<div style="text-align:center;padding:16px;color:var(--text-muted);font-size:0.82rem;"><i class="fa-solid fa-spinner fa-spin"></i></div>');
    $('#miniPurchaseCount').text('…');
    openModal('viewCustomerModal');

    // Load mini history
    $.post('controllers/CustomerController.php',{action:'get_purchase_history',customer_id:_viewCustId},function(r){
        if(r.status==='success' && r.data.length>0){
            $('#miniPurchaseCount').text(r.data.length);
            let html='';
            r.data.slice(0,5).forEach(p=>{
                const d2=new Date(p.purchase_date||p.created_at);
                const ds=d2.toLocaleDateString('en-US',{month:'short',day:'numeric',year:'numeric'});
                html+=`<div class="mini-purchase-row">
                    <span style="color:var(--text-muted);">${ds} &nbsp;<span style="font-weight:600;color:var(--text-primary);">${p.receipt_no||'#—'}</span></span>
                    <span class="fw-600 text-teal">GH₵${parseFloat(p.total).toLocaleString('en-US',{minimumFractionDigits:2})}</span>
                    <span class="badge badge-warning" style="font-size:.72rem;"><i class="fa-solid fa-star" style="font-size:.65rem;"></i> +${p.points_earned}</span>
                </div>`;
            });
            if(r.data.length>5) html+=`<div style="text-align:center;padding:8px;font-size:0.75rem;color:var(--text-muted);">+${r.data.length-5} more</div>`;
            $('#miniPurchaseList').html(html);
        } else {
            $('#miniPurchaseCount').text(0);
            $('#miniPurchaseList').html('<div style="text-align:center;padding:16px;color:var(--text-muted);font-size:0.82rem;">No purchases yet.</div>');
        }
    },'json');
});

$('#viewToEditCustBtn').on('click',function(){
    closeModal('viewCustomerModal');
    const row=$(`#customersTable tr[data-id="${_viewCustId}"]`);
    row.find('.edit-customer').trigger('click');
});

/* ── ADD CUSTOMER ── */
$('#addCustomerBtn,#addCustEmptyLink').on('click',function(e){
    e.preventDefault();
    $('#customerForm')[0].reset();
    $('#customerForm input[name="id"]').val('0');
    $('#customerModalTitle').text('👤 Add Customer');
    $('#saveCustomerBtn').html('<i class="fa-solid fa-check"></i> Save Customer');
    openModal('customerModal');
});

/* ── EDIT CUSTOMER ── */
$(document).on('click','.edit-customer',function(){
    const row=$(this).closest('tr');
    const f=$('#customerForm');
    f.find('[name="id"]').val(row.data('id'));
    f.find('[name="name"]').val(row.data('cname'));
    f.find('[name="phone"]').val(row.data('phone'));
    f.find('[name="email"]').val(row.data('email'));
    f.find('[name="address"]').val(row.data('address'));
    f.find('[name="notes"]').val(row.data('notes'));
    f.find('.floating-control').trigger('input');
    $('#customerModalTitle').text('✏️ Edit Customer');
    $('#saveCustomerBtn').html('<i class="fa-solid fa-cloud-arrow-up"></i> Update Customer');
    openModal('customerModal');
});

/* ── SAVE CUSTOMER ── */
$('#customerForm').on('submit',function(e){
    e.preventDefault();
    const btn=$('#saveCustomerBtn').prop('disabled',true).html('<i class="fa-solid fa-spinner fa-spin"></i> Saving...');
    $.post('controllers/CustomerController.php',$(this).serialize(),function(r){
        btn.prop('disabled',false).html('<i class="fa-solid fa-check"></i> Save Customer');
        if(r.status==='success'){
            closeModal('customerModal');
            Swal.fire({icon:'success',title:'Done!',text:r.message,timer:1800,showConfirmButton:false}).then(()=>location.reload());
        } else {
            Swal.fire({icon:'error',title:'Error',text:r.message});
        }
    },'json').fail(()=>{ btn.prop('disabled',false); Swal.fire({icon:'error',title:'Network Error',text:'Could not reach server.'}); });
});

/* ── DELETE CUSTOMER ── */
$(document).on('click','.delete-customer',function(){
    const id=$(this).data('id'), name=$(this).data('name');
    Swal.fire({
        title:'Delete Customer?',
        html:`Are you sure you want to delete <strong>${name}</strong>? This cannot be undone.`,
        icon:'warning',showCancelButton:true,confirmButtonText:'Yes, Delete',
        confirmButtonColor:'#ef4444',cancelButtonText:'Cancel'
    }).then(r=>{
        if(r.isConfirmed){
            $.post('controllers/CustomerController.php',{action:'delete_customer',id},function(r){
                if(r.status==='success'){
                    Swal.fire({icon:'success',title:'Deleted!',text:r.message,timer:1500,showConfirmButton:false}).then(()=>location.reload());
                } else {
                    Swal.fire({icon:'error',title:'Cannot Delete',text:r.message});
                }
            },'json');
        }
    });
});

/* ── PURCHASE HISTORY ── */
function loadPurchaseHistory(){
    const cid=$('#historyCustomerFilter').val()||0;
    $('#purchaseTbody').html('<tr><td colspan="8" style="text-align:center;padding:30px;color:var(--text-muted);"><i class="fa-solid fa-spinner fa-spin"></i> Loading...</td></tr>');
    $.post('controllers/CustomerController.php',{action:'get_purchase_history',customer_id:cid},function(r){
        if(r.status==='success' && r.data.length>0){
            let html='';
            r.data.forEach((p, index) => {
                const d2=new Date(p.purchase_date||p.created_at);
                const ds=d2.toLocaleDateString('en-US',{month:'short',day:'numeric',year:'numeric'});
                html+=`<tr>
                    <td>${index + 1}</td>
                    <td style="color:var(--text-muted);font-size:.82rem;">${ds}</td>
                    <td><span class="fw-600" style="font-size:.82rem;">${p.receipt_no||'—'}</span></td>
                    <td class="fw-600">${p.customer_name}</td>
                    <td><span class="badge badge-teal">${p.items} item${p.items!=1?'s':''}</span></td>
                    <td class="fw-600 text-teal">GH₵${parseFloat(p.total).toLocaleString('en-US',{minimumFractionDigits:2})}</td>
                    <td>${payBadge(p.payment_method)}</td>
                    <td><span class="badge badge-warning"><i class="fa-solid fa-star" style="font-size:.7rem;"></i> +${p.points_earned}</span></td>
                </tr>`;
            });
            $('#purchaseTbody').html(html);
            initDataTable('#purchaseTable');
        } else {
            if ($.fn.DataTable.isDataTable('#purchaseTable')) $('#purchaseTable').DataTable().clear().destroy();
            $('#purchaseTbody').html('<tr><td colspan="8" style="text-align:center;padding:40px;color:var(--text-muted);"><i class="fa-solid fa-receipt" style="font-size:2rem;display:block;margin-bottom:12px;opacity:.3;"></i>No purchase records found.</td></tr>');
        }
    },'json');
}
$('#historyCustomerFilter').on('change',loadPurchaseHistory);

/* ── ADD PURCHASE ── */
$('#addPurchaseBtn').on('click',function(){
    $('#addPurchaseForm')[0].reset();
    $('input[name="purchase_date"]','#addPurchaseModal').val('<?= date('Y-m-d') ?>').addClass('has-value');
    $('input[name="items"]','#addPurchaseModal').val(1);
    $('#pointsPreview').hide();
    checkSel($('select.floating-control','#addPurchaseModal'));
    openModal('addPurchaseModal');
});

$('#purchaseTotalInput').on('input',function(){
    const t=parseFloat($(this).val())||0, pts=Math.floor(t/10);
    if(t>0){ $('#pointsEarnedPreview').text(pts); $('#pointsPreview').show(); }
    else { $('#pointsPreview').hide(); }
});

$('#addPurchaseForm').on('submit',function(e){
    e.preventDefault();
    if(!$('select[name="customer_id"]',this).val()){
        Swal.fire({icon:'warning',title:'Select Customer',text:'Please select a customer first.'}); return;
    }
    const btn=$(this).find('[type="submit"]').prop('disabled',true).html('<i class="fa-solid fa-spinner fa-spin"></i> Saving...');
    $.post('controllers/CustomerController.php',$(this).serialize(),function(r){
        btn.prop('disabled',false).html('<i class="fa-solid fa-circle-check"></i> Record Purchase');
        if(r.status==='success'){
            closeModal('addPurchaseModal');
            Swal.fire({icon:'success',title:'Purchase Recorded!',text:r.message,timer:2000,showConfirmButton:false}).then(()=>location.reload());
        } else {
            Swal.fire({icon:'error',title:'Error',text:r.message});
        }
    },'json');
});

/* ── ADJUST POINTS ── */
$(document).on('click','.adjust-points',function(){
    const id=$(this).data('id'), name=$(this).data('name'), pts=$(this).data('points');
    $('#adjustPointsForm input[name="customer_id"]').val(id);
    $('#adjustPointsName').text(name);
    $('#currentPointsDisplay').text(parseInt(pts).toLocaleString());
    $('#adjustPointsForm input[name="points"]').val('');
    $('#adjustPointsForm input[name="reason"]').val('');
    $('#adjustPointsBtn').html('<i class="fa-solid fa-plus"></i> Add Points');
    checkSel($('#adjustType'));
    openModal('adjustPointsModal');
});

$('#adjustType').on('change',function(){
    const isRedeem=$(this).val()==='redeem';
    $('#adjustPointsBtn').html(isRedeem ?
        '<i class="fa-solid fa-minus"></i> Redeem Points' :
        '<i class="fa-solid fa-plus"></i> Add Points');
});

$('#adjustPointsForm').on('submit',function(e){
    e.preventDefault();
    const btn=$('#adjustPointsBtn').prop('disabled',true).html('<i class="fa-solid fa-spinner fa-spin"></i>');
    $.post('controllers/CustomerController.php',$(this).serialize(),function(r){
        btn.prop('disabled',false).html('<i class="fa-solid fa-check"></i> Apply');
        if(r.status==='success'){
            closeModal('adjustPointsModal');
            Swal.fire({icon:'success',title:'Points Updated',text:r.message,timer:1800,showConfirmButton:false}).then(()=>location.reload());
        } else {
            Swal.fire({icon:'error',title:'Error',text:r.message});
        }
    },'json');
});

/* ── Auto-load history if that tab was active ── */
if(localStorage.getItem(TAB_KEY)==='tab-custhistory') loadPurchaseHistory();

});
</script>
