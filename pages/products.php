<?php
require_once 'includes/queries.php';
$products   = getProducts();
$categories = getCategories();
$units      = getUnits();
$lowStock   = getLowStockProducts();
$stockIn    = getStockMovements('in');
$stockOut   = getStockMovements('out');
$adjustments= getStockMovements('adjustment');
?>

<!-- Top Bar -->
<div class="top-bar">
    <div class="top-bar-left">
        <button class="mobile-menu-toggle" id="mobileMenuToggle"><i class="fa-solid fa-bars"></i></button>
        <h1>Products &amp; Inventory</h1>
    </div>
    <div class="top-bar-right">
        <button class="btn btn-primary" data-modal="addProductModal">
            <i class="fa-solid fa-plus"></i> Add Product
        </button>
    </div>
</div>

<!-- ==================== STATS CARDS ==================== -->
<?php
    $totalProducts   = count($products);
    $totalCategories = count($categories);
    $lowStockCount   = count($lowStock);
    $outOfStock      = count(array_filter($products, fn($p) => $p['stock_quantity'] <= 0));
    $totalStockValue = array_sum(array_map(fn($p) => $p['cost_price'] * $p['stock_quantity'], $products));
?>
<div class="stats-grid inv-stats-grid" style="margin-bottom:24px;">
    <div class="stat-card pink">
        <div class="stat-icon pink"><i class="fa-solid fa-boxes-stacked"></i></div>
        <div class="stat-info">
            <h3><?= $totalProducts ?></h3>
            <p>Total Products</p>
            <span class="stat-change up"><i class="fa-solid fa-box"></i> <?= count($categories) ?> categor<?= count($categories)==1?'y':'ies' ?></span>
        </div>
    </div>
    <div class="stat-card purple">
        <div class="stat-icon purple"><i class="fa-solid fa-tags"></i></div>
        <div class="stat-info">
            <h3><?= $totalCategories ?></h3>
            <p>Categories</p>
            <span class="stat-change up"><i class="fa-solid fa-tag"></i> Active groups</span>
        </div>
    </div>
    <div class="stat-card gold">
        <div class="stat-icon gold"><i class="fa-solid fa-triangle-exclamation"></i></div>
        <div class="stat-info">
            <h3><?= $lowStockCount ?></h3>
            <p>Low Stock Alerts</p>
            <?php if($lowStockCount > 0): ?>
            <span class="stat-change down"><i class="fa-solid fa-arrow-down"></i> Needs restocking</span>
            <?php else: ?>
            <span class="stat-change up"><i class="fa-solid fa-check"></i> All stocked up</span>
            <?php endif; ?>
        </div>
    </div>
    <div class="stat-card green">
        <div class="stat-icon" style="background:var(--info-bg,#eff6ff);color:var(--info,#3b82f6)"><i class="fa-solid fa-circle-dollar-to-slot"></i></div>
        <div class="stat-info">
            <h3>GH₵<?= number_format($totalStockValue, 0) ?></h3>
            <p>Stock Value (Cost)</p>
            <?php if($outOfStock > 0): ?>
            <span class="stat-change down"><i class="fa-solid fa-box-open"></i> <?= $outOfStock ?> out of stock</span>
            <?php else: ?>
            <span class="stat-change up"><i class="fa-solid fa-circle-check"></i> Fully in stock</span>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Tabs -->
<div class="glass-card-static">
    <div class="tab-nav">
        <button class="tab-btn active" data-tab="tab-products">Products</button>
        <button class="tab-btn" data-tab="tab-categories">Categories</button>
        <button class="tab-btn" data-tab="tab-units">Units</button>
        <button class="tab-btn" data-tab="tab-pricing">Price Management</button>
        <button class="tab-btn" data-tab="tab-stockin">Stock In</button>
        <button class="tab-btn" data-tab="tab-stockout">Stock Out</button>
        <button class="tab-btn" data-tab="tab-adjustments">Adjustments</button>
        <button class="tab-btn" data-tab="tab-lowstock">
            Low Stock <?php if(count($lowStock)>0): ?><span class="badge badge-danger" style="margin-left:4px;font-size:0.7rem;"><?= count($lowStock) ?></span><?php endif; ?>
        </button>
    </div>

    <!-- ====== TAB: PRODUCTS ====== -->
    <div class="tab-content active" id="tab-products">
        <div class="d-flex align-center justify-between mb-16">
            <div class="search-bar">
                <i class="fa-solid fa-magnifying-glass search-icon"></i>
                <input type="text" id="productSearch" placeholder="Search products...">
            </div>
            <div class="d-flex gap-8">
                <select class="form-control" id="categoryFilter" style="width:auto;padding:8px 34px 8px 12px;font-size:0.82rem;">
                    <option value="">All Categories</option>
                    <?php foreach($categories as $cat): ?>
                    <option value="<?= htmlspecialchars($cat['name']) ?>"><?= htmlspecialchars($cat['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="table-container">
            <table class="data-table" id="productsTable">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Product</th>
                        <th>SKU</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                        $i = 1;
                        foreach ($products as $p): ?>
                        <tr data-category="<?= htmlspecialchars($p['category_name'] ?? '') ?>" data-name="<?= htmlspecialchars(strtolower($p['name'])) ?>">
                            <td><?= $i++ ?></td>
                            <td>
                                <div class="d-flex align-center gap-12">
                                    <?php if ($p['image']): ?>
                                        <img src="assets/image/products/<?= htmlspecialchars($p['image']) ?>" alt="" class="prod-thumb">
                                    <?php else: ?>
                                        <div class="prod-thumb-placeholder"><i class="fa-solid fa-box"></i></div>
                                    <?php endif; ?>
                                    <div>
                                        <div class="fw-600"><?= htmlspecialchars($p['name']) ?></div>
                                        <?php if($p['description']): ?><div style="font-size:0.76rem;color:var(--text-muted);"><?= htmlspecialchars(substr($p['description'],0,40)) ?>...</div><?php endif; ?>
                                    </div>
                                </div>
                            </td>
                            <td><?= htmlspecialchars($p['sku']) ?></td>
                            <td><span class="badge badge-teal"><?= htmlspecialchars($p['category_name'] ?: 'Uncategorized') ?></span></td>
                            <td class="fw-600">GH₵<?= number_format($p['selling_price'], 2) ?></td>
                            <td><span class="<?= $p['stock_quantity'] <= $p['min_stock_level'] ? 'text-danger fw-600' : '' ?>"><?= $p['stock_quantity'] ?> <?= htmlspecialchars($p['unit_name'] ?: '') ?></span></td>
                            <td>
                                <?php if ($p['stock_quantity'] <= 0): ?>
                                    <span class="badge badge-danger">Out of Stock</span>
                                <?php elseif ($p['stock_quantity'] <= $p['min_stock_level']): ?>
                                    <span class="badge badge-warning">Low Stock</span>
                                <?php else: ?>
                                    <span class="badge badge-success">Active</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="d-flex gap-6">
                                    <button class="btn btn-sm btn-secondary view-product"
                                        data-id="<?= $p['id'] ?>"
                                        data-name="<?= htmlspecialchars($p['name']) ?>"
                                        data-sku="<?= htmlspecialchars($p['sku']) ?>"
                                        data-category="<?= htmlspecialchars($p['category_name'] ?? 'Uncategorized') ?>"
                                        data-unit="<?= htmlspecialchars($p['unit_name'] ?? '-') ?>"
                                        data-cost="<?= number_format($p['cost_price'],2) ?>"
                                        data-sell="<?= number_format($p['selling_price'],2) ?>"
                                        data-stock="<?= $p['stock_quantity'] ?>"
                                        data-minstock="<?= $p['min_stock_level'] ?>"
                                        data-desc="<?= htmlspecialchars($p['description'] ?? '') ?>"
                                        data-image="<?= htmlspecialchars($p['image'] ?? '') ?>"
                                        title="View">
                                        <i class="fa-solid fa-eye" style="color:var(--info,#3b82f6);"></i>
                                    </button>
                                    <button class="btn btn-sm btn-secondary edit-product" data-id="<?= $p['id'] ?>" title="Edit"><i class="fa-solid fa-pen-to-square" style="color:var(--primary-500);"></i></button>
                                    <button class="btn btn-sm btn-secondary delete-product" data-id="<?= $p['id'] ?>" data-name="<?= htmlspecialchars($p['name']) ?>" title="Delete"><i class="fa-solid fa-trash" style="color:var(--danger);"></i></button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- ====== TAB: CATEGORIES ====== -->
    <div class="tab-content" id="tab-categories">
        <div class="d-flex align-center justify-between mb-16">
            <h3 style="margin:0;">Categories</h3>
            <button class="btn btn-primary btn-sm" id="addCategoryBtn"><i class="fa-solid fa-plus"></i> Add Category</button>
        </div>
        <div class="table-container">
            <table class="data-table">
                <thead><tr><th>#</th><th>Category</th><th>Description</th><th>Products</th><th>Status</th><th>Actions</th></tr></thead>
                <tbody>
                    <?php 
                        $j = 1;
                        foreach($categories as $cat): ?>
                    <tr>
                        <td><?= $j++ ?></td>
                        <td class="fw-600"><?= htmlspecialchars($cat['name']) ?></td>
                        <td style="color:var(--text-muted);font-size:0.85rem;"><?= htmlspecialchars($cat['description'] ?? '-') ?></td>
                        <td><span class="badge badge-teal"><?= (int)$cat['product_count'] ?></span></td>
                        <td><span class="badge badge-<?= $cat['status']==='active'?'success':'warning' ?>"><?= ucfirst($cat['status']) ?></span></td>
                        <td>
                            <div class="d-flex gap-6">
                                <button class="btn btn-sm btn-secondary edit-category" data-id="<?= $cat['id'] ?>" data-name="<?= htmlspecialchars($cat['name']) ?>" data-description="<?= htmlspecialchars($cat['description']??'') ?>" title="Edit"><i class="fa-solid fa-pen-to-square" style="color:var(--primary-500);"></i></button>
                                <button class="btn btn-sm btn-secondary delete-category" data-id="<?= $cat['id'] ?>" data-name="<?= htmlspecialchars($cat['name']) ?>" title="Delete"><i class="fa-solid fa-trash" style="color:var(--danger);"></i></button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- ====== TAB: UNITS ====== -->
    <div class="tab-content" id="tab-units">
        <div class="d-flex align-center justify-between mb-16">
            <h3 style="margin:0;">Units of Measure</h3>
            <button class="btn btn-primary btn-sm" id="addUnitBtn"><i class="fa-solid fa-plus"></i> Add Unit</button>
        </div>
        <div class="table-container">
            <table class="data-table">
                <thead><tr><th>#</th><th>Unit Name</th><th>Short Name</th><th>Actions</th></tr></thead>
                <tbody>
                    <?php 
                        $k = 1;
                        foreach($units as $u): ?>
                    <tr>
                        <td><?= $k++ ?></td>
                        <td class="fw-600"><?= htmlspecialchars($u['name']) ?></td>
                        <td><span class="badge badge-teal"><?= htmlspecialchars($u['short_name'] ?: '-') ?></span></td>
                        <td>
                            <div class="d-flex gap-6">
                                <button class="btn btn-sm btn-secondary edit-unit" data-id="<?= $u['id'] ?>" data-name="<?= htmlspecialchars($u['name']) ?>" data-short="<?= htmlspecialchars($u['short_name']??'') ?>" title="Edit"><i class="fa-solid fa-pen-to-square" style="color:var(--primary-500);"></i></button>
                                <button class="btn btn-sm btn-secondary delete-unit" data-id="<?= $u['id'] ?>" data-name="<?= htmlspecialchars($u['name']) ?>" title="Delete"><i class="fa-solid fa-trash" style="color:var(--danger);"></i></button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- ====== TAB: PRICE MANAGEMENT ====== -->
    <div class="tab-content" id="tab-pricing">
        <div class="d-flex align-center justify-between mb-16">
            <h3 style="margin:0;">Price Management</h3>
        </div>
        <div class="table-container">
            <table class="data-table">
                <thead><tr><th>#</th><th>Product</th><th>Cost Price</th><th>Selling Price</th><th>Margin</th><th>Actions</th></tr></thead>
                <tbody>
                    <?php 
                        $l = 1;
                        foreach($products as $p):
                        $margin = $p['selling_price'] > 0 ? round((($p['selling_price']-$p['cost_price'])/$p['selling_price'])*100,1) : 0;
                    ?>
                    <tr>
                        <td><?= $l++ ?></td>
                        <td>
                            <div class="d-flex align-center gap-10">
                                <?php if($p['image']): ?><img src="assets/image/products/<?= htmlspecialchars($p['image']) ?>" class="prod-thumb" alt=""><?php else: ?><div class="prod-thumb-placeholder"><i class="fa-solid fa-box"></i></div><?php endif; ?>
                                <span class="fw-600"><?= htmlspecialchars($p['name']) ?></span>
                            </div>
                        </td>
                        <td>GH₵<?= number_format($p['cost_price'],2) ?></td>
                        <td class="fw-600">GH₵<?= number_format($p['selling_price'],2) ?></td>
                        <td><span class="badge badge-<?= $margin>=30?'success':($margin>=10?'warning':'danger') ?>"><?= $margin ?>%</span></td>
                        <td>
                            <button class="btn btn-sm btn-secondary edit-price" 
                                data-id="<?= $p['id'] ?>" 
                                data-name="<?= htmlspecialchars($p['name']) ?>" 
                                data-cost="<?= $p['cost_price'] ?>" 
                                data-sell="<?= $p['selling_price'] ?>" title="Edit Price">
                                <i class="fa-solid fa-tag" style="color:var(--primary-500);"></i> Edit Price
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- ====== TAB: STOCK IN ====== -->
    <div class="tab-content" id="tab-stockin">
        <div class="d-flex align-center justify-between mb-16">
            <h3 style="margin:0;">Stock In Records</h3>
            <button class="btn btn-primary btn-sm" id="addStockInBtn"><i class="fa-solid fa-plus"></i> New Stock In</button>
        </div>
        <div class="table-container">
            <table class="data-table">
                <thead><tr><th>#</th><th>Date</th><th>Product</th><th>Qty Added</th><th>Before</th><th>After</th><th>Supplier</th><th>Ref #</th><th>Notes</th></tr></thead>
                <tbody>
                    <?php 
                        $m = 1;
                        foreach($stockIn as $s): ?>
                    <tr>
                        <td><?= $m++ ?></td>
                        <td><?= date('M d, Y', strtotime($s['date'] ?: $s['created_at'])) ?></td>
                        <td class="fw-600"><?= htmlspecialchars($s['product_name']) ?></td>
                        <td><span class="badge badge-success">+<?= $s['qty'] ?></span></td>
                        <td><?= $s['before_qty'] ?></td>
                        <td class="fw-600"><?= $s['after_qty'] ?></td>
                        <td><?= htmlspecialchars($s['supplier'] ?: '-') ?></td>
                        <td><?= htmlspecialchars($s['ref_number'] ?: '-') ?></td>
                        <td style="font-size:0.82rem;color:var(--text-muted);"><?= htmlspecialchars($s['notes'] ?: '-') ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- ====== TAB: STOCK OUT ====== -->
    <div class="tab-content" id="tab-stockout">
        <div class="d-flex align-center justify-between mb-16">
            <h3 style="margin:0;">Stock Out Records</h3>
            <button class="btn btn-primary btn-sm" id="addStockOutBtn"><i class="fa-solid fa-minus"></i> Record Stock Out</button>
        </div>
        <div class="table-container">
            <table class="data-table">
                <thead><tr><th>#</th><th>Date</th><th>Product</th><th>Qty</th><th>Before</th><th>After</th><th>Reason</th><th>Notes</th></tr></thead>
                <tbody>
                    <?php 
                        $n = 1;
                        foreach($stockOut as $s): ?>
                    <tr>
                        <td><?= $n++ ?></td>
                        <td><?= date('M d, Y', strtotime($s['date'] ?: $s['created_at'])) ?></td>
                        <td class="fw-600"><?= htmlspecialchars($s['product_name']) ?></td>
                        <td><span class="badge badge-danger">-<?= $s['qty'] ?></span></td>
                        <td><?= $s['before_qty'] ?></td>
                        <td class="fw-600"><?= $s['after_qty'] ?></td>
                        <td><span class="badge badge-warning"><?= htmlspecialchars($s['reason'] ?: '-') ?></span></td>
                        <td style="font-size:0.82rem;color:var(--text-muted);"><?= htmlspecialchars($s['notes'] ?: '-') ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- ====== TAB: ADJUSTMENTS ====== -->
    <div class="tab-content" id="tab-adjustments">
        <div class="d-flex align-center justify-between mb-16">
            <h3 style="margin:0;">Inventory Adjustments</h3>
            <button class="btn btn-primary btn-sm" id="addAdjBtn"><i class="fa-solid fa-sliders"></i> New Adjustment</button>
        </div>
        <div class="table-container">
            <table class="data-table">
                <thead><tr><th>#</th><th>Date</th><th>Product</th><th>Before</th><th>After</th><th>Difference</th><th>Reason</th><th>Notes</th></tr></thead>
                <tbody>
                    <?php 
                        $o = 1;
                        foreach($adjustments as $s):
                        $diff = $s['after_qty'] - $s['before_qty'];
                    ?>
                    <tr>
                        <td><?= $o++ ?></td>
                        <td><?= date('M d, Y', strtotime($s['date'] ?: $s['created_at'])) ?></td>
                        <td class="fw-600"><?= htmlspecialchars($s['product_name']) ?></td>
                        <td><?= $s['before_qty'] ?></td>
                        <td class="fw-600"><?= $s['after_qty'] ?></td>
                        <td class="fw-600 <?= $diff>=0?'text-success':'text-danger' ?>"><?= $diff>=0?"+$diff":$diff ?></td>
                        <td><?= htmlspecialchars($s['reason'] ?: '-') ?></td>
                        <td style="font-size:0.82rem;color:var(--text-muted);"><?= htmlspecialchars($s['notes'] ?: '-') ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- ====== TAB: LOW STOCK ====== -->
    <div class="tab-content" id="tab-lowstock">
        <div class="d-flex align-center justify-between mb-16">
            <h3 style="margin:0;">Low Stock Items</h3>
            <?php if(count($lowStock)>0): ?>
            <span class="badge badge-danger"><?= count($lowStock) ?> item<?= count($lowStock)>1?'s':'' ?> below threshold</span>
            <?php endif; ?>
        </div>
        <div class="table-container">
            <table class="data-table">
                <thead><tr><th>#</th><th>Product</th><th>SKU</th><th>Current Stock</th><th>Min. Stock</th><th>Status</th><th>Action</th></tr></thead>
                <tbody>
                    <?php 
                        $p_idx = 1;
                        foreach($lowStock as $p):
                        $isCritical = $p['stock_quantity'] <= 0 || $p['stock_quantity'] <= ($p['min_stock_level']/2);
                    ?>
                    <tr>
                        <td><?= $p_idx++ ?></td>
                        <td>
                            <div class="d-flex align-center gap-10">
                                <?php if($p['image']): ?><img src="assets/image/products/<?= htmlspecialchars($p['image']) ?>" class="prod-thumb" alt=""><?php else: ?><div class="prod-thumb-placeholder"><i class="fa-solid fa-box"></i></div><?php endif; ?>
                                <span class="fw-600"><?= htmlspecialchars($p['name']) ?></span>
                            </div>
                        </td>
                        <td><?= htmlspecialchars($p['sku']) ?></td>
                        <td class="<?= $isCritical?'text-danger':'text-warning' ?> fw-600"><?= $p['stock_quantity'] ?></td>
                        <td><?= $p['min_stock_level'] ?></td>
                        <td><span class="badge badge-<?= $isCritical?'danger':'warning' ?>"><?= $isCritical?'Critical':'Low' ?></span></td>
                        <td>
                            <button class="btn btn-sm btn-primary stock-in-quick" data-id="<?= $p['id'] ?>" data-name="<?= htmlspecialchars($p['name']) ?>">
                                <i class="fa-solid fa-arrow-up"></i> Restock
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

<!-- View Product Modal -->
<div class="modal-overlay" id="viewProductModal">
    <div class="modal modal-lg">
        <div class="modal-header">
            <h3 id="viewProductTitle">📦 Product Details</h3>
            <button type="button" class="modal-close">&times;</button>
        </div>
        <div class="modal-body p-24">
            <div class="view-product-layout">
                <!-- Image Column -->
                <div class="view-product-img-col">
                    <div class="view-prod-circle" id="viewProductImgWrap">
                        <i class="fa-solid fa-box" id="viewProductImgIcon"></i>
                    </div>
                    <div class="view-prod-badges" id="viewProductBadges"></div>
                </div>
                <!-- Info Column -->
                <div class="view-product-info-col">
                    <div class="view-detail-grid">
                        <div class="view-detail-item">
                            <span class="vd-label"><i class="fa-solid fa-barcode"></i> SKU</span>
                            <span class="vd-value" id="vd-sku">—</span>
                        </div>
                        <div class="view-detail-item">
                            <span class="vd-label"><i class="fa-solid fa-tag"></i> Category</span>
                            <span class="vd-value" id="vd-category">—</span>
                        </div>
                        <div class="view-detail-item">
                            <span class="vd-label"><i class="fa-solid fa-ruler"></i> Unit</span>
                            <span class="vd-value" id="vd-unit">—</span>
                        </div>
                        <div class="view-detail-item">
                            <span class="vd-label"><i class="fa-solid fa-coins"></i> Cost Price</span>
                            <span class="vd-value fw-600" id="vd-cost">—</span>
                        </div>
                        <div class="view-detail-item">
                            <span class="vd-label"><i class="fa-solid fa-circle-dollar-to-slot"></i> Selling Price</span>
                            <span class="vd-value fw-600 text-teal" id="vd-sell">—</span>
                        </div>
                        <div class="view-detail-item">
                            <span class="vd-label"><i class="fa-solid fa-warehouse"></i> Stock Qty</span>
                            <span class="vd-value fw-600" id="vd-stock">—</span>
                        </div>
                        <div class="view-detail-item">
                            <span class="vd-label"><i class="fa-solid fa-bell"></i> Min. Stock Level</span>
                            <span class="vd-value" id="vd-minstock">—</span>
                        </div>
                        <div class="view-detail-item" style="grid-column:1/-1;">
                            <span class="vd-label"><i class="fa-solid fa-align-left"></i> Description</span>
                            <span class="vd-value" id="vd-desc" style="white-space:pre-wrap;">—</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary modal-cancel">Close</button>
            <button type="button" class="btn btn-primary" id="viewToEditBtn"><i class="fa-solid fa-pen-to-square"></i> Edit Product</button>
        </div>
    </div>
</div>

<!-- Add/Edit Product Modal -->
<div class="modal-overlay" id="addProductModal">
    <div class="modal modal-lg">
        <form id="productForm" enctype="multipart/form-data">
            <input type="hidden" name="id" value="0">
            <input type="hidden" name="action" value="save_product">
            <div class="modal-header">
                <h3 id="productModalTitle">📦 Add Product</h3>
                <button type="button" class="modal-close">&times;</button>
            </div>
            <div class="modal-body p-24">
                <div class="product-form-grid">
                    <div class="d-flex flex-column gap-20">
                        <div class="floating-group">
                            <input type="text" name="name" class="floating-control" required placeholder=" ">
                            <label class="floating-label">Product Name</label>
                        </div>
                        <div class="floating-group">
                            <input type="text" name="sku" class="floating-control" placeholder=" ">
                            <label class="floating-label">SKU / Item Code</label>
                        </div>
                        <div class="d-grid gap-12" style="grid-template-columns:1fr 1fr;">
                            <div class="floating-group">
                                <select name="category_id" id="categorySelect" class="floating-control">
                                    <option value="">Select Category</option>
                                    <option value="new">+ Add New Category</option>
                                </select>
                                <label class="floating-label">Category</label>
                            </div>
                            <div class="floating-group">
                                <select name="unit_id" id="unitSelect" class="floating-control">
                                    <option value="">Select Unit</option>
                                    <option value="new">+ Add New Unit</option>
                                </select>
                                <label class="floating-label">Unit of Measure</label>
                            </div>
                        </div>
                        <div id="quickAddCategory" class="quick-add-box" style="display:none;">
                            <div class="d-flex gap-8">
                                <input type="text" id="newCategoryName" class="form-control" placeholder="New category name">
                                <button type="button" class="btn btn-primary btn-sm" onclick="saveQuickCategory()"><i class="fa-solid fa-plus"></i></button>
                            </div>
                        </div>
                        <div id="quickAddUnit" class="quick-add-box" style="display:none;">
                            <div class="d-flex gap-8">
                                <input type="text" id="newUnitName" class="form-control" placeholder="Unit name (e.g. Box)">
                                <button type="button" class="btn btn-primary btn-sm" onclick="saveQuickUnit()"><i class="fa-solid fa-plus"></i></button>
                            </div>
                        </div>
                        <div class="floating-group">
                            <textarea name="description" class="floating-control" style="height:90px;" placeholder=" "></textarea>
                            <label class="floating-label">Description (Optional)</label>
                        </div>
                    </div>
                    <div class="d-flex flex-column gap-20">
                        <div class="image-upload-wrapper">
                            <div class="image-preview" id="productImagePreview" onclick="$('#productImageInput').click()">
                                <i class="fa-solid fa-camera mb-8"></i>
                                <span>Upload Product Image</span>
                            </div>
                            <input type="file" name="image" id="productImageInput" accept="image/*" style="display:none;">
                        </div>
                        <div class="d-grid gap-12" style="grid-template-columns:1fr 1fr;">
                            <div class="floating-group">
                                <input type="number" step="0.01" name="cost_price" class="floating-control" required placeholder=" ">
                                <label class="floating-label">Cost Price (₱)</label>
                            </div>
                            <div class="floating-group">
                                <input type="number" step="0.01" name="selling_price" class="floating-control" required placeholder=" ">
                                <label class="floating-label">Selling Price (₱)</label>
                            </div>
                        </div>
                        <div class="d-grid gap-12" style="grid-template-columns:1fr 1fr;">
                            <div class="floating-group">
                                <input type="number" name="stock_quantity" class="floating-control" required placeholder=" ">
                                <label class="floating-label">Initial Stock</label>
                            </div>
                            <div class="floating-group">
                                <input type="number" name="min_stock_level" class="floating-control" value="5" placeholder=" ">
                                <label class="floating-label">Low Stock Alert</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary modal-cancel">Cancel</button>
                <button type="submit" class="btn btn-primary" id="saveProductBtn"><i class="fa-solid fa-cloud-arrow-up"></i> Save Product</button>
            </div>
        </form>
    </div>
</div>

<!-- Category Modal -->
<div class="modal-overlay" id="categoryModal">
    <div class="modal">
        <form id="categoryForm">
            <input type="hidden" name="id" value="0">
            <input type="hidden" name="action" value="save_category">
            <div class="modal-header">
                <h3 id="categoryModalTitle">🗂️ Add Category</h3>
                <button type="button" class="modal-close">&times;</button>
            </div>
            <div class="modal-body p-24">
                <div class="d-flex flex-column gap-16">
                    <div class="floating-group">
                        <input type="text" name="name" class="floating-control" required placeholder=" ">
                        <label class="floating-label">Category Name</label>
                    </div>
                    <div class="floating-group">
                        <textarea name="description" class="floating-control" style="height:80px;" placeholder=" "></textarea>
                        <label class="floating-label">Description (Optional)</label>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary modal-cancel">Cancel</button>
                <button type="submit" class="btn btn-primary" id="saveCategoryBtn"><i class="fa-solid fa-save"></i> Save Category</button>
            </div>
        </form>
    </div>
</div>

<!-- Unit Modal -->
<div class="modal-overlay" id="unitModal">
    <div class="modal">
        <form id="unitForm">
            <input type="hidden" name="id" value="0">
            <input type="hidden" name="action" value="save_unit">
            <div class="modal-header">
                <h3 id="unitModalTitle">📐 Add Unit</h3>
                <button type="button" class="modal-close">&times;</button>
            </div>
            <div class="modal-body p-24">
                <div class="d-flex flex-column gap-16">
                    <div class="floating-group">
                        <input type="text" name="name" class="floating-control" required placeholder=" ">
                        <label class="floating-label">Unit Name (e.g. Piece)</label>
                    </div>
                    <div class="floating-group">
                        <input type="text" name="short_name" class="floating-control" placeholder=" ">
                        <label class="floating-label">Short Name (e.g. pc)</label>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary modal-cancel">Cancel</button>
                <button type="submit" class="btn btn-primary" id="saveUnitBtn"><i class="fa-solid fa-save"></i> Save Unit</button>
            </div>
        </form>
    </div>
</div>

<!-- Price Edit Modal -->
<div class="modal-overlay" id="priceModal">
    <div class="modal">
        <form id="priceForm">
            <input type="hidden" name="id" value="0">
            <input type="hidden" name="action" value="update_price">
            <div class="modal-header">
                <h3>🏷️ Edit Price — <span id="priceProductName"></span></h3>
                <button type="button" class="modal-close">&times;</button>
            </div>
            <div class="modal-body p-24">
                <div class="d-flex flex-column gap-16">
                    <div class="floating-group">
                        <input type="number" step="0.01" name="cost_price" class="floating-control" required placeholder=" ">
                        <label class="floating-label">Cost Price (GH₵)</label>
                    </div>
                    <div class="floating-group">
                        <input type="number" step="0.01" name="selling_price" class="floating-control" required placeholder=" ">
                        <label class="floating-label">Selling Price (GH₵)</label>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary modal-cancel">Cancel</button>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save"></i> Update Price</button>
            </div>
        </form>
    </div>
</div>

<!-- Stock In Modal -->
<div class="modal-overlay" id="stockInModal">
    <div class="modal">
        <form id="stockInForm">
            <input type="hidden" name="action" value="stock_in">
            <div class="modal-header">
                <h3>📥 Stock In</h3>
                <button type="button" class="modal-close">&times;</button>
            </div>
            <div class="modal-body p-24">
                <div class="d-flex flex-column gap-16">
                    <div class="floating-group">
                        <select name="product_id" id="siProductSelect" class="floating-control" required>
                            <option value="">Select Product</option>
                            <?php foreach($products as $p): ?>
                            <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['name']) ?> (Stock: <?= $p['stock_quantity'] ?>)</option>
                            <?php endforeach; ?>
                        </select>
                        <label class="floating-label">Product</label>
                    </div>
                    <div class="d-grid gap-12" style="grid-template-columns:1fr 1fr;">
                        <div class="floating-group">
                            <input type="number" name="qty" class="floating-control" required min="1" placeholder=" ">
                            <label class="floating-label">Quantity Added</label>
                        </div>
                        <div class="floating-group">
                            <input type="date" name="date" class="floating-control has-value" value="<?= date('Y-m-d') ?>">
                            <label class="floating-label">Date</label>
                        </div>
                    </div>
                    <div class="floating-group">
                        <input type="text" name="supplier" class="floating-control" placeholder=" ">
                        <label class="floating-label">Supplier (Optional)</label>
                    </div>
                    <div class="floating-group">
                        <input type="text" name="ref_number" class="floating-control" placeholder=" ">
                        <label class="floating-label">Reference # (Optional)</label>
                    </div>
                    <div class="floating-group">
                        <textarea name="notes" class="floating-control" style="height:70px;" placeholder=" "></textarea>
                        <label class="floating-label">Notes (Optional)</label>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary modal-cancel">Cancel</button>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-arrow-up"></i> Record Stock In</button>
            </div>
        </form>
    </div>
</div>

<!-- Stock Out Modal -->
<div class="modal-overlay" id="stockOutModal">
    <div class="modal">
        <form id="stockOutForm">
            <input type="hidden" name="action" value="stock_out">
            <div class="modal-header">
                <h3>📤 Stock Out</h3>
                <button type="button" class="modal-close">&times;</button>
            </div>
            <div class="modal-body p-24">
                <div class="d-flex flex-column gap-16">
                    <div class="floating-group">
                        <select name="product_id" class="floating-control" required>
                            <option value="">Select Product</option>
                            <?php foreach($products as $p): ?>
                            <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['name']) ?> (Stock: <?= $p['stock_quantity'] ?>)</option>
                            <?php endforeach; ?>
                        </select>
                        <label class="floating-label">Product</label>
                    </div>
                    <div class="d-grid gap-12" style="grid-template-columns:1fr 1fr;">
                        <div class="floating-group">
                            <input type="number" name="qty" class="floating-control" required min="1" placeholder=" ">
                            <label class="floating-label">Quantity</label>
                        </div>
                        <div class="floating-group">
                            <input type="date" name="date" class="floating-control has-value" value="<?= date('Y-m-d') ?>">
                            <label class="floating-label">Date</label>
                        </div>
                    </div>
                    <div class="floating-group">
                        <select name="reason" class="floating-control" required>
                            <option value="">Select Reason</option>
                            <option>Damaged</option>
                            <option>Expired</option>
                            <option>Sample</option>
                            <option>Lost</option>
                            <option>Returned to Supplier</option>
                            <option>Other</option>
                        </select>
                        <label class="floating-label">Reason</label>
                    </div>
                    <div class="floating-group">
                        <textarea name="notes" class="floating-control" style="height:70px;" placeholder=" "></textarea>
                        <label class="floating-label">Notes (Optional)</label>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary modal-cancel">Cancel</button>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-arrow-down"></i> Record Stock Out</button>
            </div>
        </form>
    </div>
</div>

<!-- Adjustment Modal -->
<div class="modal-overlay" id="adjModal">
    <div class="modal">
        <form id="adjForm">
            <input type="hidden" name="action" value="adjust_stock">
            <div class="modal-header">
                <h3>⚖️ Stock Adjustment</h3>
                <button type="button" class="modal-close">&times;</button>
            </div>
            <div class="modal-body p-24">
                <div class="d-flex flex-column gap-16">
                    <div class="floating-group">
                        <select name="product_id" class="floating-control" required>
                            <option value="">Select Product</option>
                            <?php foreach($products as $p): ?>
                            <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['name']) ?> (Stock: <?= $p['stock_quantity'] ?>)</option>
                            <?php endforeach; ?>
                        </select>
                        <label class="floating-label">Product</label>
                    </div>
                    <div class="d-grid gap-12" style="grid-template-columns:1fr 1fr;">
                        <div class="floating-group">
                            <input type="number" name="new_qty" class="floating-control" required min="0" placeholder=" ">
                            <label class="floating-label">New Actual Quantity</label>
                        </div>
                        <div class="floating-group">
                            <input type="date" name="date" class="floating-control has-value" value="<?= date('Y-m-d') ?>">
                            <label class="floating-label">Date</label>
                        </div>
                    </div>
                    <div class="floating-group">
                        <input type="text" name="reason" class="floating-control" placeholder=" ">
                        <label class="floating-label">Reason (e.g. Physical count)</label>
                    </div>
                    <div class="floating-group">
                        <textarea name="notes" class="floating-control" style="height:70px;" placeholder=" "></textarea>
                        <label class="floating-label">Notes (Optional)</label>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary modal-cancel">Cancel</button>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-sliders"></i> Save Adjustment</button>
            </div>
        </form>
    </div>
</div>

<!-- ==================== STYLES ==================== -->
<style>
/* --- Product Form --- */
.product-form-grid { display:grid; grid-template-columns:1.2fr 0.8fr; gap:24px; align-items:start; }
.image-upload-wrapper { width:100%; }
.image-preview {
    width:100%; aspect-ratio:1/1;
    background:rgba(26,138,124,0.03);
    border:2px dashed rgba(26,138,124,0.15);
    border-radius:50%; display:flex; flex-direction:column;
    align-items:center; justify-content:center; cursor:pointer;
    overflow:hidden; color:var(--text-muted); transition:all 0.2s;
}
.image-preview:hover { border-color:var(--primary-400); background:rgba(26,138,124,0.06); color:var(--primary-600); }
.image-preview img { width:100%; height:100%; object-fit:cover; border-radius:50%; }
.quick-add-box { background:var(--bg-light); padding:12px; border-radius:8px; margin-top:-4px; border:1px solid var(--border-light); animation:slideInDown 0.2s ease; }
@keyframes slideInDown { from{opacity:0;transform:translateY(-10px)} to{opacity:1;transform:translateY(0)} }

/* --- Circular Product Thumbnails --- */
.prod-thumb {
    width:42px; height:42px;
    border-radius:50%;
    object-fit:cover;
    border:2px solid var(--primary-100);
    box-shadow:0 2px 8px rgba(26,138,124,0.12);
    flex-shrink:0;
    transition:transform 0.2s;
}
.prod-thumb:hover { transform:scale(1.08); }
.prod-thumb-placeholder {
    width:42px; height:42px;
    border-radius:50%;
    background:linear-gradient(135deg,var(--primary-50),var(--primary-100));
    border:2px solid var(--primary-100);
    display:flex; align-items:center; justify-content:center;
    color:var(--primary-400); font-size:0.95rem; flex-shrink:0;
    font-weight:700; font-family:'Outfit',sans-serif;
}

/* --- View Product Modal --- */
.view-product-layout { display:grid; grid-template-columns:180px 1fr; gap:28px; align-items:start; }
.view-product-img-col { display:flex; flex-direction:column; align-items:center; gap:14px; }
.view-prod-circle {
    width:150px; height:150px;
    border-radius:50%;
    background:linear-gradient(135deg,var(--primary-50),var(--primary-100));
    border:3px solid var(--primary-200);
    box-shadow:0 6px 24px rgba(26,138,124,0.18);
    display:flex; align-items:center; justify-content:center;
    overflow:hidden; font-size:2.8rem; color:var(--primary-400);
    font-weight:800; font-family:'Outfit',sans-serif;
    flex-shrink:0;
}
.view-prod-circle img { width:100%; height:100%; object-fit:cover; }
.view-prod-badges { display:flex; flex-direction:column; align-items:center; gap:6px; }
.view-detail-grid { display:grid; grid-template-columns:1fr 1fr; gap:14px; }
.view-detail-item { display:flex; flex-direction:column; gap:4px; padding:12px 14px; background:rgba(26,138,124,0.03); border-radius:10px; border:1px solid rgba(26,138,124,0.07); }
.vd-label { font-size:0.72rem; font-weight:600; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.5px; display:flex; align-items:center; gap:5px; }
.vd-value { font-size:0.9rem; font-weight:500; color:var(--text-primary); }
.text-teal { color:var(--primary-500) !important; }

/* --- Stats Grid for inventory --- */
.inv-stats-grid { grid-template-columns:repeat(4,1fr); }
@media(max-width:1100px){ .inv-stats-grid{ grid-template-columns:repeat(2,1fr); } }
@media(max-width:600px){ .inv-stats-grid{ grid-template-columns:1fr; } }
@media(max-width:850px){ .product-form-grid{ grid-template-columns:1fr; } }
@media(max-width:640px){ .view-product-layout{ grid-template-columns:1fr; } .view-prod-circle{ margin:0 auto; } }
</style>

<!-- ==================== SCRIPTS ==================== -->
<script>
$(document).ready(function() {

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

    initDataTable('#productsTable');
    // For other tabs, we will initialize them when clicked to avoid rendering issues with hidden tabs
    let initializedTabs = { 'tab-products': true };

    // Floating label: selects
    function checkSelect(sel) { sel.val() ? sel.addClass('has-value') : sel.removeClass('has-value'); }
    $('select.floating-control').each(function(){ checkSelect($(this)); }).on('change', function(){ checkSelect($(this)); });

    // ---- TAB PERSISTENCE ----
    const ACTIVE_TAB_KEY = 'vendora_products_active_tab';

    function activateTab(tabId) {
        $('.tab-btn').removeClass('active');
        $('.tab-content').removeClass('active');
        $(`.tab-btn[data-tab="${tabId}"]`).addClass('active');
        $(`#${tabId}`).addClass('active');
        localStorage.setItem(ACTIVE_TAB_KEY, tabId);
        
        // Initialize DataTables for the active tab if not already done
        if (!initializedTabs[tabId]) {
            if (tabId === 'tab-categories') initDataTable('#tab-categories .data-table');
            if (tabId === 'tab-units') initDataTable('#tab-units .data-table');
            if (tabId === 'tab-pricing') initDataTable('#tab-pricing .data-table');
            if (tabId === 'tab-stockin') initDataTable('#tab-stockin .data-table');
            if (tabId === 'tab-stockout') initDataTable('#tab-stockout .data-table');
            if (tabId === 'tab-adjustments') initDataTable('#tab-adjustments .data-table');
            if (tabId === 'tab-lowstock') initDataTable('#tab-lowstock .data-table');
            initializedTabs[tabId] = true;
        }
    }

    // Restore tab on page load
    const savedTab = localStorage.getItem(ACTIVE_TAB_KEY);
    if (savedTab && $(`#${savedTab}`).length) {
        activateTab(savedTab);
    }

    // Override tab-btn clicks to also persist
    $('.tab-btn').on('click', function() {
        activateTab($(this).data('tab'));
    });

    // Helper: reload and stay on current tab
    function reloadOnTab() {
        localStorage.setItem(ACTIVE_TAB_KEY, $('.tab-btn.active').data('tab') || 'tab-products');
        location.reload();
    }

    // Generic modal open helpers
    function openModal(id) { $('#'+id).addClass('active'); }
    function closeModal(id) { $('#'+id).removeClass('active'); }

    $('.modal-close, .modal-cancel').on('click', function(){ $(this).closest('.modal-overlay').removeClass('active'); });

    // ---- VIEW PRODUCT ----
    var _viewProductId = 0;
    $(document).on('click', '.view-product', function(){
        const t = $(this);
        _viewProductId = t.data('id');
        const name  = t.data('name');
        const image = t.data('image');
        const stock = parseInt(t.data('stock'));
        const minStock = parseInt(t.data('minstock'));

        $('#viewProductTitle').text('📦 ' + name);
        // Image
        if(image){
            $('#viewProductImgWrap').html(`<img src="assets/image/products/${image}" alt="${name}">`);
        } else {
            const initials = name.split(' ').map(w=>w[0]).join('').substr(0,2).toUpperCase();
            $('#viewProductImgWrap').html(`<span>${initials}</span>`);
        }
        // Badges
        let badgeHtml = '';
        if(stock <= 0) badgeHtml = '<span class="badge badge-danger">Out of Stock</span>';
        else if(stock <= minStock) badgeHtml = '<span class="badge badge-warning">Low Stock</span>';
        else badgeHtml = '<span class="badge badge-success">In Stock</span>';
        $('#viewProductBadges').html(badgeHtml);

        // Details
        $('#vd-sku').text(t.data('sku') || '—');
        $('#vd-category').html(`<span class="badge badge-teal">${t.data('category')}</span>`);
        $('#vd-unit').text(t.data('unit') || '—');
        $('#vd-cost').text('GH₵' + t.data('cost'));
        $('#vd-sell').text('GH₵' + t.data('sell'));
        $('#vd-stock').html(`<span class="${stock<=0?'text-danger':stock<=minStock?'text-warning':''} fw-600">${stock}</span>`);
        $('#vd-minstock').text(minStock);
        $('#vd-desc').text(t.data('desc') || 'No description provided.');

        openModal('viewProductModal');
    });

    // "Edit Product" from view modal
    $('#viewToEditBtn').on('click', function(){
        closeModal('viewProductModal');
        $(`.edit-product[data-id="${_viewProductId}"]`).trigger('click');
    });

    // ---- PRODUCT ----
    function loadProductDropdowns() {
        $.post('controllers/ProductController.php', {action:'get_categories'}, function(r){
            if(r.status==='success'){
                const s = $('#categorySelect');
                s.find('option:not([value=""],[value="new"])').remove();
                r.data.forEach(c => s.prepend(`<option value="${c.id}">${c.name}</option>`));
                checkSelect(s);
            }
        },'json');
        $.post('controllers/ProductController.php', {action:'get_units'}, function(r){
            if(r.status==='success'){
                const s = $('#unitSelect');
                s.find('option:not([value=""],[value="new"])').remove();
                r.data.forEach(u => s.prepend(`<option value="${u.id}">${u.name}</option>`));
                checkSelect(s);
            }
        },'json');
    }

    $('[data-modal="addProductModal"]').on('click', function(){
        $('#productForm')[0].reset();
        $('#productForm input[name="id"]').val('0');
        $('#productModalTitle').text('📦 Add Product');
        $('#productImagePreview').html('<i class="fa-solid fa-camera mb-8"></i><span>Upload Product Image</span>');
        $('#quickAddCategory,#quickAddUnit').hide();
        loadProductDropdowns();
        openModal('addProductModal');
    });

    // Edit Product
    $(document).on('click','.edit-product', function(){
        const id = $(this).data('id');
        $.post('controllers/ProductController.php',{action:'get_product',id:id}, function(r){
            if(r.status==='success'){
                const d = r.data;
                const f = $('#productForm')[0];
                f.reset();
                $('#productForm input[name="id"]').val(d.id);
                $('#productModalTitle').text('✏️ Edit Product');
                $('input[name="name"]',f).val(d.name).trigger('input');
                $('input[name="sku"]',f).val(d.sku).trigger('input');
                $('input[name="cost_price"]',f).val(d.cost_price).trigger('input');
                $('input[name="selling_price"]',f).val(d.selling_price).trigger('input');
                $('input[name="stock_quantity"]',f).val(d.stock_quantity).trigger('input');
                $('input[name="min_stock_level"]',f).val(d.min_stock_level).trigger('input');
                $('textarea[name="description"]',f).val(d.description).trigger('input');
                if(d.image){
                    $('#productImagePreview').html(`<img src="assets/image/products/${d.image}" alt="Preview">`);
                } else {
                    $('#productImagePreview').html('<i class="fa-solid fa-camera mb-8"></i><span>Upload Product Image</span>');
                }
                loadProductDropdowns();
                setTimeout(function(){
                    if(d.category_id){ $('#categorySelect').val(d.category_id); checkSelect($('#categorySelect')); }
                    if(d.unit_id){ $('#unitSelect').val(d.unit_id); checkSelect($('#unitSelect')); }
                },400);
                openModal('addProductModal');
            }
        },'json');
    });

    // Delete Product
    $(document).on('click','.delete-product', function(){
        const id=$(this).data('id'), name=$(this).data('name');
        Swal.fire({title:'Delete Product?',text:`"${name}" will be permanently deleted.`,icon:'warning',showCancelButton:true,confirmButtonColor:'#d33',confirmButtonText:'Yes, Delete'})
        .then(r=>{ if(r.isConfirmed){ $.post('controllers/ProductController.php',{action:'delete_product',id:id},function(res){ if(res.status==='success') Swal.fire('Deleted!',res.message,'success').then(()=>reloadOnTab()); else Swal.fire('Error',res.message,'error'); },'json'); }});
    });

    // Quick-add toggles
    $('#categorySelect').on('change', function(){ $(this).val()==='new' ? $('#quickAddCategory').slideDown() : $('#quickAddCategory').slideUp(); });
    $('#unitSelect').on('change', function(){ $(this).val()==='new' ? $('#quickAddUnit').slideDown() : $('#quickAddUnit').slideUp(); });

    // Image preview
    $('#productImageInput').on('change', function(){
        const f=this.files[0]; if(f){ const r=new FileReader(); r.onload=e=>$('#productImagePreview').html(`<img src="${e.target.result}" alt="Preview">`); r.readAsDataURL(f); }
    });

    // Save Product
    $('#productForm').on('submit', function(e){
        e.preventDefault();
        const btn=$('#saveProductBtn'), orig=btn.html();
        btn.html('<i class="fas fa-spinner fa-spin"></i> Saving...').prop('disabled',true);
        $.ajax({url:'controllers/ProductController.php',type:'POST',data:new FormData(this),contentType:false,processData:false,dataType:'json',
            success:function(r){ if(r.status==='success') Swal.fire('Success',r.message,'success').then(()=>reloadOnTab()); else{ Swal.fire('Error',r.message,'error'); btn.html(orig).prop('disabled',false); }},
            error:function(){ Swal.fire('Error','Server communication failed.','error'); btn.html(orig).prop('disabled',false); }
        });
    });

    // ---- CATEGORIES ----
    $('#addCategoryBtn').on('click', function(){
        $('#categoryForm')[0].reset();
        $('#categoryForm input[name="id"]').val('0');
        $('#categoryModalTitle').text('🗂️ Add Category');
        openModal('categoryModal');
    });
    $(document).on('click','.edit-category', function(){
        const t=$(this);
        $('#categoryForm input[name="id"]').val(t.data('id'));
        $('#categoryForm input[name="name"]').val(t.data('name')).trigger('input');
        $('#categoryForm textarea[name="description"]').val(t.data('description')).trigger('input');
        $('#categoryModalTitle').text('✏️ Edit Category');
        openModal('categoryModal');
    });
    $(document).on('click','.delete-category', function(){
        const id=$(this).data('id'), name=$(this).data('name');
        Swal.fire({title:'Delete Category?',text:`"${name}" will be deleted.`,icon:'warning',showCancelButton:true,confirmButtonColor:'#d33',confirmButtonText:'Delete'})
        .then(r=>{ if(r.isConfirmed){ $.post('controllers/ProductController.php',{action:'delete_category',id:id},function(res){ if(res.status==='success') Swal.fire('Deleted!',res.message,'success').then(()=>reloadOnTab()); else Swal.fire('Error',res.message,'error'); },'json'); }});
    });
    $('#categoryForm').on('submit', function(e){
        e.preventDefault();
        const btn=$('#saveCategoryBtn'), orig=btn.html();
        btn.html('<i class="fas fa-spinner fa-spin"></i>').prop('disabled',true);
        $.post('controllers/ProductController.php', $(this).serialize(), function(r){
            if(r.status==='success') Swal.fire('Saved!',r.message,'success').then(()=>reloadOnTab());
            else{ Swal.fire('Error',r.message,'error'); btn.html(orig).prop('disabled',false); }
        },'json');
    });

    // ---- UNITS ----
    $('#addUnitBtn').on('click', function(){
        $('#unitForm')[0].reset();
        $('#unitForm input[name="id"]').val('0');
        $('#unitModalTitle').text('📐 Add Unit');
        openModal('unitModal');
    });
    $(document).on('click','.edit-unit', function(){
        const t=$(this);
        $('#unitForm input[name="id"]').val(t.data('id'));
        $('#unitForm input[name="name"]').val(t.data('name')).trigger('input');
        $('#unitForm input[name="short_name"]').val(t.data('short')).trigger('input');
        $('#unitModalTitle').text('✏️ Edit Unit');
        openModal('unitModal');
    });
    $(document).on('click','.delete-unit', function(){
        const id=$(this).data('id'), name=$(this).data('name');
        Swal.fire({title:'Delete Unit?',text:`"${name}" will be deleted.`,icon:'warning',showCancelButton:true,confirmButtonColor:'#d33',confirmButtonText:'Delete'})
        .then(r=>{ if(r.isConfirmed){ $.post('controllers/ProductController.php',{action:'delete_unit',id:id},function(res){ if(res.status==='success') Swal.fire('Deleted!',res.message,'success').then(()=>reloadOnTab()); else Swal.fire('Error',res.message,'error'); },'json'); }});
    });
    $('#unitForm').on('submit', function(e){
        e.preventDefault();
        const btn=$('#saveUnitBtn'), orig=btn.html();
        btn.html('<i class="fas fa-spinner fa-spin"></i>').prop('disabled',true);
        $.post('controllers/ProductController.php', $(this).serialize(), function(r){
            if(r.status==='success') Swal.fire('Saved!',r.message,'success').then(()=>reloadOnTab());
            else{ Swal.fire('Error',r.message,'error'); btn.html(orig).prop('disabled',false); }
        },'json');
    });

    // ---- PRICE MANAGEMENT ----
    $(document).on('click','.edit-price', function(){
        const t=$(this);
        $('#priceForm input[name="id"]').val(t.data('id'));
        $('#priceForm input[name="cost_price"]').val(t.data('cost')).trigger('input');
        $('#priceForm input[name="selling_price"]').val(t.data('sell')).trigger('input');
        $('#priceProductName').text(t.data('name'));
        openModal('priceModal');
    });
    $('#priceForm').on('submit', function(e){
        e.preventDefault();
        $.post('controllers/ProductController.php', $(this).serialize(), function(r){
            if(r.status==='success') Swal.fire('Updated!',r.message,'success').then(()=>reloadOnTab());
            else Swal.fire('Error',r.message,'error');
        },'json');
    });

    // ---- STOCK IN ----
    $('#addStockInBtn').on('click', function(){ $('#stockInForm')[0].reset(); $('#siProductSelect').val(''); checkSelect($('#siProductSelect')); openModal('stockInModal'); });
    $(document).on('click','.stock-in-quick', function(){
        $('#siProductSelect').val($(this).data('id')); checkSelect($('#siProductSelect'));
        openModal('stockInModal');
    });
    $('#stockInForm').on('submit', function(e){
        e.preventDefault();
        $.post('controllers/ProductController.php', $(this).serialize(), function(r){
            if(r.status==='success') Swal.fire('Done!',r.message,'success').then(()=>reloadOnTab());
            else Swal.fire('Error',r.message,'error');
        },'json');
    });

    // ---- STOCK OUT ----
    $('#addStockOutBtn').on('click', function(){ $('#stockOutForm')[0].reset(); openModal('stockOutModal'); });
    $('#stockOutForm').on('submit', function(e){
        e.preventDefault();
        $.post('controllers/ProductController.php', $(this).serialize(), function(r){
            if(r.status==='success') Swal.fire('Done!',r.message,'success').then(()=>reloadOnTab());
            else Swal.fire('Error',r.message,'error');
        },'json');
    });

    // ---- ADJUSTMENT ----
    $('#addAdjBtn').on('click', function(){ $('#adjForm')[0].reset(); openModal('adjModal'); });
    $('#adjForm').on('submit', function(e){
        e.preventDefault();
        $.post('controllers/ProductController.php', $(this).serialize(), function(r){
            if(r.status==='success') Swal.fire('Done!',r.message,'success').then(()=>reloadOnTab());
            else Swal.fire('Error',r.message,'error');
        },'json');
    });

    // ---- SEARCH & FILTER (DataTables Override) ----
    function filterProducts(){
        const q=$('#productSearch').val();
        const cat=$('#categoryFilter').val();
        
        const table = $('#productsTable').DataTable();
        table.search(q).draw();

        $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
            if (settings.nTable.id !== 'productsTable') return true;
            if (!cat) return true;
            
            // Category is in column 2 (index 2)
            const catCol = $(table.cell(dataIndex, 2).node()).text().trim();
            return catCol === cat;
        });
        table.draw();
        $.fn.dataTable.ext.search.pop();
    }
    $('#productSearch').on('keyup', function() {
        $('#productsTable').DataTable().search($(this).val()).draw();
    });
    $('#categoryFilter').on('change', filterProducts);
});

// Quick-add Category from product modal
function saveQuickCategory() {
    const name = $('#newCategoryName').val();
    if(!name) return Swal.fire('Error','Please enter a category name.','error');
    $.post('controllers/ProductController.php',{action:'save_category',name:name},function(r){
        if(r.status==='success'){
            $('#categorySelect').prepend(`<option value="${r.id}" selected>${r.name}</option>`).trigger('change');
            $('#quickAddCategory').slideUp(); $('#newCategoryName').val('');
        } else Swal.fire('Error',r.message,'error');
    },'json');
}

// Quick-add Unit from product modal
function saveQuickUnit() {
    const name = $('#newUnitName').val();
    if(!name) return Swal.fire('Error','Please enter a unit name.','error');
    $.post('controllers/ProductController.php',{action:'save_unit',name:name},function(r){
        if(r.status==='success'){
            $('#unitSelect').prepend(`<option value="${r.id}" selected>${r.name}</option>`).trigger('change');
            $('#quickAddUnit').slideUp(); $('#newUnitName').val('');
        } else Swal.fire('Error',r.message,'error');
    },'json');
}
</script>
