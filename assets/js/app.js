/**
 * Vendora — POS System
 * Main Application JavaScript
 */

document.addEventListener('DOMContentLoaded', function () {

    // ── Sidebar Toggle (Mobile) ──
    const sidebar = document.getElementById('sidebar');
    const backdrop = document.getElementById('sidebarBackdrop');
    const menuToggle = document.getElementById('mobileMenuToggle');

    function openSidebar() {
        if (sidebar) sidebar.classList.add('open');
        if (backdrop) backdrop.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function closeSidebar() {
        if (sidebar) sidebar.classList.remove('open');
        if (backdrop) backdrop.classList.remove('active');
        document.body.style.overflow = '';
    }

    if (menuToggle) {
        menuToggle.addEventListener('click', openSidebar);
    }

    if (backdrop) {
        backdrop.addEventListener('click', closeSidebar);
    }

    // ── Tab Switching ──
    document.querySelectorAll('.tab-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const tabGroup = this.closest('.tab-nav');
            const target = this.getAttribute('data-tab');
            const container = tabGroup.parentElement;

            // Deactivate all tabs in this group
            tabGroup.querySelectorAll('.tab-btn').forEach(function (b) {
                b.classList.remove('active');
            });

            // Deactivate all content panes
            container.querySelectorAll('.tab-content').forEach(function (tc) {
                tc.classList.remove('active');
            });

            // Activate clicked tab
            this.classList.add('active');

            // Activate target content
            var targetEl = document.getElementById(target);
            if (targetEl) {
                targetEl.classList.add('active');
            }
        });
    });

    // ── Modal Controls ──
    document.querySelectorAll('[data-modal]').forEach(function (trigger) {
        trigger.addEventListener('click', function () {
            var modalId = this.getAttribute('data-modal');
            var modal = document.getElementById(modalId);
            if (modal) modal.classList.add('active');
        });
    });

    document.querySelectorAll('.modal-close, .modal-cancel').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var overlay = this.closest('.modal-overlay');
            if (overlay) overlay.classList.remove('active');
        });
    });

    document.querySelectorAll('.modal-overlay').forEach(function (overlay) {
        overlay.addEventListener('click', function (e) {
            if (e.target === this) {
                this.classList.remove('active');
            }
        });
    });

    // ── Settings Accordion ──
    document.querySelectorAll('.settings-section-header').forEach(function (header) {
        header.addEventListener('click', function () {
            this.classList.toggle('open');
            var body = this.nextElementSibling;
            if (body) body.classList.toggle('open');
        });
    });

    // ── POS: Cart Quantity Controls ──
    document.querySelectorAll('.cart-item-qty button').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var qtySpan = this.parentElement.querySelector('span');
            if (!qtySpan) return;
            var qty = parseInt(qtySpan.textContent);

            if (this.classList.contains('qty-plus')) {
                qtySpan.textContent = qty + 1;
            } else if (this.classList.contains('qty-minus') && qty > 1) {
                qtySpan.textContent = qty - 1;
            }
        });
    });

    // ── Search Input Focus Effect ──
    document.querySelectorAll('.search-bar input').forEach(function (input) {
        input.addEventListener('focus', function () {
            this.parentElement.style.transform = 'translateY(-1px)';
        });
        input.addEventListener('blur', function () {
            this.parentElement.style.transform = '';
        });
    });

    // ── Notification Pulse (demo) ──
    var notifBtns = document.querySelectorAll('.btn-icon.has-badge');
    notifBtns.forEach(function (btn) {
        setInterval(function () {
            btn.style.transform = 'scale(1.05)';
            setTimeout(function () {
                btn.style.transform = '';
            }, 200);
        }, 5000);
    });

    // ── Auto-dismiss alerts ──
    document.querySelectorAll('.alert.auto-dismiss').forEach(function (alert) {
        setTimeout(function () {
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-10px)';
            setTimeout(function () {
                alert.remove();
            }, 300);
        }, 4000);
    });
});
