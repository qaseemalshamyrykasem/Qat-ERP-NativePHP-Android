@extends('layouts.app')
@section('title', 'الديون')
@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="bi bi-cash-coin text-danger"></i> الديون</h2>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-secondary btn-sm" onclick="exportData()"><i class="bi bi-download"></i> تصدير</button>
        </div>
    </div>
    <div class="card shadow-sm">
        <div class="card-body">
            <div class="d-flex gap-2 mb-3 flex-wrap">
                <input type="text" class="form-control form-control-sm" style="max-width:250px" placeholder="بحث بالاسم أو الفاتورة..." id="searchInput" oninput="debounceSearch()">
                <select class="form-select form-select-sm" style="max-width:150px" id="statusFilter" onchange="loadData()">
                    <option value="">كل الحالات</option>
                    <option value="pending">معلق</option>
                    <option value="partial">جزئي</option>
                    <option value="paid">مدفوع</option>
                    <option value="overdue">متأخر</option>
                </select>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead><tr><th>#</th><th>العميل</th><th>الفاتورة</th><th>المبلغ</th><th>المدفوع</th><th>المتبقي</th><th>الاستحقاق</th><th>الحالة</th><th>إجراءات</th></tr></thead>
                    <tbody id="rows"></tbody>
                </table>
            </div>
            <div class="mobile-cards" id="mobileCards"></div>
            <div class="d-flex justify-content-between align-items-center mt-3" id="pagination"></div>
        </div>
    </div>
</div>
<!-- Pay Modal -->
<div class="modal fade" id="payModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">تسديد دين</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <input type="hidden" id="payId">
                <div class="alert alert-info"><strong>المتبقي:</strong> <span id="payRemaining">0</span></div>
                <div class="mb-3"><label class="form-label">المبلغ المدفوع *</label><input type="number" class="form-control" id="payAmount" step="0.01"></div>
                <div class="mb-3"><label class="form-label">ملاحظات</label><textarea class="form-control" id="payNotes" rows="2"></textarea></div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button><button type="button" class="btn btn-success" onclick="submitPayment()" id="payBtn">تسديد</button></div>
        </div>
    </div>
</div>
@include('components.confirmation-modal')
<script>
const API='/api/v1/debts';let currentPage=1,lastPage=1,searchTimer;
function debounceSearch(){clearTimeout(searchTimer);searchTimer=setTimeout(()=>{currentPage=1;loadData()},400);}
async function loadData(){const s=document.getElementById('searchInput').value,st=document.getElementById('statusFilter').value;let u=`${API}?per_page=20&page=${currentPage}`;if(s)u+=`&search=${encodeURIComponent(s)}`;if(st)u+=`&status=${st}`;try{const r=await fetch(u,{headers:{Accept:'application/json'}});const j=await r.json();renderTable(j.data||[]);lastPage=j.last_page||1;renderPagination(j);}catch(e){document.getElementById('rows').innerHTML='<tr><td colspan="9" class="text-center text-danger">خطأ في تحميل البيانات</td></tr>';}}
function debtStatusBadge(s){const m={pending:'warning',partial:'info',paid:'success',overdue:'danger'};const l={pending:'معلق',partial:'جزئي',paid:'مدفوع',overdue:'متأخر'};return `<span class="badge bg-${m[s]||'secondary'}">${l[s]||s||'-'}</span>`;}
function renderTable(rows){document.getElementById('rows').innerHTML=rows.length?rows.map(r=>{const remaining=(r.amount||0)-(r.paid||0);return `<tr>
<td>${r.id}</td><td class="fw-semibold">${r.customer_name||r.customer?.name||'-'}</td><td>${r.invoice_no||'-'}</td>
<td>${formatMoney(r.amount)}</td><td>${formatMoney(r.paid)}</td><td><span class="${remaining>0?'text-danger fw-bold':'text-success'}">${formatMoney(remaining)}</span></td>
<td>${r.due_date||'-'}</td><td>${debtStatusBadge(r.status)}</td>
<td><div class="btn-group btn-group-sm">
${remaining>0?`<button class="btn btn-outline-success" onclick="openPayModal(${r.id},${remaining})" title="تسديد"><i class="bi bi-check-circle"></i></button>`:''}
${r.customer_phone||r.customer?.phone?`<button class="btn btn-outline-success" onclick="waReminder(${r.id})" title="تذكير واتساب"><i class="bi bi-whatsapp"></i></button>`:''}
<button class="btn btn-outline-danger" onclick="delItem(${r.id})"><i class="bi bi-trash"></i></button>
</div></td></tr>`;}).join(''):'<tr><td colspan="9" class="text-center text-muted py-4">لا توجد ديون</td></tr>';
document.getElementById('mobileCards').innerHTML=rows.map(r=>{const remaining=(r.amount||0)-(r.paid||0);return `<div class="mobile-card"><div class="mobile-card-title"><span>${r.customer_name||'-'}</span>${debtStatusBadge(r.status)}</div><div class="mobile-card-row"><span>المتبقي</span><span class="${remaining>0?'text-danger':''}">${formatMoney(remaining)}</span></div><div class="mobile-card-row"><span>الاستحقاق</span><span>${r.due_date||'-'}</span></div><div class="d-flex gap-1 mt-2">${remaining>0?`<button class="btn btn-sm btn-outline-success" onclick="openPayModal(${r.id},${remaining})"><i class="bi bi-check-circle"></i></button>`:''}<button class="btn btn-sm btn-outline-danger" onclick="delItem(${r.id})"><i class="bi bi-trash"></i></button></div></div>`;}).join('');}
function renderPagination(j){const p=document.getElementById('pagination');p.innerHTML=`<span class="text-muted small">عرض ${j.from||0}-${j.to||0} من ${j.total||0}</span><div class="btn-group btn-group-sm"><button class="btn btn-outline-success" ${currentPage<=1?'disabled':''} onclick="goPage(${currentPage-1})"><i class="bi bi-chevron-right"></i></button><span class="btn btn-success disabled">${currentPage}/${lastPage}</span><button class="btn btn-outline-success" ${currentPage>=lastPage?'disabled':''} onclick="goPage(${currentPage+1})"><i class="bi bi-chevron-left"></i></button></div>`;}
function goPage(p){currentPage=p;loadData();}
function openPayModal(id,remaining){document.getElementById('payId').value=id;document.getElementById('payRemaining').textContent=formatMoney(remaining);document.getElementById('payAmount').value='';document.getElementById('payNotes').value='';new bootstrap.Modal(document.getElementById('payModal')).show();}
async function submitPayment(){const id=document.getElementById('payId').value;const amount=parseFloat(document.getElementById('payAmount').value);if(!amount||amount<=0)return showToast('تنبيه','أدخل مبلغ صحيح','warning');const btn=document.getElementById('payBtn');btn.classList.add('btn-loading');btn.disabled=true;try{const o={method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF_TOKEN,Accept:'application/json'},body:JSON.stringify({debt_id:id,amount,notes:document.getElementById('payNotes').value})};const r=await fetch(`${API}/pay`,o);const j=await r.json();if(j.success||j.id){bootstrap.Modal.getInstance(document.getElementById('payModal')).hide();loadData();showToast('نجاح','تم التسديد','success');}else showToast('خطأ',j.message||'حدث خطأ','danger');}catch(e){showToast('خطأ','خطأ في الاتصال','danger');}btn.classList.remove('btn-loading');btn.disabled=false;}
async function waReminder(id){try{const r=await fetch(`/api/v1/whatsapp/debt/${id}/reminder`,{headers:{Accept:'application/json'}});const j=await r.json();if(j.link)window.open(j.link,'_blank');else showToast('خطأ','لا يمكن إنشاء رابط','danger');}catch(e){window.open(`https://wa.me/`,'_blank');}}
async function delItem(id){if(!confirmDelete())return;try{await fetch(`${API}/${id}`,{method:'DELETE',headers:{'X-CSRF-TOKEN':CSRF_TOKEN,Accept:'application/json'}});loadData();showToast('نجاح','تم الحذف','success');}catch(e){showToast('خطأ','حدث خطأ','danger');}}
async function exportData(){try{const r=await fetch(`${API}?per_page=1000`,{headers:{Accept:'application/json'}});const j=await r.json();exportCsv(j.data||[],'debts.csv');}catch(e){showToast('خطأ','خطأ في التصدير','danger');}}
loadData();
</script>
@endsection