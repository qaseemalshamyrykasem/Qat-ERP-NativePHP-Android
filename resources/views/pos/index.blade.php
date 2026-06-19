@extends('layouts.app')
@section('title', 'نقطة البيع')
@section('content')
<div class="container-fluid py-2">
    <div class="row g-3" style="min-height:calc(100vh - 120px)">
        <!-- Products Side -->
        <div class="col-lg-8">
            <div class="card shadow-sm h-100">
                <div class="card-header py-2">
                    <div class="d-flex gap-2 align-items-center">
                        <div class="flex-grow-1">
                            <input type="text" class="form-control form-control-sm" placeholder="بحث عن منتج بالاسم أو الباركود..." id="posSearch" oninput="debounceProductSearch()" autofocus>
                        </div>
                        <span class="text-muted small" id="productCount"></span>
                    </div>
                </div>
                <div class="card-body p-2" style="overflow-y:auto;max-height:calc(100vh - 200px)">
                    <div class="row g-2" id="productGrid"></div>
                </div>
            </div>
        </div>
        <!-- Cart Side -->
        <div class="col-lg-4">
            <div class="card shadow-sm h-100 border-success">
                <div class="card-header bg-success text-white py-2 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><i class="bi bi-cart3"></i> سلة المشتريات</h6>
                    <button class="btn btn-sm btn-outline-light" onclick="clearCart()">تفريغ</button>
                </div>
                <div class="card-body p-2" style="overflow-y:auto;max-height:calc(100vh - 340px)">
                    <div id="cartItems">
                        <div class="text-center text-muted py-4"><i class="bi bi-cart-x display-4"></i><p>السلة فارغة</p></div>
                    </div>
                </div>
                <div class="card-footer bg-white">
                    <!-- Customer Search -->
                    <div class="mb-2">
                        <input type="text" class="form-control form-control-sm" placeholder="بحث عميل (اختياري)..." id="customerSearch" oninput="searchCustomers(this.value)">
                        <input type="hidden" id="customerId">
                        <span class="small text-muted" id="customerName"></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">المجموع الفرعي:</span>
                        <span class="fw-semibold" id="subtotal">0</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">الخصم:</span>
                        <div class="d-flex align-items-center gap-1">
                            <input type="number" class="form-control form-control-sm" style="width:80px" id="discountInput" value="0" min="0" oninput="recalcTotals()">
                        </div>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between mb-3">
                        <span class="fw-bold fs-5">الإجمالي:</span>
                        <span class="fw-bold fs-5 text-success" id="grandTotal">0</span>
                    </div>
                    <div class="d-grid gap-2">
                        <button class="btn btn-success btn-lg" onclick="checkout()" id="checkoutBtn" disabled>
                            <i class="bi bi-check-circle"></i> إتمام البيع
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Checkout Modal -->
<div class="modal fade" id="checkoutModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white"><h5 class="modal-title"><i class="bi bi-receipt"></i> تأكيد البيع</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-sm mb-0" id="checkoutTable"></table>
                </div>
                <hr>
                <div class="d-flex justify-content-between"><span>المجموع:</span><strong id="coSubtotal">0</strong></div>
                <div class="d-flex justify-content-between text-danger"><span>الخصم:</span><strong id="coDiscount">0</strong></div>
                <hr>
                <div class="d-flex justify-content-between fs-5"><strong>الإجمالي النهائي:</strong><strong class="text-success" id="coTotal">0</strong></div>
                <div class="mt-3">
                    <label class="form-label">طريقة الدفع</label>
                    <select class="form-select" id="coPayMethod">
                        <option value="cash">نقدي</option>
                        <option value="debt">آجل (دين)</option>
                        <option value="bank">تحويل بنكي</option>
                        <option value="card">بطاقة</option>
                    </select>
                </div>
                <div class="mt-2">
                    <label class="form-label">المبلغ المدفوع</label>
                    <input type="number" class="form-control" id="coPaidAmount" step="0.01" oninput="calcChange()">
                    <div class="d-flex justify-content-between mt-1"><span class="text-muted">الباقي:</span><strong id="coChange" class="text-primary">0</strong></div>
                </div>
                <div class="mt-2">
                    <label class="form-label">ملاحظات</label>
                    <textarea class="form-control" id="coNotes" rows="2"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                <button type="button" class="btn btn-success btn-lg" onclick="confirmSale()" id="confirmBtn"><i class="bi bi-check2-circle"></i> تأكيد</button>
            </div>
        </div>
    </div>
</div>

<!-- Receipt Modal -->
<div class="modal fade" id="receiptModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white"><h5 class="modal-title text-center w-100"><i class="bi bi-receipt-cutoff"></i> إيصال البيع</h5></div>
            <div class="modal-body" id="receiptContent"></div>
            <div class="modal-footer d-flex justify-content-center gap-2">
                <button class="btn btn-primary" onclick="window.print()"><i class="bi bi-printer"></i> طباعة</button>
                <button class="btn btn-outline-success" onclick="waInvoice()" id="waInvoiceBtn"><i class="bi bi-whatsapp"></i> واتساب</button>
                <button class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
            </div>
        </div>
    </div>
</div>

<script>
const PRODUCTS_API = '/api/v1/products';
const SALES_API = '/api/v1/sales';
let cart = [];
let allProducts = [];
let productSearchTimer;
let lastSaleId = null;

function debounceProductSearch() { clearTimeout(productSearchTimer); productSearchTimer = setTimeout(renderProducts, 300); }

async function loadProducts() {
    try {
        const res = await fetch(`${PRODUCTS_API}?per_page=200&status=active`, {headers:{Accept:'application/json'}});
        const json = await res.json();
        allProducts = json.data || [];
        renderProducts();
    } catch(e) {
        document.getElementById('productGrid').innerHTML = '<div class="col-12 text-center text-danger py-4">خطأ في تحميل المنتجات</div>';
    }
}

function renderProducts() {
    const q = document.getElementById('posSearch').value.toLowerCase();
    const filtered = q ? allProducts.filter(p => (p.name||'').toLowerCase().includes(q) || (p.barcode||'').toLowerCase().includes(q)) : allProducts;
    document.getElementById('productCount').textContent = `${filtered.length} منتج`;
    document.getElementById('productGrid').innerHTML = filtered.length ? filtered.map(p => {
        const inCart = cart.find(c => c.product_id === p.id);
        return `<div class="col-6 col-md-4 col-xl-3">
            <div class="card h-100 ${inCart ? 'border-success' : ''} pos-product-card" onclick="addToCart(${p.id})" style="cursor:pointer">
                <div class="card-body text-center p-2">
                    <div class="text-truncate fw-semibold">${p.name}</div>
                    <div class="text-muted small">${p.type||''}</div>
                    <div class="mt-1"><span class="text-success fw-bold">${formatMoney(p.sell_price)}</span></div>
                    <div class="small ${((p.quantity||0) <= (p.min_quantity||0)) ? 'text-danger' : 'text-muted'}">الكمية: ${p.quantity||0}</div>
                    ${inCart ? `<span class="badge bg-success position-absolute top-0 start-0 m-1">${inCart.qty}</span>` : ''}
                </div>
            </div>
        </div>`;
    }).join('') : '<div class="col-12 text-center text-muted py-4"><i class="bi bi-search display-4"></i><p>لا توجد نتائج</p></div>';
}

function addToCart(productId) {
    const product = allProducts.find(p => p.id === productId);
    if (!product) return;
    const existing = cart.find(c => c.product_id === productId);
    if (existing) {
        if (existing.qty >= (product.quantity || 999)) return showToast('تنبيه', 'الكمية المتوفرة غير كافية', 'warning');
        existing.qty++;
        existing.total = existing.qty * existing.price;
    } else {
        cart.push({
            product_id: product.id,
            name: product.name,
            price: parseFloat(product.sell_price) || 0,
            qty: 1,
            total: parseFloat(product.sell_price) || 0
        });
    }
    renderCart();
    renderProducts();
}

function removeFromCart(idx) {
    cart.splice(idx, 1);
    renderCart();
    renderProducts();
}

function changeQty(idx, delta) {
    if (cart[idx].qty + delta < 1) return removeFromCart(idx);
    cart[idx].qty += delta;
    cart[idx].total = cart[idx].qty * cart[idx].price;
    renderCart();
}

function clearCart() {
    cart = [];
    renderCart();
    renderProducts();
}

function renderCart() {
    const el = document.getElementById('cartItems');
    const btn = document.getElementById('checkoutBtn');
    if (!cart.length) {
        el.innerHTML = '<div class="text-center text-muted py-4"><i class="bi bi-cart-x display-4"></i><p>السلة فارغة</p></div>';
        btn.disabled = true;
    } else {
        el.innerHTML = cart.map((item, i) => `
            <div class="d-flex align-items-center p-2 border-bottom">
                <div class="flex-grow-1 me-2">
                    <div class="fw-semibold small text-truncate">${item.name}</div>
                    <div class="small text-muted">${formatMoney(item.price)} × ${item.qty}</div>
                </div>
                <div class="d-flex align-items-center gap-1">
                    <button class="btn btn-sm btn-outline-secondary" onclick="changeQty(${i},-1)">-</button>
                    <span class="fw-bold px-1">${item.qty}</span>
                    <button class="btn btn-sm btn-outline-secondary" onclick="changeQty(${i},1)">+</button>
                    <span class="fw-bold text-success me-2" style="min-width:70px;text-align:left">${formatMoney(item.total)}</span>
                    <button class="btn btn-sm btn-outline-danger" onclick="removeFromCart(${i})"><i class="bi bi-x"></i></button>
                </div>
            </div>
        `).join('');
        btn.disabled = false;
    }
    recalcTotals();
}

function recalcTotals() {
    const subtotal = cart.reduce((s, i) => s + i.total, 0);
    const discount = parseFloat(document.getElementById('discountInput').value) || 0;
    const total = Math.max(0, subtotal - discount);
    document.getElementById('subtotal').textContent = formatMoney(subtotal);
    document.getElementById('grandTotal').textContent = formatMoney(total);
}

function checkout() {
    if (!cart.length) return;
    const subtotal = cart.reduce((s, i) => s + i.total, 0);
    const discount = parseFloat(document.getElementById('discountInput').value) || 0;
    const total = Math.max(0, subtotal - discount);

    document.getElementById('checkoutTable').innerHTML = `<thead><tr><th>المنتج</th><th>الكمية</th><th>المجموع</th></tr></thead><tbody>` +
        cart.map(i => `<tr><td>${i.name}</td><td>${i.qty}</td><td>${formatMoney(i.total)}</td></tr>`).join('') + '</tbody>';
    document.getElementById('coSubtotal').textContent = formatMoney(subtotal);
    document.getElementById('coDiscount').textContent = formatMoney(discount);
    document.getElementById('coTotal').textContent = formatMoney(total);
    document.getElementById('coPaidAmount').value = total;
    document.getElementById('coChange').textContent = '0';
    document.getElementById('coNotes').value = '';
    new bootstrap.Modal(document.getElementById('checkoutModal')).show();
}

function calcChange() {
    const total = parseFloat(document.getElementById('coTotal').textContent.replace(/[^0-9.-]/g, '')) || 0;
    const paid = parseFloat(document.getElementById('coPaidAmount').value) || 0;
    document.getElementById('coChange').textContent = formatMoney(Math.max(0, paid - total));
}

async function confirmSale() {
    const btn = document.getElementById('confirmBtn');
    btn.classList.add('btn-loading');
    btn.disabled = true;
    const subtotal = cart.reduce((s, i) => s + i.total, 0);
    const discount = parseFloat(document.getElementById('discountInput').value) || 0;
    const data = {
        customer_id: document.getElementById('customerId').value || null,
        items: cart.map(i => ({product_id: i.product_id, quantity: i.qty, price: i.price})),
        discount: discount,
        payment_method: document.getElementById('coPayMethod').value,
        paid_amount: document.getElementById('coPaidAmount').value,
        notes: document.getElementById('coNotes').value,
    };
    try {
        const res = await fetch(SALES_API, {
            method: 'POST',
            headers: {'Content-Type':'application/json','X-CSRF-TOKEN':CSRF_TOKEN,Accept:'application/json'},
            body: JSON.stringify(data)
        });
        const json = await res.json();
        if (json.id || json.success) {
            bootstrap.Modal.getInstance(document.getElementById('checkoutModal')).hide();
            lastSaleId = json.id || json.data?.id;
            clearCart();
            document.getElementById('customerId').value = '';
            document.getElementById('customerName').textContent = '';
            document.getElementById('customerSearch').value = '';
            document.getElementById('discountInput').value = '0';
            if (lastSaleId) showReceipt(json);
            showToast('نجاح', 'تمت عملية البيع بنجاح', 'success');
            loadProducts(); // refresh stock
        } else {
            showToast('خطأ', json.message || 'حدث خطأ', 'danger');
        }
    } catch(e) {
        showToast('خطأ', 'خطأ في الاتصال', 'danger');
    }
    btn.classList.remove('btn-loading');
    btn.disabled = false;
}

function showReceipt(sale) {
    const d = sale.data || sale;
    document.getElementById('receiptContent').innerHTML = `
        <div class="text-center mb-3">
            <h5>فاتورة مبيعات</h5>
            <p class="text-muted mb-0">${d.invoice_no||'#'+d.id}</p>
            <small>${d.sale_date||d.created_at?.substring(0,19)||''}</small>
        </div>
        ${d.customer_name?'<p><strong>العميل:</strong> '+d.customer_name+'</p>':''}
        <table class="table table-sm"><thead><tr><th>المنتج</th><th>الكمية</th><th>السعر</th><th>المجموع</th></tr></thead>
        <tbody>${(d.items||[]).map(i=>`<tr><td>${i.product_name||i.name||'-'}</td><td>${i.quantity}</td><td>${formatMoney(i.price)}</td><td>${formatMoney(i.total)}</td></tr>`).join('')}</tbody></table>
        <div class="d-flex justify-content-between"><span>المجموع:</span><strong>${formatMoney(d.subtotal||d.total)}</strong></div>
        ${d.discount>0?`<div class="d-flex justify-content-between text-danger"><span>الخصم:</span><strong>${formatMoney(d.discount)}</strong></div>`:''}
        <hr><div class="d-flex justify-content-between fs-5"><strong>الإجمالي:</strong><strong class="text-success">${formatMoney(d.total)}</strong></div>
        ${d.paid_amount!==undefined?`<div class="d-flex justify-content-between mt-1"><span>المدفوع:</span><strong>${formatMoney(d.paid_amount)}</strong></div>
        <div class="d-flex justify-content-between"><span>الباقي:</span><strong>${formatMoney((d.paid_amount||0)-(d.total||0))}</strong></div>`:''}
    `;
    document.getElementById('waInvoiceBtn').style.display = d.customer_phone ? '' : 'none';
    new bootstrap.Modal(document.getElementById('receiptModal')).show();
}

async function waInvoice() {
    if (!lastSaleId) return;
    try {
        const r = await fetch(`/api/v1/whatsapp/sale/${lastSaleId}/invoice`, {headers:{Accept:'application/json'}});
        const j = await r.json();
        if (j.link) window.open(j.link, '_blank');
    } catch(e) {}
}

let customerSearchTimer;
function searchCustomers(q) {
    clearTimeout(customerSearchTimer);
    if (q.length < 1) { document.getElementById('customerName').textContent = ''; document.getElementById('customerId').value = ''; return; }
    customerSearchTimer = setTimeout(async () => {
        try {
            const r = await fetch(`/api/v1/customers?search=${encodeURIComponent(q)}&per_page=5`, {headers:{Accept:'application/json'}});
            const j = await r.json();
            const items = j.data || [];
            if (items.length) {
                const c = items[0];
                document.getElementById('customerId').value = c.id;
                document.getElementById('customerName').textContent = c.name + (c.phone ? ' (' + c.phone + ')' : '');
            }
        } catch(e) {}
    }, 400);
}

// Init
loadProducts();
</script>
@endsection