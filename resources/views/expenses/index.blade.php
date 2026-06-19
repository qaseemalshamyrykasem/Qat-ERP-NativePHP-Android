@extends('layouts.app')
@section('title', 'المصروفات')
@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="bi bi-wallet2 text-success"></i> المصروفات</h2>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-secondary btn-sm" onclick="exportData()"><i class="bi bi-download"></i> تصدير</button>
            <button class="btn btn-success" onclick="openAddModal()"><i class="bi bi-plus-lg"></i> إضافة مصروف</button>
        </div>
    </div>
    <div class="card shadow-sm">
        <div class="card-body">
            <div class="d-flex gap-2 mb-3 flex-wrap">
                <input type="text" class="form-control form-control-sm" style="max-width:250px" placeholder="بحث..." id="searchInput" oninput="debounceSearch()">
                <select class="form-select form-select-sm" style="max-width:150px" id="categoryFilter" onchange="loadData()">
                    <option value="">كل التصنيفات</option>
                    <option value="rent">إيجار</option>
                    <option value="salary">رواتب</option>
                    <option value="transport">نقل</option>
                    <option value="utilities">خدمات</option>
                    <option value="maintenance">صيانة</option>
                    <option value="other">أخرى</option>
                </select>
                <input type="date" class="form-control form-control-sm" style="max-width:180px" id="dateFrom" onchange="loadData()">
                <input type="date" class="form-control form-control-sm" style="max-width:180px" id="dateTo" onchange="loadData()">
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead><tr><th>#</th><th>التصنيف</th><th>المبلغ</th><th>طريقة الدفع</th><th>التاريخ</th><th>الوصف</th><th>إجراءات</th></tr></thead>
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
            <div class="modal-header"><h5 class="modal-title" id="modalTitle">إضافة مصروف</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <input type="hidden" id="editId">
                <div class="mb-3"><label class="form-label">التصنيف *</label>
                    <select class="form-select" id="fCategory">
                        <option value="">اختر التصنيف</option>
                        <option value="rent">إيجار</option>
                        <option value="salary">رواتب</option>
                        <option value="transport">نقل</option>
                        <option value="utilities">خدمات</option>
                        <option value="maintenance">صيانة</option>
                        <option value="other">أخرى</option>
                    </select>
                </div>
                <div class="mb-3"><label class="form-label">المبلغ *</label><input type="number" class="form-control" id="fAmount" step="0.01"></div>
                <div class="mb-3"><label class="form-label">طريقة الدفع</label>
                    <select class="form-select" id="fPaymentMethod">
                        <option value="cash">نقدي</option>
                        <option value="bank">تحويل بنكي</option>
                        <option value="card">بطاقة</option>
                    </select>
                </div>
                <div class="mb-3"><label class="form-label">التاريخ</label><input type="date" class="form-control" id="fDate"></div>
                <div class="mb-3"><label class="form-label">الوصف</label><textarea class="form-control" id="fDescription" rows="2"></textarea></div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button><button type="button" class="btn btn-success" onclick="saveItem()" id="saveBtn">حفظ</button></div>
        </div>
    </div>
</div>
@include('components.confirmation-modal')
<script>
const API='/api/v1/expenses';let currentPage=1,lastPage=1,searchTimer;
const catLabels={rent:'إيجار',salary:'رواتب',transport:'نقل',utilities:'خدمات',maintenance:'صيانة',other:'أخرى'};
const payLabels={cash:'نقدي',bank:'تحويل بنكي',card:'بطاقة'};
function debounceSearch(){clearTimeout(searchTimer);searchTimer=setTimeout(()=>{currentPage=1;loadData()},400);}
async function loadData(){const s=document.getElementById('searchInput').value,cat=document.getElementById('categoryFilter').value,df=document.getElementById('dateFrom').value,dt=document.getElementById('dateTo').value;let u=`${API}?per_page=20&page=${currentPage}`;if(s)u+=`&search=${encodeURIComponent(s)}`;if(cat)u+=`&category=${cat}`;if(df)u+=`&date_from=${df}`;if(dt)u+=`&date_to=${dt}`;try{const r=await fetch(u,{headers:{Accept:'application/json'}});const j=await r.json();renderTable(j.data||[]);lastPage=j.last_page||1;renderPagination(j);}catch(e){document.getElementById('rows').innerHTML='<tr><td colspan="7" class="text-center text-danger">خطأ في تحميل البيانات</td></tr>';}}
function renderTable(rows){document.getElementById('rows').innerHTML=rows.length?rows.map(r=>`<tr>
<td>${r.id}</td><td><span class="badge bg-secondary">${catLabels[r.category]||r.category||'-'}</span></td>
<td class="fw-semibold">${formatMoney(r.amount)}</td><td>${payLabels[r.payment_method]||r.payment_method||'-'}</td>
<td>${r.expense_date||r.created_at?.substring(0,10)||'-'}</td><td>${r.description||'-'}</td>
<td><div class="btn-group btn-group-sm">
<button class="btn btn-outline-primary" onclick="editItem(${r.id})"><i class="bi bi-pencil"></i></button>
<button class="btn btn-outline-danger" onclick="delItem(${r.id})"><i class="bi bi-trash"></i></button>
</div></td></tr>`).join(''):'<tr><td colspan="7" class="text-center text-muted py-4">لا توجد بيانات</td></tr>';
document.getElementById('mobileCards').innerHTML=rows.map(r=>`<div class="mobile-card"><div class="mobile-card-title"><span>${catLabels[r.category]||r.category||'-'}</span><span class="fw-bold">${formatMoney(r.amount)}</span></div><div class="mobile-card-row"><span>التاريخ</span><span>${r.expense_date||'-'}</span></div><div class="d-flex gap-1 mt-2"><button class="btn btn-sm btn-outline-primary" onclick="editItem(${r.id})"><i class="bi bi-pencil"></i></button><button class="btn btn-sm btn-outline-danger" onclick="delItem(${r.id})"><i class="bi bi-trash"></i></button></div></div>`).join('');}
function renderPagination(j){const p=document.getElementById('pagination');p.innerHTML=`<span class="text-muted small">عرض ${j.from||0}-${j.to||0} من ${j.total||0}</span><div class="btn-group btn-group-sm"><button class="btn btn-outline-success" ${currentPage<=1?'disabled':''} onclick="goPage(${currentPage-1})"><i class="bi bi-chevron-right"></i></button><span class="btn btn-success disabled">${currentPage}/${lastPage}</span><button class="btn btn-outline-success" ${currentPage>=lastPage?'disabled':''} onclick="goPage(${currentPage+1})"><i class="bi bi-chevron-left"></i></button></div>`;}
function goPage(p){currentPage=p;loadData();}
function openAddModal(){document.getElementById('editId').value='';document.getElementById('modalTitle').textContent='إضافة مصروف';document.getElementById('fCategory').value='';document.getElementById('fAmount').value='';document.getElementById('fPaymentMethod').value='cash';document.getElementById('fDate').value=new Date().toISOString().substring(0,10);document.getElementById('fDescription').value='';new bootstrap.Modal(document.getElementById('itemModal')).show();}
async function editItem(id){try{const r=await fetch(`${API}/${id}`,{headers:{Accept:'application/json'}});const d=await r.json();document.getElementById('editId').value=d.id;document.getElementById('modalTitle').textContent='تعديل مصروف';document.getElementById('fCategory').value=d.category||'';document.getElementById('fAmount').value=d.amount||'';document.getElementById('fPaymentMethod').value=d.payment_method||'cash';document.getElementById('fDate').value=d.expense_date||'';document.getElementById('fDescription').value=d.description||'';new bootstrap.Modal(document.getElementById('itemModal')).show();}catch(e){showToast('خطأ','حدث خطأ','danger');}}
async function saveItem(){const id=document.getElementById('editId').value;const d={category:document.getElementById('fCategory').value,amount:document.getElementById('fAmount').value,payment_method:document.getElementById('fPaymentMethod').value,expense_date:document.getElementById('fDate').value,description:document.getElementById('fDescription').value};if(!d.category||!d.amount)return showToast('تنبيه','التصنيف والمبلغ مطلوبان','warning');const btn=document.getElementById('saveBtn');btn.classList.add('btn-loading');btn.disabled=true;try{const o={method:id?'PUT':'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF_TOKEN,Accept:'application/json'},body:JSON.stringify(d)};const r=await fetch(id?`${API}/${id}`:API,o);const j=await r.json();if(j.id||j.success){bootstrap.Modal.getInstance(document.getElementById('itemModal')).hide();loadData();showToast('نجاح',id?'تم التعديل':'تم الإضافة','success');}else showToast('خطأ',j.message||'حدث خطأ','danger');}catch(e){showToast('خطأ','خطأ في الاتصال','danger');}btn.classList.remove('btn-loading');btn.disabled=false;}
async function delItem(id){if(!confirmDelete())return;try{await fetch(`${API}/${id}`,{method:'DELETE',headers:{'X-CSRF-TOKEN':CSRF_TOKEN,Accept:'application/json'}});loadData();showToast('نجاح','تم الحذف','success');}catch(e){showToast('خطأ','حدث خطأ','danger');}}
async function exportData(){try{const r=await fetch(`${API}?per_page=1000`,{headers:{Accept:'application/json'}});const j=await r.json();exportCsv(j.data||[],'expenses.csv');}catch(e){showToast('خطأ','خطأ في التصدير','danger');}}
loadData();
</script>
@endsection