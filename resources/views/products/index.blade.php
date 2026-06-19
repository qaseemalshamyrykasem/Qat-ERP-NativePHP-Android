@extends('layouts.app')
@section('title', 'المنتجات')
@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="bi bi-box-seam text-success"></i> المنتجات</h2>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-secondary btn-sm" onclick="exportData()"><i class="bi bi-download"></i> تصدير</button>
            <button class="btn btn-success" onclick="openAddModal()"><i class="bi bi-plus-lg"></i> إضافة منتج</button>
        </div>
    </div>
    <div class="card shadow-sm">
        <div class="card-body">
            <div class="d-flex gap-2 mb-3 flex-wrap">
                <input type="text" class="form-control form-control-sm" style="max-width:250px" placeholder="بحث بالاسم..." id="searchInput" oninput="debounceSearch()">
                <select class="form-select form-select-sm" style="max-width:150px" id="statusFilter" onchange="loadData()">
                    <option value="">كل الحالات</option>
                    <option value="active">نشط</option>
                    <option value="inactive">متوقف</option>
                </select>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead><tr><th>#</th><th>المنتج</th><th>النوع</th><th>سعر الشراء</th><th>سعر البيع</th><th>الكمية</th><th>الوحدة</th><th>الحالة</th><th>إجراءات</th></tr></thead>
                    <tbody id="rows"></tbody>
                </table>
            </div>
            <div class="mobile-cards" id="mobileCards"></div>
            <div class="d-flex justify-content-between align-items-center mt-3" id="pagination"></div>
        </div>
    </div>
</div>

<!-- Add/Edit Modal -->
<div class="modal fade" id="itemModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title" id="modalTitle">إضافة منتج</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <input type="hidden" id="editId">
                <div class="mb-3"><label class="form-label">اسم المنتج *</label><input type="text" class="form-control" id="fName" required></div>
                <div class="mb-3"><label class="form-label">النوع</label><input type="text" class="form-control" id="fType" placeholder="مثلاً: خضرة، مسور"></div>
                <div class="row g-2 mb-3">
                    <div class="col-6"><label class="form-label">سعر الشراء</label><input type="number" class="form-control" id="fBuyPrice" step="0.01"></div>
                    <div class="col-6"><label class="form-label">سعر البيع</label><input type="number" class="form-control" id="fSellPrice" step="0.01"></div>
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-4"><label class="form-label">الكمية</label><input type="number" class="form-control" id="fQty"></div>
                    <div class="col-4"><label class="form-label">الوحدة</label><input type="text" class="form-control" id="fUnit" placeholder="حزمة"></div>
                    <div class="col-4"><label class="form-label">حد أدنى</label><input type="number" class="form-control" id="fMinQty"></div>
                </div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button><button type="button" class="btn btn-success" onclick="saveItem()" id="saveBtn">حفظ</button></div>
        </div>
    </div>
</div>

@include('components.confirmation-modal')

<script>
const API = '/api/v1/products';
let currentPage = 1, lastPage = 1;
let searchTimer;

function debounceSearch() { clearTimeout(searchTimer); searchTimer = setTimeout(() => { currentPage = 1; loadData(); }, 400); }

async function loadData() {
    const search = document.getElementById('searchInput').value;
    const status = document.getElementById('statusFilter').value;
    let url = `${API}?per_page=20&page=${currentPage}`;
    if (search) url += `&search=${encodeURIComponent(search)}`;
    if (status) url += `&status=${status}`;
    try {
        const res = await fetch(url, {headers:{Accept:'application/json'}});
        const json = await res.json();
        const rows = json.data || [];
        lastPage = json.last_page || 1;
        renderTable(rows);
        renderPagination(json);
    } catch(e) { document.getElementById('rows').innerHTML = '<tr><td colspan="9" class="text-center text-danger">خطأ في تحميل البيانات</td></tr>'; }
}

function renderTable(rows) {
    document.getElementById('rows').innerHTML = rows.length ? rows.map((r,i) => `<tr>
        <td>${r.id}</td>
        <td><span class="fw-semibold">${r.name}</span></td>
        <td>${r.type || '-'}</td>
        <td>${formatMoney(r.buy_price)}</td>
        <td>${formatMoney(r.sell_price)}</td>
        <td><span class="${(r.quantity||0) <= (r.min_quantity||0) ? 'text-danger fw-bold' : ''}">${r.quantity||0}</span></td>
        <td>${r.unit || '-'}</td>
        <td>${statusBadge(r.status || 'active')}</td>
        <td>
            <div class="btn-group btn-group-sm">
                <button class="btn btn-outline-primary" onclick="editItem(${r.id})" title="تعديل"><i class="bi bi-pencil"></i></button>
                <button class="btn btn-outline-danger" onclick="delItem(${r.id})" title="حذف"><i class="bi bi-trash"></i></button>
            </div>
        </td>
    </tr>`).join('') : '<tr><td colspan="9" class="text-center text-muted py-4">لا توجد بيانات</td></tr>';

    document.getElementById('mobileCards').innerHTML = rows.map(r => `<div class="mobile-card">
        <div class="mobile-card-title"><span>${r.name}</span>${statusBadge(r.status||'active')}</div>
        <div class="mobile-card-row"><span>السعر</span><span>${formatMoney(r.sell_price)}</span></div>
        <div class="mobile-card-row"><span>الكمية</span><span>${r.quantity||0}</span></div>
        <div class="d-flex gap-1 mt-2"><button class="btn btn-sm btn-outline-primary" onclick="editItem(${r.id})"><i class="bi bi-pencil"></i></button><button class="btn btn-sm btn-outline-danger" onclick="delItem(${r.id})"><i class="bi bi-trash"></i></button></div>
    </div>`).join('');
}

function renderPagination(json) {
    const p = document.getElementById('pagination');
    p.innerHTML = `<span class="text-muted small">عرض ${(json.from||0)}-${(json.to||0)} من ${json.total||0}</span>
    <div class="btn-group btn-group-sm">
        <button class="btn btn-outline-success" ${currentPage<=1?'disabled':''} onclick="goPage(${currentPage-1})"><i class="bi bi-chevron-right"></i></button>
        <span class="btn btn-success disabled">${currentPage} / ${lastPage}</span>
        <button class="btn btn-outline-success" ${currentPage>=lastPage?'disabled':''} onclick="goPage(${currentPage+1})"><i class="bi bi-chevron-left"></i></button>
    </div>`;
}
function goPage(p) { currentPage = p; loadData(); }

function openAddModal() {
    document.getElementById('editId').value = '';
    document.getElementById('modalTitle').textContent = 'إضافة منتج';
    ['fName','fType','fBuyPrice','fSellPrice','fQty','fUnit','fMinQty'].forEach(id => document.getElementById(id).value = '');
    new bootstrap.Modal(document.getElementById('itemModal')).show();
}

async function editItem(id) {
    try {
        const res = await fetch(`${API}/${id}`, {headers:{Accept:'application/json'}});
        const r = await res.json();
        document.getElementById('editId').value = r.id;
        document.getElementById('modalTitle').textContent = 'تعديل منتج';
        document.getElementById('fName').value = r.name;
        document.getElementById('fType').value = r.type || '';
        document.getElementById('fBuyPrice').value = r.buy_price || '';
        document.getElementById('fSellPrice').value = r.sell_price || '';
        document.getElementById('fQty').value = r.quantity || '';
        document.getElementById('fUnit').value = r.unit || '';
        document.getElementById('fMinQty').value = r.min_quantity || '';
        new bootstrap.Modal(document.getElementById('itemModal')).show();
    } catch(e) { showToast('خطأ','حدث خطأ','danger'); }
}

async function saveItem() {
    const id = document.getElementById('editId').value;
    const data = {
        name: document.getElementById('fName').value,
        type: document.getElementById('fType').value,
        buy_price: document.getElementById('fBuyPrice').value || null,
        sell_price: document.getElementById('fSellPrice').value || null,
        quantity: document.getElementById('fQty').value || 0,
        unit: document.getElementById('fUnit').value,
        min_quantity: document.getElementById('fMinQty').value || null,
    };
    if (!data.name) return showToast('تنبيه','اسم المنتج مطلوب','warning');

    const btn = document.getElementById('saveBtn');
    btn.classList.add('btn-loading');
    btn.disabled = true;

    try {
        const opts = { method: id ? 'PUT' : 'POST', headers: {'Content-Type':'application/json','X-CSRF-TOKEN':CSRF_TOKEN,Accept:'application/json'}, body: JSON.stringify(data) };
        const res = await fetch(id ? `${API}/${id}` : API, opts);
        const json = await res.json();
        if (json.id || json.success) {
            bootstrap.Modal.getInstance(document.getElementById('itemModal')).hide();
            loadData();
            showToast('نجاح', id ? 'تم تعديل المنتج' : 'تم إضافة المنتج', 'success');
        } else {
            showToast('خطأ', json.message || 'حدث خطأ', 'danger');
        }
    } catch(e) { showToast('خطأ','حدث خطأ في الاتصال','danger'); }
    btn.classList.remove('btn-loading');
    btn.disabled = false;
}

async function delItem(id) {
    if (!confirmDelete()) return;
    try {
        await fetch(`${API}/${id}`, {method:'DELETE',headers:{'X-CSRF-TOKEN':CSRF_TOKEN,Accept:'application/json'}});
        loadData();
        showToast('نجاح','تم حذف المنتج','success');
    } catch(e) { showToast('خطأ','حدث خطأ','danger'); }
}

async function exportData() {
    try {
        const res = await fetch(`${API}?per_page=1000`, {headers:{Accept:'application/json'}});
        const json = await res.json();
        exportCsv(json.data || [], 'products.csv');
    } catch(e) { showToast('خطأ','خطأ في التصدير','danger'); }
}

loadData();
</script>
@endsection