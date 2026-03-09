<!-- Top Bar -->
<div class="top-bar">
    <div class="top-bar-left">
        <button class="mobile-menu-toggle" id="mobileMenuToggle">
            <i class="fa-solid fa-bars"></i>
        </button>
        <h1>Point of Sale</h1>
    </div>
    <div class="top-bar-right">
        <button class="btn btn-sm btn-secondary" id="holdSaleBtn">
            <i class="fa-solid fa-pause"></i> Hold Sale
        </button>
        <button class="btn btn-sm btn-outline" id="resumeSaleBtn">
            <i class="fa-solid fa-play"></i> Resume
        </button>
    </div>
</div>

<!-- POS Layout -->
<div class="pos-layout">
    <!-- Left: Product Search & Grid -->
    <div class="pos-products">
        <!-- Search & Filter Bar -->
        <div class="glass-card-static" style="padding: 14px 18px;">
            <div class="d-flex gap-12 align-center">
                <div class="search-bar flex-1" style="max-width:100%;">
                    <i class="fa-solid fa-magnifying-glass search-icon"></i>
                    <input type="text" id="productSearch" placeholder="Search products or scan barcode...">
                </div>
                <select class="form-control" id="categoryFilter" style="width:auto; padding:9px 34px 9px 12px; font-size:0.83rem;">
                    <option value="0">All Categories</option>
                </select>
            </div>
        </div>

        <!-- Products Grid -->
        <div class="pos-products-grid" id="productGrid">
            <!-- Products will be loaded here dynamically -->
            <div class="loading-state" style="grid-column: 1/-1; text-align: center; padding: 40px; color: var(--text-muted);">
                <i class="fa-solid fa-spinner fa-spin fa-2x"></i>
                <p style="margin-top: 10px;">Loading products...</p>
            </div>
        </div>
    </div>

    <!-- Right: Cart Panel -->
    <div class="pos-cart">
        <div class="pos-cart-header">
            <h3><i class="fa-solid fa-cart-shopping text-pink"></i> &nbsp;Current Sale</h3>
            <span class="badge badge-pink" id="cartBadge">0 items</span>
        </div>

        <!-- Cart Items -->
        <div class="pos-cart-items" id="cartItems">
            <div class="empty-cart-msg" style="text-align: center; padding: 40px; color: var(--text-muted); opacity: 0.6;">
                <i class="fa-solid fa-cart-arrow-down fa-3x" style="margin-bottom: 15px; display: block;"></i>
                <p>Your cart is empty</p>
            </div>
        </div>

        <!-- Cart Summary -->
        <div class="pos-cart-summary">
            <div class="cart-summary-row">
                <span>Subtotal</span>
                <span class="fw-600" id="summarySubtotal"><span class="currency-symbol">GH₵</span>0.00</span>
            </div>
            <div class="cart-summary-row" id="taxRow">
                <span>Tax</span>
                <span class="fw-600" id="summaryTax"><span class="currency-symbol">GH₵</span>0.00</span>
            </div>
            <div class="cart-summary-row">
                <div class="d-flex align-center gap-8">
                    <span>Discount</span>
                    <button class="btn btn-icon-sm" id="editDiscountBtn" title="Edit Discount"><i class="fa-solid fa-pen-to-square" style="font-size: 0.75rem;"></i></button>
                </div>
                <span class="fw-600 text-success" id="summaryDiscount">-<span class="currency-symbol">GH₵</span>0.00</span>
            </div>
            <div class="cart-summary-row total">
                <span>Total</span>
                <span id="summaryTotal"><span class="currency-symbol">GH₵</span>0.00</span>
            </div>

            <div class="d-flex gap-8">
                <button class="btn btn-secondary flex-1" style="padding:12px;" id="cancelSaleBtn">
                    <i class="fa-solid fa-xmark"></i> Cancel
                </button>
                <button class="btn btn-primary flex-1" style="padding:12px;" id="payBtn">
                    <i class="fa-solid fa-credit-card"></i> Pay
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Checkout Modal -->
<div class="modal-overlay" id="checkoutModal">
    <div class="modal" style="max-width: 480px; padding: 0; overflow: hidden;">
        <div class="modal-header" style="background: var(--primary-50); padding: 24px; border-bottom: 1px solid var(--primary-100);">
            <div class="d-flex align-center justify-between w-100">
                <div>
                    <div style="font-size: 0.75rem; color: var(--primary-500); font-weight: 700; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 4px;">Processing Payment</div>
                    <h3 id="checkoutTitle" style="font-size: 1.5rem; color: var(--primary-700);">GH₵0.00</h3>
                </div>
                <button class="modal-close" style="background: #fff; width: 32px; height: 32px; border-radius: 50%; border: 1px solid var(--primary-100); display: flex; align-items: center; justify-content: center; color: var(--text-muted);">&times;</button>
            </div>
        </div>

        <div class="modal-body" style="padding: 24px;">
            <div class="form-group">
                <label class="form-label" style="font-weight: 600; color: var(--text-main); margin-bottom: 12px; display: block;">Payment Method</label>
                <div class="d-flex gap-8" id="pmGroup">
                    <button class="btn pm-btn active flex-1" data-pm="Cash" style="padding: 14px; border-radius: 12px; display: flex; flex-direction: column; align-items: center; gap: 8px;">
                        <i class="fa-solid fa-money-bill" style="font-size: 1.25rem;"></i>
                        <span style="font-size: 0.8rem; font-weight: 600;">Cash</span>
                    </button>
                    <button class="btn btn-outline pm-btn flex-1" data-pm="Card" style="padding: 14px; border-radius: 12px; display: flex; flex-direction: column; align-items: center; gap: 8px;">
                        <i class="fa-solid fa-credit-card" style="font-size: 1.25rem;"></i>
                        <span style="font-size: 0.8rem; font-weight: 600;">Card</span>
                    </button>
                    <button class="btn btn-outline pm-btn flex-1" data-pm="Mobile Money" style="padding: 14px; border-radius: 12px; display: flex; flex-direction: column; align-items: center; gap: 8px;">
                        <i class="fa-solid fa-mobile" style="font-size: 1.25rem;"></i>
                        <span style="font-size: 0.8rem; font-weight: 600;">Mobile</span>
                    </button>
                </div>
            </div>

            <div class="form-group" style="margin-top: 24px;">
                <label class="form-label" style="font-weight: 600; color: var(--text-main); margin-bottom: 8px; display: block;">Amount Received</label>
                <div style="position: relative;">
                    <span style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: var(--text-muted); font-weight: 600;" class="currency-symbol">GH₵</span>
                    <input type="number" step="0.01" class="form-control" id="amountReceived" placeholder="0.00" style="padding: 16px 16px 16px 50px; font-size: 1.25rem; font-weight: 700; border-radius: 12px; border: 2px solid var(--primary-100);">
                </div>
            </div>

            <div style="background: #f0faf8; border: 1px solid #d1edeb; border-radius: 16px; padding: 20px; text-align: center; margin: 24px 0; display: flex; justify-content: space-between; align-items: center;">
                <div style="text-align: left;">
                    <div style="font-size: 0.7rem; color: #14645a; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">Customer Change</div>
                    <div style="font-size: 1.5rem; font-weight: 800; color: #14645a;" id="changeDisplay"><span class="currency-symbol">GH₵</span>0.00</div>
                </div>
                <div style="width: 48px; height: 48px; background: #fff; border-radius: 12px; display: flex; align-items: center; justify-content: center; color: #14645a;">
                    <i class="fa-solid fa-hand-holding-dollar fa-lg"></i>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" style="font-weight: 600; color: var(--text-main); margin-bottom: 8px; display: block;">Link to Customer</label>
                <div class="d-flex gap-8 align-center">
                    <select class="form-control" id="customerSelect" style="padding: 12px; border-radius: 10px;">
                        <option value="0">Walk-in Customer</option>
                    </select>
                    <button class="btn btn-secondary" id="quickAddCustomerBtn" style="padding: 12px 20px; white-space: nowrap; border-radius: 10px;">
                        <i class="fa-solid fa-plus"></i> New
                    </button>
                </div>
            </div>
        </div>

        <div class="modal-footer" style="padding: 24px; background: #fff; border-top: 1px solid var(--border); display: flex; gap: 12px;">
            <button class="btn btn-secondary modal-cancel" style="padding: 14px 24px; font-weight: 600; flex: 1; border-radius: 12px;">Cancel</button>
            <button class="btn btn-primary" id="completeSaleBtn" style="padding: 14px 24px; font-weight: 700; flex: 2; border-radius: 12px; background: #14645a; box-shadow: 0 4px 15px rgba(20, 100, 90, 0.2);">
                <i class="fa-solid fa-check-double"></i> Complete Sale & Print
            </button>
        </div>
    </div>
</div>

<!-- Quick Add Customer Modal -->
<div class="modal-overlay" id="quickCustomerModal">
    <div class="modal" style="max-width: 400px;">
        <div class="modal-header">
            <h3>👤 Quick Add Customer</h3>
            <button class="modal-close">&times;</button>
        </div>
        <div class="modal-body" style="padding: 20px;">
            <div class="form-group">
                <label class="form-label">Customer Name</label>
                <input type="text" class="form-control" id="quickCustName" placeholder="e.g. Ama Serwaa">
            </div>
            <div class="form-group">
                <label class="form-label">Phone Number</label>
                <input type="text" class="form-control" id="quickCustPhone" placeholder="e.g. 0244000000">
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary modal-cancel">Cancel</button>
            <button class="btn btn-primary" id="saveQuickCustomerBtn">
                <i class="fa-solid fa-check"></i> Save Customer
            </button>
        </div>
    </div>
</div>

<!-- Scripts for POS functionality -->
<script>
$(document).ready(function() {
    let allProducts = [];
    let categories = [];
    let customers = [];
    let cart = [];
    let selectedPM = 'Cash';
    let currentDiscount = 0;
    let settings = {
        currency_symbol: 'GH₵',
        tax_rate: 0,
        tax_enabled: 0,
        tax_included: 0
    };

    // 1. Initial Data Fetch
    const loadPosData = () => {
        $.post('controllers/POSController.php', { action: 'get_pos_data' }, function(response) {
            if (response.status === 'success') {
                allProducts = response.data.products;
                categories = response.data.categories;
                customers = response.data.customers;
                settings = response.data.settings;

                // Update UI with currency symbol
                $('.currency-symbol').text(settings.currency_symbol);
                renderCategories();
                renderProducts();
                renderCustomers();
                updateCart(); // Trigger initial totals render
            } else {
                Swal.fire('Error', response.message, 'error');
            }
        }, 'json');
    };

    const renderCategories = () => {
        const sel = $('#categoryFilter');
        categories.forEach(c => {
            sel.append(`<option value="${c.id}">${c.name}</option>`);
        });
    };

    const renderCustomers = () => {
        const sel = $('#customerSelect');
        customers.forEach(c => {
            sel.append(`<option value="${c.id}">${c.name} (${c.phone || 'N/A'})</option>`);
        });

        // Ensure settings are available
        const s = settings || { payment_cash: 1, payment_card: 1, payment_mobile: 1, payment_bank: 0 };

        // Also render enabled payment methods
        let pmHtml = '';
        if (s.payment_cash != 0) pmHtml += `<button class="btn pm-btn active flex-1" data-pm="Cash" style="padding: 14px; border-radius: 12px; display: flex; flex-direction: column; align-items: center; gap: 8px;"><i class="fa-solid fa-money-bill" style="font-size: 1.25rem;"></i><span style="font-size: 0.8rem; font-weight: 600;">Cash</span></button>`;
        if (s.payment_card == 1) pmHtml += `<button class="btn btn-outline pm-btn flex-1" data-pm="Card" style="padding: 14px; border-radius: 12px; display: flex; flex-direction: column; align-items: center; gap: 8px;"><i class="fa-solid fa-credit-card" style="font-size: 1.25rem;"></i><span style="font-size: 0.8rem; font-weight: 600;">Card</span></button>`;
        if (s.payment_mobile == 1) pmHtml += `<button class="btn btn-outline pm-btn flex-1" data-pm="Mobile Money" style="padding: 14px; border-radius: 12px; display: flex; flex-direction: column; align-items: center; gap: 8px;"><i class="fa-solid fa-mobile" style="font-size: 1.25rem;"></i><span style="font-size: 0.8rem; font-weight: 600;">Mobile</span></button>`;
        if (s.payment_bank == 1) pmHtml += `<button class="btn btn-outline pm-btn flex-1" data-pm="Bank Transfer" style="padding: 14px; border-radius: 12px; display: flex; flex-direction: column; align-items: center; gap: 8px;"><i class="fa-solid fa-building-columns" style="font-size: 1.25rem;"></i><span style="font-size: 0.8rem; font-weight: 600;">Bank</span></button>`;
        
        if (pmHtml) $('#pmGroup').html(pmHtml);
        
        // Initial active PM
        selectedPM = $('#pmGroup .pm-btn.active').data('pm') || 'Cash';
    };

    const renderProducts = (productId = null) => {
        const grid = $('#productGrid');
        const q = $('#productSearch').val().toLowerCase();
        const cid = $('#categoryFilter').val();

        let filtered = allProducts.filter(p => {
            const matchQ = !q || p.name.toLowerCase().includes(q) || (p.sku && p.sku.toLowerCase().includes(q));
            const matchC = cid == 0 || p.category_id == cid;
            return matchQ && matchC;
        });

        if (filtered.length === 0) {
            grid.html('<div style="grid-column: 1/-1; text-align: center; padding: 40px; color: var(--text-muted);">No products found.</div>');
            return;
        }

        let html = '';
        filtered.forEach(p => {
            const outOfStock = p.stock_quantity <= 0;
            html += `
                <div class="pos-product-card ${outOfStock ? 'out-of-stock' : ''}" data-id="${p.id}">
                    <div class="product-img">
                        ${p.image ? `<img src="assets/image/products/${p.image}">` : '📦'}
                    </div>
                    <div class="product-name">${p.name}</div>
                    <div class="product-price">${settings.currency_symbol}${parseFloat(p.selling_price).toFixed(2)}</div>
                    <div class="product-stock ${p.stock_quantity <= p.min_stock_level ? 'text-danger' : ''}">In stock: ${p.stock_quantity}</div>
                </div>
            `;
        });
        grid.html(html);
    };

    // 2. Cart Functionality
    const updateCart = () => {
        const container = $('#cartItems');
        if (cart.length === 0) {
            container.html(`
                <div class="empty-cart-msg" style="text-align: center; padding: 40px; color: var(--text-muted); opacity: 0.6;">
                    <i class="fa-solid fa-cart-arrow-down fa-3x" style="margin-bottom: 15px; display: block;"></i>
                    <p>Your cart is empty</p>
                </div>
            `);
            $('#cartBadge').text('0 items');
            calculateTotals();
            return;
        }

        let html = '';
        cart.forEach((item, index) => {
            html += `
                <div class="cart-item">
                    <div class="cart-item-info">
                        <div class="cart-item-name">${item.name}</div>
                        <div class="cart-item-price">${settings.currency_symbol}${parseFloat(item.price).toFixed(2)} each</div>
                    </div>
                    <div class="cart-item-qty">
                        <button class="qty-minus" data-index="${index}">−</button>
                        <span>${item.qty}</span>
                        <button class="qty-plus" data-index="${index}">+</button>
                    </div>
                    <div class="cart-item-total">${settings.currency_symbol}${(item.qty * item.price).toFixed(2)}</div>
                    <button class="cart-item-remove" data-index="${index}" title="Remove"><i class="fa-solid fa-xmark"></i></button>
                </div>
            `;
        });
        container.html(html);
        $('#cartBadge').text(`${cart.reduce((a, b) => a + b.qty, 0)} items`);
        calculateTotals();
    };

    const calculateTotals = () => {
        const s = settings || { currency_symbol: 'GH₵', tax_enabled: 0, tax_rate: 0 };
        const subtotal = cart.reduce((a, b) => a + (b.qty * b.price), 0);
        let tax = 0;
        if (s.tax_enabled == 1) {
            tax = (subtotal - currentDiscount) * (parseFloat(s.tax_rate) / 100);
        }
        const total = (subtotal - currentDiscount) + tax;

        $('#summarySubtotal').text(s.currency_symbol + subtotal.toFixed(2));
        $('#summaryDiscount').text('-' + s.currency_symbol + currentDiscount.toFixed(2));
        $('#summaryTax').text(s.currency_symbol + tax.toFixed(2));
        $('#summaryTotal').text(s.currency_symbol + (total > 0 ? total : 0).toFixed(2));
        $('#checkoutTitle').text(s.currency_symbol + (total > 0 ? total : 0).toFixed(2));
        
        $('.currency-symbol').text(s.currency_symbol);
        updateChange();
    };

    const updateChange = () => {
        const subtotal = cart.reduce((a, b) => a + (b.qty * b.price), 0);
        let tax = 0;
        if (settings.tax_enabled == 1) {
            tax = (subtotal - currentDiscount) * (parseFloat(settings.tax_rate) / 100);
        }
        const total = (subtotal - currentDiscount) + tax;
        const received = parseFloat($('#amountReceived').val()) || 0;
        const change = received - total;
        $('#changeDisplay').text(settings.currency_symbol + (change > 0 ? change : 0).toFixed(2));
    };

    // 3. Event Handlers
    $('#productSearch').on('input', renderProducts);
    $('#categoryFilter').on('change', renderProducts);

    $(document).on('click', '.pos-product-card', function() {
        const id = $(this).data('id');
        const p = allProducts.find(x => x.id == id);
        
        if (p.stock_quantity <= 0) {
            toast('Product out of stock!', 'error');
            return;
        }

        const existing = cart.find(x => x.id == id);
        if (existing) {
            if (existing.qty >= p.stock_quantity) {
                toast('Cannot exceed available stock!', 'error');
                return;
            }
            existing.qty++;
        } else {
            cart.push({
                id: p.id,
                name: p.name,
                price: parseFloat(p.selling_price),
                qty: 1,
                max_stock: p.stock_quantity
            });
        }
        updateCart();
    });

    $(document).on('click', '.qty-plus', function() {
        const idx = $(this).data('index');
        if (cart[idx].qty >= cart[idx].max_stock) {
            toast('Cannot exceed available stock!', 'error');
            return;
        }
        cart[idx].qty++;
        updateCart();
    });

    $(document).on('click', '.qty-minus', function() {
        const idx = $(this).data('index');
        if (cart[idx].qty > 1) {
            cart[idx].qty--;
            updateCart();
        }
    });

    $(document).on('click', '.cart-item-remove', function() {
        const idx = $(this).data('index');
        cart.splice(idx, 1);
        updateCart();
    });

    $('#cancelSaleBtn').on('click', function() {
        if (cart.length === 0) return;
        Swal.fire({
            title: 'Cancel Sale?',
            text: 'Are you sure you want to clear the cart?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, Clear'
        }).then(res => {
            if (res.isConfirmed) {
                cart = [];
                updateCart();
            }
        });
    });

    $('#payBtn').on('click', function() {
        if (cart.length === 0) {
            toast('Cart is empty!', 'error');
            return;
        }
        const subtotal = cart.reduce((a, b) => a + (b.qty * b.price), 0);
        const total = subtotal - currentDiscount;
        $('#amountReceived').val((total > 0 ? total : 0).toFixed(2));
        updateChange();
        $('#checkoutModal').addClass('active');
    });

    $('#editDiscountBtn').on('click', function() {
        Swal.fire({
            title: 'Apply Discount',
            input: 'number',
            inputLabel: 'Amount (GH₵)',
            inputValue: currentDiscount,
            showCancelButton: true,
            inputValidator: (value) => {
                if (!value || value < 0) return 'Please enter a valid amount';
            }
        }).then((result) => {
            if (result.isConfirmed) {
                currentDiscount = parseFloat(result.value);
                calculateTotals();
                toast('Discount updated');
            }
        });
    });

    $('#quickAddCustomerBtn').on('click', function() {
        $('#quickCustName').val('');
        $('#quickCustPhone').val('');
        $('#quickCustomerModal').addClass('active');
    });

    $('#saveQuickCustomerBtn').on('click', function() {
        const name = $('#quickCustName').val().trim();
        const phone = $('#quickCustPhone').val().trim();

        if (!name) {
            toast('Name is required', 'error');
            return;
        }

        const btn = $(this);
        btn.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin"></i> Saving...');

        $.post('controllers/POSController.php', { action: 'add_customer_minimal', name: name, phone: phone }, function(r) {
            btn.prop('disabled', false).html('<i class="fa-solid fa-check"></i> Save Customer');
            if (r.status === 'success') {
                $('#quickCustomerModal').removeClass('active');
                customers.push(r.customer);
                const sel = $('#customerSelect');
                sel.append(`<option value="${r.customer.id}" selected>${r.customer.name} (${r.customer.phone || 'N/A'})</option>`);
                toast('Customer added');
            } else {
                Swal.fire('Error', r.message, 'error');
            }
        }, 'json');
    });

    $(document).on('click', '.pm-btn', function() {
        $('.pm-btn').removeClass('btn-primary active').addClass('btn-outline');
        $(this).removeClass('btn-outline').addClass('btn-primary active');
        selectedPM = $(this).data('pm');
    });

    $('#amountReceived').on('input', updateChange);

    $('#completeSaleBtn').on('click', function() {
        const btn = $(this);
        const subtotal = cart.reduce((a, b) => a + (b.qty * b.price), 0);
        const total = subtotal;
        const received = parseFloat($('#amountReceived').val()) || 0;

        if (received < total) {
            toast('Amount received is less than total!', 'error');
            return;
        }

        // Open receipt window immediately (same user gesture) so popup blockers don't block it
        const receiptWindow = window.open('', '_blank', 'width=450,height=600');

        btn.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin"></i> Processing...');

        const data = {
            action: 'complete_sale',
            cart: JSON.stringify(cart),
            customer_id: $('#customerSelect').val(),
            subtotal: subtotal,
            tax: 0,
            discount: currentDiscount,
            total: subtotal - currentDiscount,
            amount_received: received,
            payment_method: selectedPM,
            notes: ''
        };

        $.post('controllers/POSController.php', data, function(r) {
            btn.prop('disabled', false).html('<i class="fa-solid fa-check"></i> Complete Sale');
            if (r.status === 'success') {
                $('.modal-overlay').removeClass('active');
                
                // Show a quick success message then print
                Swal.fire({
                    title: 'Success!',
                    text: 'Sale Completed successfully!',
                    icon: 'success',
                    timer: 2000,
                    showConfirmButton: false
                });
                
                // Reset POS
                cart = [];
                currentDiscount = 0;
                updateCart();
                loadPosData(); // Refresh stock quantities
                
                // Load receipt in the already-opened window (use full URL so it works from about:blank)
                const receiptUrl = getReceiptUrl(r.sale_id);
                if (receiptWindow && !receiptWindow.closed) {
                    receiptWindow.location.href = receiptUrl;
                } else {
                    window.open(receiptUrl, '_blank', 'width=450,height=600');
                }
            } else {
                if (receiptWindow && !receiptWindow.closed) receiptWindow.close();
                Swal.fire('Error', r.message, 'error');
            }
        }).fail(() => {
            if (receiptWindow && !receiptWindow.closed) receiptWindow.close();
            btn.prop('disabled', false).html('<i class="fa-solid fa-check"></i> Complete Sale');
            Swal.fire('Error', 'Server connection failed', 'error');
        });
    });

    function getReceiptUrl(saleId) {
        const path = window.location.pathname;
        const basePath = path.substring(0, path.lastIndexOf('/') + 1);
        return window.location.origin + basePath + 'print_receipt.php?id=' + saleId;
    }
    function printReceipt(saleId) {
        window.open(getReceiptUrl(saleId), '_blank', 'width=450,height=600');
    }

    // Modal close Helpers
    $('.modal-close, .modal-cancel').on('click', function() {
        $(this).closest('.modal-overlay').removeClass('active');
    });

    function toast(msg, type='success'){
        let t=$('<div>').addClass('toast toast-'+type).text(msg).appendTo('body');
        setTimeout(()=>t.addClass('show'),10);
        setTimeout(()=>{ t.removeClass('show'); setTimeout(()=>t.remove(),400); },3000);
    }

    // Init
    loadPosData();
});
</script>

<style>
/* POS Specific Styles */
.pos-product-card.out-of-stock {
    opacity: 0.6;
    cursor: not-allowed;
    filter: grayscale(1);
}
.pos-product-card.out-of-stock::after {
    content: 'OUT OF STOCK';
    position: absolute;
    top: 50%; left: 50%;
    transform: translate(-50%, -50%) rotate(-15deg);
    background: var(--danger);
    color: #fff;
    padding: 2px 8px;
    font-size: 0.65rem;
    font-weight: 700;
    border-radius: 4px;
}
.pm-btn.active {
    background: var(--primary-500);
    color: #fff;
    border-color: var(--primary-500);
}

/* Toast logic if not exists */
.toast{position:fixed;bottom:24px;right:24px;padding:12px 20px;border-radius:12px;
    font-size:.88rem;font-weight:600;color:#fff;z-index:9999;
    transform:translateY(20px);opacity:0;transition:all .3s ease;pointer-events:none;max-width:340px;}
.toast.show{transform:translateY(0);opacity:1;}
.toast-success{background:linear-gradient(135deg,#10b981,#059669);}
.toast-error  {background:linear-gradient(135deg,#ef4444,#b91c1c);}
</style>
