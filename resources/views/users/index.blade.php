@extends('layouts.app')
@section('title', 'المستخدمين')
@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="bi bi-people-fill text-success"></i> المستخدمين</h2>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-secondary btn-sm" onclick="exportData()"><i class="bi bi-download"></i> تصدير</button>
            <button class="btn btn-success" onclick="openAddModal()"><i class="bi bi-plus-lg"></i> إضافة مستخدم</button>
        </div>
    </div>
    <div class="card shadow-sm">
        <div class="card-body">
            <div class="d-flex gap-2 mb-3 flex-wrap">
                <input type="text" class="form-control form-control-sm" style="max-width:250px" placeholder="بحث بالاسم..." id="searchInput" oninput="debounceSearch()">
                <select class="form-select form-select-sm" style="max-width:150px" id="roleFilter" onchange="loadData()">
                    <option value="">كل الأدوار</option>
                    <option value="admin">مدير</option>
                    <option value="cashier">كاشير</option>
                    <option value="agent">وكيل</option>
                    <option value="viewer">مشاهد</option>
                </select>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead><tr><th>#</th><th>الاسم</th><th>اسم المستخدم</th><th>الدور</th><th>الهاتف</th><th>الحالة</th><th>إجراءات</th></tr></thead>
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
            <div class="modal-header"><h5 class="modal-title" id="modalTitle">إضافة مستخدم</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <input type="hidden" id="editId">
                <div class="mb-3"><label class="form-label">الاسم الكامل *</label><input type="text" class="form-control" id="fFullName"></div>
                <div class="mb-3"><label class="form-label">اسم المستخدم *</label><input type="text" class="form-control" id="fUsername"></div>
                <div class="mb-3"><label class="form-label">البريد</label><input type="email" class="form-control" id="fEmail"></div>
                <div class="mb-3"><label class="form-label">الهاتف</label><input type="tel" class="form-control" id="fPhone"></div>
                <div class="mb-3"><label class="form-label">الدور *</label>
                    <select class="form-select" id="fRole">
                        <option value="viewer">مشاهد</option>
                        <option value="cashier">كاشير</option>
                        <option value="agent">وكيل</option>
                        <option value="admin">مدير</option>
                    </select>
                </div>
                <div class="mb-3"><label class="form-label">الوكيل المرتبط</label>
                    <select class="form-select" id="fAgentId"><option value="">-- لا يوجد --</option></select>
                </div>
                <div id="passwordFields">
                    <div class="mb-3"><label class="form-label">كلمة المرور *</label><input type="password" class="form-control" id="fPassword"></div>
                    <div class="mb-3"><label class="form-label">تأكيد كلمة المرور</label><input type="password" class="form-control" id="fPasswordConfirm"></div>
                </div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button><button type="button" class="btn btn-success" onclick="saveItem()" id="saveBtn">حفظ</button></div>
        </div>
    </div>
</div>
@include('components.confirmation-modal')
<script>
const API='/api/v1/users';let currentPage=1,lastPage=1,searchTimer;
const roleLabels={admin:'مدير',cashier:'كاشير',agent:'وكيل',viewer:'مشاهد'};
const roleBadges={admin:'danger',cashier:'primary',agent:'warning',viewer:'secondary'};
function debounceSearch(){clearTimeout(searchTimer);searchTimer=setTimeout(()=>{currentPage=1;loadData()},400);}
async function loadData(){const s=document.getElementById('searchInput').value,role=document.getElementById('roleFilter').value;let u=`${API}?per_page=20&page=${currentPage}`;if(s)u+=`&search=${encodeURIComponent(s)}`;if(role)u+=`&role=${role}`;try{const r=await fetch(u,{headers:{Accept:'application/json'}});const j=await r.json();renderTable(j.data||[]);lastPage=j.last_page||1;renderPagination(j);}catch(e){document.getElementById('rows').innerHTML='<tr><td colspan="7" class="text-center text-danger">خطأ في تحميل البيانات</td></tr>';}}
function renderTable(rows){document.getElementById('rows').innerHTML=rows.length?rows.map(r=>`<tr>
<td>${r.id}</td><td class="fw-semibold">${r.full_name||r.name||'-'}</td><td>${r.username||'-'}</td>
<td><span class="badge bg-${roleBadges[r.role]||'secondary'}">${roleLabels[r.role]||r.role||'-'}</span></td>
<td><span dir="ltr">${r.phone||'-'}</span></td>
<td>${statusBadge(r.status||'active')}</td>
<td><div class="btn-group btn-group-sm">
<button class="btn btn-outline-primary" onclick="editItem(${r.id})"><i class="bi bi-pencil"></i></button>
<button class="btn btn-outline-danger" onclick="delItem(${r.id})"><i class="bi bi-trash"></i></button>
</div></td></tr>`).join(''):'<tr><td colspan="7" class="text-center text-muted py-4">لا توجد بيانات</td></tr>';
document.getElementById('mobileCards').innerHTML=rows.map(r=>`<div class="mobile-card"><div class="mobile-card-title"><span>${r.full_name||r.username||'-'}</span><span class="badge bg-${roleBadges[r.role]||'secondary'}">${roleLabels[r.role]||r.role||'-'}</span></div><div class="mobile-card-row"><span>المستخدم</span><span dir="ltr">${r.username||'-'}</span></div><div class="d-flex gap-1 mt-2"><button class="btn btn-sm btn-outline-primary" onclick="editItem(${r.id})"><i class="bi bi-pencil"></i></button><button class="btn btn-sm btn-outline-danger" onclick="delItem(${r.id})"><i class="bi bi-trash"></i></button></div></div>`).join('');}
function renderPagination(j){const p=document.getElementById('pagination');p.innerHTML=`<span class="text-muted small">عرض ${j.from||0}-${j.to||0} من ${j.total||0}</span><div class="btn-group btn-group-sm"><button class="btn btn-outline-success" ${currentPage<=1?'disabled':''} onclick="goPage(${currentPage-1})"><i class="bi bi-chevron-right"></i></button><span class="btn btn-success disabled">${currentPage}/${lastPage}</span><button class="btn btn-outline-success" ${currentPage>=lastPage?'disabled':''} onclick="goPage(${currentPage+1})"><i class="bi bi-chevron-left"></i></button></div>`;}
function goPage(p){currentPage=p;loadData();}
async function loadAgents(){try{const r=await fetch('/api/v1/agents?per_page=100&status=active',{headers:{Accept:'application/json'}});const j=await r.json();document.getElementById('fAgentId').innerHTML='<option value="">-- لا يوجد --</option>'+(j.data||[]).map(a=>`<option value="${a.id}">${a.name}</option>`).join('');}catch(e){}}
function openAddModal(){document.getElementById('editId').value='';document.getElementById('modalTitle').textContent='إضافة مستخدم';['fFullName','fUsername','fEmail','fPhone','fPassword','fPasswordConfirm'].forEach(id=>document.getElementById(id).value='');document.getElementById('fRole').value='cashier';document.getElementById('fAgentId').value='';document.getElementById('passwordFields').style.display='block';loadAgents();new bootstrap.Modal(document.getElementById('itemModal')).show();}
async function editItem(id){try{loadAgents();const r=await fetch(`${API}/${id}`,{headers:{Accept:'application/json'}});const d=await r.json();document.getElementById('editId').value=d.id;document.getElementById('modalTitle').textContent='تعديل مستخدم';document.getElementById('fFullName').value=d.full_name||d.name||'';document.getElementById('fUsername').value=d.username||'';document.getElementById('fEmail').value=d.email||'';document.getElementById('fPhone').value=d.phone||'';document.getElementById('fRole').value=d.role||'viewer';document.getElementById('fAgentId').value=d.agent_id||'';document.getElementById('passwordFields').style.display='none';new bootstrap.Modal(document.getElementById('itemModal')).show();}catch(e){showToast('خطأ','حدث خطأ','danger');}}
async function saveItem(){const id=document.getElementById('editId').value;const d={full_name:document.getElementById('fFullName').value,username:document.getElementById('fUsername').value,email:document.getElementById('fEmail').value,phone:document.getElementById('fPhone').value,role:document.getElementById('fRole').value,agent_id:document.getElementById('fAgentId').value||null};if(!d.full_name||!d.username)return showToast('تنبيه','الاسم واسم المستخدم مطلوبان','warning');if(!id){d.password=document.getElementById('fPassword').value;if(!d.password)return showToast('تنبيه','كلمة المرور مطلوبة','warning');if(d.password!==document.getElementById('fPasswordConfirm').value)return showToast('تنبيه','كلمات المرور غير متطابقة','warning');}const btn=document.getElementById('saveBtn');btn.classList.add('btn-loading');btn.disabled=true;try{const o={method:id?'PUT':'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF_TOKEN,Accept:'application/json'},body:JSON.stringify(d)};const r=await fetch(id?`${API}/${id}`:API,o);const j=await r.json();if(j.id||j.success){bootstrap.Modal.getInstance(document.getElementById('itemModal')).hide();loadData();showToast('نجاح',id?'تم التعديل':'تم الإضافة','success');}else showToast('خطأ',j.message||'حدث خطأ','danger');}catch(e){showToast('خطأ','خطأ في الاتصال','danger');}btn.classList.remove('btn-loading');btn.disabled=false;}
async function delItem(id){if(!confirmDelete())return;try{await fetch(`${API}/${id}`,{method:'DELETE',headers:{'X-CSRF-TOKEN':CSRF_TOKEN,Accept:'application/json'}});loadData();showToast('نجاح','تم الحذف','success');}catch(e){showToast('خطأ','حدث خطأ','danger');}}
async function exportData(){try{const r=await fetch(`${API}?per_page=1000`,{headers:{Accept:'application/json'}});const j=await r.json();exportCsv(j.data||[],'users.csv');}catch(e){showToast('خطأ','خطأ في التصدير','danger');}}
loadData();
</script>
@endsection