@extends('layouts.app')
@section('title', 'الوكلاء')
@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="bi bi-person-badge text-success"></i> الوكلاء</h2>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-secondary btn-sm" onclick="exportData()"><i class="bi bi-download"></i> تصدير</button>
            <button class="btn btn-success" onclick="openAddModal()"><i class="bi bi-plus-lg"></i> إضافة وكيل</button>
        </div>
    </div>
    <div class="card shadow-sm">
        <div class="card-body">
            <div class="d-flex gap-2 mb-3 flex-wrap">
                <input type="text" class="form-control form-control-sm" style="max-width:250px" placeholder="بحث بالاسم أو الرقم..." id="searchInput" oninput="debounceSearch()">
                <select class="form-select form-select-sm" style="max-width:150px" id="statusFilter" onchange="loadData()">
                    <option value="">كل الحالات</option>
                    <option value="active">نشط</option>
                    <option value="inactive">متوقف</option>
                </select>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead><tr><th>#</th><th>الاسم</th><th>الهاتف</th><th>المنطقة</th><th>الرصيد</th><th>نسبة العمولة</th><th>الحالة</th><th>إجراءات</th></tr></thead>
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
            <div class="modal-header"><h5 class="modal-title" id="modalTitle">إضافة وكيل</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <input type="hidden" id="editId">
                <div class="mb-3"><label class="form-label">الاسم *</label><input type="text" class="form-control" id="fName"></div>
                <div class="mb-3"><label class="form-label">الهاتف</label><input type="tel" class="form-control" id="fPhone" placeholder="777123456"></div>
                <div class="mb-3"><label class="form-label">المنطقة</label><input type="text" class="form-control" id="fArea" placeholder="مثلاً: صنعاء"></div>
                <div class="mb-3"><label class="form-label">نسبة العمولة (%)</label><input type="number" class="form-control" id="fCommission" step="0.1" min="0" max="100" value="0"></div>
                <div class="mb-3"><label class="form-label">ملاحظات</label><textarea class="form-control" id="fNotes" rows="2"></textarea></div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button><button type="button" class="btn btn-success" onclick="saveItem()" id="saveBtn">حفظ</button></div>
        </div>
    </div>
</div>
@include('components.confirmation-modal')
<script>
const API='/api/v1/agents';let currentPage=1,lastPage=1,searchTimer;
function debounceSearch(){clearTimeout(searchTimer);searchTimer=setTimeout(()=>{currentPage=1;loadData()},400);}
async function loadData(){const s=document.getElementById('searchInput').value,st=document.getElementById('statusFilter').value;let u=`${API}?per_page=20&page=${currentPage}`;if(s)u+=`&search=${encodeURIComponent(s)}`;if(st)u+=`&status=${st}`;try{const r=await fetch(u,{headers:{Accept:'application/json'}});const j=await r.json();renderTable(j.data||[]);lastPage=j.last_page||1;renderPagination(j);}catch(e){document.getElementById('rows').innerHTML='<tr><td colspan="8" class="text-center text-danger">خطأ في تحميل البيانات</td></tr>';}}
function renderTable(rows){document.getElementById('rows').innerHTML=rows.length?rows.map(r=>`<tr>
<td>${r.id}</td><td class="fw-semibold">${r.name}</td><td><span dir="ltr">${r.phone||'-'}</span></td><td>${r.area||'-'}</td>
<td><span class="${(r.balance||0)<0?'text-danger':'text-success'}">${formatMoney(r.balance)}</span></td>
<td>${r.commission_rate||0}%</td>
<td>${statusBadge(r.status||'active')}</td>
<td><div class="btn-group btn-group-sm">
<button class="btn btn-outline-primary" onclick="editItem(${r.id})"><i class="bi bi-pencil"></i></button>
${r.phone?`<button class="btn btn-outline-success" onclick="waSend('${r.phone}')"><i class="bi bi-whatsapp"></i></button>`:''}
<button class="btn btn-outline-danger" onclick="delItem(${r.id})"><i class="bi bi-trash"></i></button>
</div></td></tr>`).join(''):'<tr><td colspan="8" class="text-center text-muted py-4">لا توجد بيانات</td></tr>';
document.getElementById('mobileCards').innerHTML=rows.map(r=>`<div class="mobile-card"><div class="mobile-card-title"><span>${r.name}</span>${statusBadge(r.status||'active')}</div><div class="mobile-card-row"><span>الهاتف</span><span dir="ltr">${r.phone||'-'}</span></div><div class="mobile-card-row"><span>الرصيد</span><span>${formatMoney(r.balance)}</span></div><div class="d-flex gap-1 mt-2"><button class="btn btn-sm btn-outline-primary" onclick="editItem(${r.id})"><i class="bi bi-pencil"></i></button><button class="btn btn-sm btn-outline-danger" onclick="delItem(${r.id})"><i class="bi bi-trash"></i></button></div></div>`).join('');}
function renderPagination(j){const p=document.getElementById('pagination');p.innerHTML=`<span class="text-muted small">عرض ${j.from||0}-${j.to||0} من ${j.total||0}</span><div class="btn-group btn-group-sm"><button class="btn btn-outline-success" ${currentPage<=1?'disabled':''} onclick="goPage(${currentPage-1})"><i class="bi bi-chevron-right"></i></button><span class="btn btn-success disabled">${currentPage}/${lastPage}</span><button class="btn btn-outline-success" ${currentPage>=lastPage?'disabled':''} onclick="goPage(${currentPage+1})"><i class="bi bi-chevron-left"></i></button></div>`;}
function goPage(p){currentPage=p;loadData();}
function openAddModal(){document.getElementById('editId').value='';document.getElementById('modalTitle').textContent='إضافة وكيل';['fName','fPhone','fArea','fNotes'].forEach(id=>document.getElementById(id).value='');document.getElementById('fCommission').value='0';new bootstrap.Modal(document.getElementById('itemModal')).show();}
async function editItem(id){try{const r=await fetch(`${API}/${id}`,{headers:{Accept:'application/json'}});const d=await r.json();document.getElementById('editId').value=d.id;document.getElementById('modalTitle').textContent='تعديل وكيل';document.getElementById('fName').value=d.name;document.getElementById('fPhone').value=d.phone||'';document.getElementById('fArea').value=d.area||'';document.getElementById('fCommission').value=d.commission_rate||0;document.getElementById('fNotes').value=d.notes||'';new bootstrap.Modal(document.getElementById('itemModal')).show();}catch(e){showToast('خطأ','حدث خطأ','danger');}}
async function saveItem(){const id=document.getElementById('editId').value;const d={name:document.getElementById('fName').value,phone:document.getElementById('fPhone').value,area:document.getElementById('fArea').value,commission_rate:document.getElementById('fCommission').value,notes:document.getElementById('fNotes').value};if(!d.name)return showToast('تنبيه','اسم الوكيل مطلوب','warning');const btn=document.getElementById('saveBtn');btn.classList.add('btn-loading');btn.disabled=true;try{const o={method:id?'PUT':'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF_TOKEN,Accept:'application/json'},body:JSON.stringify(d)};const r=await fetch(id?`${API}/${id}`:API,o);const j=await r.json();if(j.id||j.success){bootstrap.Modal.getInstance(document.getElementById('itemModal')).hide();loadData();showToast('نجاح',id?'تم التعديل':'تم الإضافة','success');}else showToast('خطأ',j.message||'حدث خطأ','danger');}catch(e){showToast('خطأ','خطأ في الاتصال','danger');}btn.classList.remove('btn-loading');btn.disabled=false;}
async function delItem(id){if(!confirmDelete())return;try{await fetch(`${API}/${id}`,{method:'DELETE',headers:{'X-CSRF-TOKEN':CSRF_TOKEN,Accept:'application/json'}});loadData();showToast('نجاح','تم الحذف','success');}catch(e){showToast('خطأ','حدث خطأ','danger');}}
async function exportData(){try{const r=await fetch(`${API}?per_page=1000`,{headers:{Accept:'application/json'}});const j=await r.json();exportCsv(j.data||[],'agents.csv');}catch(e){showToast('خطأ','خطأ في التصدير','danger');}}
function waSend(phone){let p=phone.replace(/[^0-9]/g,'');if(p.length===9)p='967'+p;window.open('https://wa.me/'+p,'_blank');}
loadData();
</script>
@endsection