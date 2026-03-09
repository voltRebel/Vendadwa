<!-- Top Bar -->
<div class="top-bar">
    <div class="top-bar-left">
        <button class="mobile-menu-toggle" id="mobileMenuToggle"><i class="fa-solid fa-bars"></i></button>
        <h1>Help & System Info</h1>
    </div>
</div>

<div class="content-grid">
    <!-- About System -->
    <div class="glass-card">
        <div class="text-center mb-24">
            <div class="mb-16 d-flex justify-center">
                <div style="width:80px;height:80px;border-radius:20px;background:linear-gradient(135deg, var(--primary-100), var(--primary-50));display:flex;align-items:center;justify-content:center;">
                    <img src="assets/image/logo.png" alt="Vendora Logo" style="height: 56px; object-fit: contain;">
                </div>
            </div>
            <h2 style="font-size:1.75rem;margin-bottom:4px;font-weight:800;letter-spacing:-0.5px;">Vendora</h2>
            <p class="text-muted" style="font-size:0.9rem;font-weight:500;">Point of Sale & Inventory System</p>
        </div>

        <div class="divider"></div>

        <div class="mb-20">
            <div class="d-flex justify-between align-center mb-12">
                <span class="text-muted fw-500" style="font-size:0.85rem;">Version</span>
                <span class="badge badge-teal fw-700">1.0.0</span>
            </div>
            <div class="d-flex justify-between align-center mb-12">
                <span class="text-muted fw-500" style="font-size:0.85rem;">Deployment</span>
                <span class="fw-600 text-primary">Web / Cloud</span>
            </div>
            <div class="d-flex justify-between align-center mb-12">
                <span class="text-muted fw-500" style="font-size:0.85rem;">Host</span>
                <span class="fw-600 text-primary">Hostinger</span>
            </div>
            <div class="d-flex justify-between align-center">
                <span class="text-muted fw-500" style="font-size:0.85rem;">License</span>
                <span class="badge badge-blue">Proprietary</span>
            </div>
        </div>

        <div class="divider"></div>

        <div class="text-center">
            <p class="text-muted" style="font-size:0.8rem;">Developed & Maintained by</p>
            <p class="fw-700 text-teal mt-4" style="font-size:0.95rem;">Alsoft Solutions</p>
            <p class="text-muted mt-8" style="font-size:0.75rem;">&copy; <?= date('Y') ?> Vendora. All rights reserved.</p>
        </div>
    </div>

    <!-- Quick Help Accordion -->
    <div class="glass-card">
        <div class="d-flex align-center gap-10 mb-20">
            <div class="stat-icon pink" style="width:36px;height:36px;font-size:0.9rem;background:var(--primary-50);color:var(--primary-500);">
                <i class="fa-solid fa-lightbulb"></i>
            </div>
            <h3 style="font-size:1.15rem;font-weight:700;">Quick Help Guide</h3>
        </div>

        <div class="help-accordion">
            <div class="settings-section mb-12" style="box-shadow:none;border:1px solid var(--border-light);background:rgba(255,255,255,0.4);">
                <div class="settings-section-header p-16">
                    <h3 style="font-size:0.9rem;font-weight:600;"><i class="fa-solid fa-cash-register mr-8 text-teal"></i> How to make a sale?</h3>
                    <i class="fa-solid fa-plus toggle-icon" style="font-size:0.8rem;"></i>
                </div>
                <div class="settings-section-body p-16 pt-0">
                    <div class="divider mt-0 mb-12"></div>
                    <ul style="font-size:0.85rem;color:var(--text-secondary);padding-left:18px;line-height:1.7;">
                        <li>Navigate to <strong>Point of Sale</strong> in the sidebar.</li>
                        <li>Search for products or select from the category grid.</li>
                        <li>Items appear in the cart on the right.</li>
                        <li>Click <strong>Checkout</strong>, enter payment, and finalize.</li>
                    </ul>
                </div>
            </div>

            <div class="settings-section mb-12" style="box-shadow:none;border:1px solid var(--border-light);background:rgba(255,255,255,0.4);">
                <div class="settings-section-header p-16">
                    <h3 style="font-size:0.9rem;font-weight:600;"><i class="fa-solid fa-boxes-stacked mr-8 text-teal"></i> How to add stock?</h3>
                    <i class="fa-solid fa-plus toggle-icon" style="font-size:0.8rem;"></i>
                </div>
                <div class="settings-section-body p-16 pt-0">
                    <div class="divider mt-0 mb-12"></div>
                    <ul style="font-size:0.85rem;color:var(--text-secondary);padding-left:18px;line-height:1.7;">
                        <li>Open <strong>Products</strong> and click the <strong>Stock In</strong> tab.</li>
                        <li>Click the <strong>Add Stock</strong> button.</li>
                        <li>Search for the product and specify quantity.</li>
                        <li>Save to update inventory immediately.</li>
                    </ul>
                </div>
            </div>

            <div class="settings-section mb-12" style="box-shadow:none;border:1px solid var(--border-light);background:rgba(255,255,255,0.4);">
                <div class="settings-section-header p-16">
                    <h3 style="font-size:0.9rem;font-weight:600;"><i class="fa-solid fa-rotate-left mr-8 text-teal"></i> How to process a return?</h3>
                    <i class="fa-solid fa-plus toggle-icon" style="font-size:0.8rem;"></i>
                </div>
                <div class="settings-section-body p-16 pt-0">
                    <div class="divider mt-0 mb-12"></div>
                    <ul style="font-size:0.85rem;color:var(--text-secondary);padding-left:18px;line-height:1.7;">
                        <li>Go to <strong>Returns & Refunds</strong> module.</li>
                        <li>Enter the <strong>Receipt ID</strong> to find the sale.</li>
                        <li>Select the items and quantity to return.</li>
                        <li>Confirm refund and stock will be restored automatically.</li>
                    </ul>
                </div>
            </div>

            <div class="settings-section mb-0" style="box-shadow:none;border:1px solid var(--border-light);background:rgba(255,255,255,0.4);">
                <div class="settings-section-header p-16">
                    <h3 style="font-size:0.9rem;font-weight:600;"><i class="fa-solid fa-chart-line mr-8 text-teal"></i> How to view reports?</h3>
                    <i class="fa-solid fa-plus toggle-icon" style="font-size:0.8rem;"></i>
                </div>
                <div class="settings-section-body p-16 pt-0">
                    <div class="divider mt-0 mb-12"></div>
                    <ul style="font-size:0.85rem;color:var(--text-secondary);padding-left:18px;line-height:1.7;">
                        <li>Navigate to the <strong>Reports</strong> section.</li>
                        <li>Choose a category (Sales, Inventory, Profit).</li>
                        <li>Filter by date and export to PDF/Excel if needed.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="content-grid mt-24">
    <!-- Recent Updates -->
    <div class="glass-card">
        <div class="d-flex align-center gap-10 mb-20">
            <div class="stat-icon purple" style="width:36px;height:36px;font-size:0.9rem;background:var(--secondary-50);color:var(--secondary-500);">
                <i class="fa-solid fa-clock-rotate-left"></i>
            </div>
            <h3 style="font-size:1.15rem;font-weight:700;">Recent Updates</h3>
        </div>
        
        <div class="mb-12">
            <div class="d-flex gap-16 mb-16">
                <div class="fw-800 text-teal" style="font-size:0.8rem; width:80px; flex-shrink:0;">MAR 2026</div>
                <div>
                    <div class="fw-700 mb-4" style="font-size:0.85rem;">v1.0.0 — Official Release</div>
                    <p class="text-muted" style="font-size:0.75rem; line-height:1.5;">Complete rebranding to Alsoft Solutions, improved system tools, and enhanced help documentation.</p>
                </div>
            </div>
            <div class="d-flex gap-16 mb-16">
                <div class="fw-800 text-muted" style="font-size:0.8rem; width:80px; flex-shrink:0;">FEB 2026</div>
                <div>
                    <div class="fw-700 mb-4" style="font-size:0.85rem;">Beta Phase</div>
                    <p class="text-muted" style="font-size:0.75rem; line-height:1.5;">Implemented multi-tenancy architecture and core POS functionalities with real-time reporting.</p>
                </div>
            </div>
            <div class="d-flex gap-16">
                <div class="fw-800 text-muted" style="font-size:0.8rem; width:80px; flex-shrink:0;">JAN 2026</div>
                <div>
                    <div class="fw-700 mb-4" style="font-size:0.85rem;">Initial Concept</div>
                    <p class="text-muted" style="font-size:0.75rem; line-height:1.5;">Development of Vendora engine and database schema by Alsoft Solutions.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- System Status -->
    <div class="glass-card">
        <div class="d-flex align-center gap-10 mb-20">
            <div class="stat-icon green" style="width:36px;height:36px;font-size:0.9rem;background:var(--success-bg);color:var(--success);">
                <i class="fa-solid fa-heart-pulse"></i>
            </div>
            <h3 style="font-size:1.15rem;font-weight:700;">System Health</h3>
        </div>
        
        <div class="mb-20">
            <?php
            // Dynamic Health Checks
            $dbStatus = 'ONLINE';
            $dbClass = 'success';
            try {
                if (!$pdo) { $dbStatus = 'OFFLINE'; $dbClass = 'danger'; }
            } catch (Exception $e) { $dbStatus = 'ERROR'; $dbClass = 'danger'; }

            $storageStatus = 'HEALTHY';
            $storageClass = 'info';
            // Check if backup directory is writable
            if (!is_writable('backups/')) {
                $storageStatus = 'RESTRICTED';
                $storageClass = 'warning';
            }
            ?>

            <div class="p-12 glass-card-static mb-12" style="background:rgba(255,255,255,0.3); border-radius:12px; border:1px solid var(--border-light);">
                <div class="d-flex justify-between align-center">
                    <div class="d-flex align-center gap-12">
                        <div style="width:32px;height:32px;border-radius:8px;background:var(--success-bg);display:flex;align-items:center;justify-content:center;">
                            <i class="fa-solid fa-database text-success" style="font-size:0.9rem;"></i>
                        </div>
                        <div>
                            <div class="fw-700" style="font-size:0.85rem;">Database Engine</div>
                            <div class="text-muted" style="font-size:0.7rem;">MySQL / MariaDB</div>
                        </div>
                    </div>
                    <span class="badge badge-<?= $dbClass ?> fw-700" style="font-size:0.65rem;"><?= $dbStatus ?></span>
                </div>
            </div>

            <div class="p-12 glass-card-static mb-12" style="background:rgba(255,255,255,0.3); border-radius:12px; border:1px solid var(--border-light);">
                <div class="d-flex justify-between align-center">
                    <div class="d-flex align-center gap-12">
                        <div style="width:32px;height:32px;border-radius:8px;background:var(--info-bg);display:flex;align-items:center;justify-content:center;">
                            <i class="fa-solid fa-folder-open text-info" style="font-size:0.9rem;"></i>
                        </div>
                        <div>
                            <div class="fw-700" style="font-size:0.85rem;">File Storage</div>
                            <div class="text-muted" style="font-size:0.7rem;"><?= $storageStatus === 'HEALTHY' ? 'Writable / Accessible' : 'Permission Error' ?></div>
                        </div>
                    </div>
                    <span class="badge badge-<?= $storageClass ?> fw-700" style="font-size:0.65rem;"><?= $storageStatus ?></span>
                </div>
            </div>

            <div class="p-12 glass-card-static" style="background:rgba(255,255,255,0.3); border-radius:12px; border:1px solid var(--border-light);">
                <div class="d-flex justify-between align-center">
                    <div class="d-flex align-center gap-12">
                        <div style="width:32px;height:32px;border-radius:8px;background:var(--primary-50);display:flex;align-items:center;justify-content:center;">
                            <i class="fa-solid fa-wand-magic-sparkles text-teal" style="font-size:0.9rem;"></i>
                        </div>
                        <div>
                            <div class="fw-700" style="font-size:0.85rem;">Theme Engine</div>
                            <div class="text-muted" style="font-size:0.7rem;">Glassmorphism v2</div>
                        </div>
                    </div>
                    <span class="badge badge-teal fw-700" style="font-size:0.65rem;">OPTIMIZED</span>
                </div>
            </div>
        </div>

        <div class="divider"></div>
        
        <div class="p-16 glass-card-static" style="background:var(--primary-50); border-radius:12px; border:1px solid var(--primary-100);">
            <div class="d-flex gap-16 align-start">
                <div class="stat-icon teal" style="width:44px;height:44px;background:var(--white);color:var(--primary-500);box-shadow:0 4px 12px rgba(26,138,124,0.1);flex-shrink:0;">
                    <i class="fa-solid fa-headset" style="font-size:1.2rem;"></i>
                </div>
                <div class="flex-1">
                    <div class="fw-800 text-primary mb-12" style="font-size:1rem;letter-spacing:-0.3px;">Technical Support</div>
                    
                    <div class="mb-12">
                        <div class="text-muted fw-600 mb-2" style="font-size:0.7rem;text-transform:uppercase;letter-spacing:0.5px;">On-call Developer</div>
                        <div class="fw-700 text-primary" style="font-size:0.95rem;">Alsoft Solutions</div>
                    </div>

                    <div class="d-grid" style="grid-template-columns:1fr 1fr; gap:16px;">
                        <div>
                            <div class="text-muted fw-600 mb-2" style="font-size:0.75rem;">Phone</div>
                            <a href="tel:0247049461" class="btn btn-sm btn-outline w-100 justify-start" style="padding:8px 12px; font-size:0.8rem; background:white;">
                                <i class="fa-solid fa-phone mr-4" style="font-size:0.7rem;"></i> 0247049461
                            </a>
                        </div>
                        <div>
                            <div class="text-muted fw-600 mb-2" style="font-size:0.75rem;">Email</div>
                            <a href="mailto:michaelkorblyjunior97@gmail.com" class="btn btn-sm btn-outline w-100 justify-start" style="padding:8px 12px; font-size:0.8rem; background:white;">
                                <i class="fa-solid fa-envelope mr-4" style="font-size:0.7rem;"></i> Email Support
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Help Accordion Logic
    $('.help-accordion .settings-section-header').click(function() {
        const section = $(this).closest('.settings-section');
        const body = section.find('.settings-section-body');
        const icon = $(this).find('.toggle-icon');
        
        // Toggle this section
        body.slideToggle(300);
        section.toggleClass('active-help');
        
        // Change icon
        if (icon.hasClass('fa-plus')) {
            icon.removeClass('fa-plus').addClass('fa-minus');
        } else {
            icon.removeClass('fa-minus').addClass('fa-plus');
        }
    });

    // Close others logic (optional, but keep it simple for now as per current view)
    // For now, let's just make sure the icons start correctly.
});
</script>

<style>
.active-help {
    border-color: var(--primary-300) !important;
    background: rgba(255, 255, 255, 0.7) !important;
}
.help-accordion .settings-section-body {
    display: none;
}
kbd {
    font-family: 'Inter', sans-serif;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}
.text-pink { color: var(--primary-500) !important; } /* Fallback for existing references */
.badge-pink { background: var(--primary-50) !important; color: var(--primary-500) !important; }
</style>
