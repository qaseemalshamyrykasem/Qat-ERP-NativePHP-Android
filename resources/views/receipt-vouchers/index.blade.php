@extends('layouts.app')
@section('title', 'سندات القبض')
@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="bi bi-arrow-down-circle text-success"></i> سندات القبض</h2>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-secondary btn-sm" onclick="exportData()"><i class="bi bi-download"></i> تصدير</button>
            <button class="btn btn-success" onclick="openAddModal()"><i class="bi bi-plus-lg"></i> سند قبض جديد</button>
        </div>
    </div>
    <div class="card shadow-sm">
        <div class="card-body">
            <div class="d-flex gap-2 mb-3 flex-wrap">
                <input type="text" class="form-control form-control-sm" style="max-width:250px" placeholder="بحث..." id="searchInput" oninput="debounceSearch()">
                <input type="date" class="form-control form-control-sm" style="max-width:180px" id="dateFrom" onchange="loadData()">
                <input type="date" class="form-control form-control-sm" style="max-width:180px" id="dateTo" onchange="loadData()">
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead><tr><th>#</th><th>الرقم</th><th>التاريخ</th><th>الحساب</th><th>العميل</th><th>المبلغ</th><th>طريقة الدفع</th><th>إجراءات</th></tr></thead>
                    <tbody id="rows"></tbody>
                </table>
            </div>
            <div class="mobile-cards" id="mobileCards"></div>
            <div class="d-flex justify-content-between align-items-center mt-3" id="pagination"></div>
        </div>
    </div>
</div>
<div class="modal fade" id="itemModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">سند قبض جديد</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="mb-3"><label class="form-label">التاريخ</label><input type="date" class="form-control" id="fDate"></div>
                <div class="mb-3"><label class="form-label">الحساب</label>
                    <select class="form-select" id="fAccountId"><option value="">اختر الحساب</option></select>
                </div>
                <div class="mb-3"><label class="form-label">العميل</label>
                    <input type="text" class="form-control" id="fCustomerSearch" placeholder="ابحث عن عميل..." oninput="searchCustomers(this.value)">
                    <input type="hidden" id="fCustomerId">
                    <div id="customerResults" class="dropdown-menu show position-relative w-100 d-none"></div>
                </div>
                <div class="mb-3"><label class="form-label">المبلغ *</label><input type="number" class="form-control" id="fAmount" step="0.01"></div>
                <div class="mb-3"><label class="form-label">طريقة الدفع</label>
                    <select class="form-select" id="fPayMethod"><option value="cash">نقدي</option><option value="bank">تحويل بنكي</option><option value="card">بطاقة</option></select>
                </div>
                <div class="mb-3"><label class="form-label">ملاحظات</label><textarea class="form-control" id="fNotes" rows="2"></textarea></div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button><button type="button" class="btn btn-success" onclick="saveItem()" id="saveBtn">حفظ</button></div>
        </div>
    </div>
</div>
@include('components.confirmation-modal')
<script>
const API='/api/v1/receipt-vouchers';let currentPage=1,lastPage=1,searchTimer;
const payLabels={cash:'نقدي',bank:'تحويل بنكي',card:'بطاقة'};
function debounceSearch(){clearTimeout(searchTimer);searchTimer=setTimeout(()=>{currentPage=1;loadData()},400);}
async function loadData(){const s=document.getElementById('searchInput').value,df=document.getElementById('dateFrom').value,dt=document.getElementById('dateTo').value;let u=`${API}?per_page=20&page=${currentPage}`;if(s)u+=`&search=${encodeURIComponent(s)}`;if(df)u+=`&date_from=${df}`;if(dt)u+=`&date_to=${dt}`;try{const r=await fetch(u,{headers:{Accept:'application/json'}});const j=await r.json();renderTable(j.data||[]);lastPage=j.last_page||1;renderPagination(j);}catch(e){document.getElementById('rows').innerHTML='<tr><td colspan="8" class="text-center text-danger">خطأ في تحميل البيانات</td></tr>';}}
function renderTable(rows){document.getElementById('rows').innerHTML=rows.length?rows.map(r=>`<tr>
<td>${r.id}</td><td class="fw-semibold">${r.voucher_no||'#'+r.id}</td><td>${r.voucher_date||r.created_at?.substring(0,10)||'-'}</td>
<td>${r.account_name||r.account?.name||'-'}</td><td>${r.customer_name||r.customer?.name||'-'}</td>
<td class="fw-semibold">${formatMoney(r.amount)}</td><td>${payLabels[r.payment_method]||r.payment_method||'-'}</td>
<td><div class="btn-group btn-group-sm">
<button class="btn btn-outline-danger" onclick="delItem(${r.id})"><i class="bi bi-trash"></i></button>
</div></td></tr>`).join(''):'<tr><td colspan="8" class="text-center text-muted py-4">لا توجد بيانات</td></tr>';
document.getElementById('mobileCards').innerHTML=rows.map(r=>`<div class="mobile-card"><div class="mobile-card-title"><span>${r.voucher_no||'#'+r.id}</span></div><div class="mobile-card-row"><span>العميل</span><span>${r.customer_name||'-'}</span></div><div class="mobile-card-row"><span>المبلغ</span><span class="fw-bold">${formatMoney(r.amount)}</span></div><div class="d-flex gap-1 mt-2"><button class="btn btn-sm btn-outline-danger" onclick="delItem(${r.id})"><i class="bi bi-trash"></i></button></div></div>`).join('');}
function renderPagination(j){const p=document.getElementById('pagination');p.innerHTML=`<span class="text-muted small">عرض ${j.from||0}-${j.to||0} من ${j.total||0}</span><div class="btn-group btn-group-sm"><button class="btn btn-outline-success" ${currentPage<=1?'disabled':''} onclick="goPage(${currentPage-1})"><i class="bi bi-chevron-right"></i></button><span class="btn btn-success disabled">${currentPage}/${lastPage}</span><button class="btn btn-outline-success" ${currentPage>=lastPage?'disabled':''} onclick="goPage(${currentPage+1})"><i class="bi bi-chevron-left"></i></button></div>`;}
function goPage(p){currentPage=p;loadData();}
async function openAddModal(){document.getElementById('fDate').value=new Date().toISOString().substring(0,10);document.getElementById('fCustomerId').value='';document.getElementById('fCustomerSearch').value='';document.getElementById('fAmount').value='';document.getElementById('fNotes').value='';try{const r=await fetch('/api/v1/chart-of-accounts?per_page=100',{headers:{Accept:'application/json'}});const j=await r.json();document.getElementById('fAccountId').innerHTML='<option value="">اختر الحساب</option>'+(j.data||[]).map(a=>`<option value="${a.id}">${a.name}</option>`).join('');}catch(e){}new bootstrap.Modal(document.getElementById('itemModal')).show();}
async function searchCustomers(q){const box=document.getElementById('customerResults');if(q.length<1){box.classList.add('d-none');return;}try{const r=await fetch(`/api/v1/customers?search=${encodeURIComponent(q)}&per_page=10`,{headers:{Accept:'application/json'}});const j=await r.json();const items=j.data||[];if(!items.length){box.classList.add('d-none');return;}box.classList.remove('d-none');box.innerHTML=items.map(c=>`<a class="dropdown-item" href="#" onclick="selectCustomer(${c.id},'${c.name}')">${c.name} <small class="text-muted">${c.phone||''}</small></a>`).join('');}catch(e){box.classList.add('d-none');}}
function selectCustomer(id,name){document.getElementById('fCustomerId').value=id;document.getElementById('fCustomerSearch').value=name;document.getElementById('customerResults').classList.add('d-none');}
async function saveItem(){const d={voucher_date:document.getElementById('fDate').value,account_id:document.getElementById('fAccountId').value,customer_id:document.getElementById('fCustomerId').value,amount:document.getElementById('fAmount').value,payment_method:document.getElementById('fPayMethod').value,notes:document.getElementById('fNotes').value};if(!d.amount)return showToast('تنبيه','المبلغ مطلوب','warning');const btn=document.getElementById('saveBtn');btn.classList.add('btn-loading');btn.disabled=true;try{const o={method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF_TOKEN,Accept:'application/json'},body:JSON.stringify(d)};const r=await fetch(API,o);const j=await r.json();if(j.id||j.success){bootstrap.Modal.getInstance(document.getElementById('itemModal')).hide();loadData();showToast('نجاح','تم إنشاء سند القبض','success');}else showToast('خطأ',j.message||'حدث خطأ','danger');}catch(e){showToast('خطأ','خطأ في الاتصال','danger');}btn.classList.remove('btn-loading');btn.disabled=false;}
async function delItem(id){if(!confirmDelete())return;try{await fetch(`${API}/${id}`,{method:'DELETE',headers:{'X-CSRF-TOKEN':CSRF_TOKEN,Accept:'application/json'}});loadData();showToast('نجاح','تم الحذف','success');}catch(e){showToast('خطأ','حدث خطأ','danger');}}
async function exportData(){try{const r=await fetch(`${API}?per_page=1000`,{headers:{Accept:'application/json'}});const j=await r.json();exportCsv(j.data||[],'receipt-vouchers.csv');}catch(e){showToast('خطأ','خطأ في التصدير','danger');}}
loadData();
</script>
@endsection