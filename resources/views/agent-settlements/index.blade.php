@extends('layouts.app')
@section('title', 'تسويات الوكلاء')
@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="bi bi-calculator text-success"></i> تسويات الوكلاء</h2>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-secondary btn-sm" onclick="exportData()"><i class="bi bi-download"></i> تصدير</button>
            <button class="btn btn-success" onclick="openCalcModal()"><i class="bi bi-plus-lg"></i> تسوية جديدة</button>
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
                    <thead><tr><th>#</th><th>الرقم</th><th>الوكيل</th><th>التاريخ</th><th>إجمالي المبيعات</th><th>العمولة</th><th>صافي</th><th>إجراءات</th></tr></thead>
                    <tbody id="rows"></tbody>
                </table>
            </div>
            <div class="mobile-cards" id="mobileCards"></div>
            <div class="d-flex justify-content-between align-items-center mt-3" id="pagination"></div>
        </div>
    </div>
</div>
<!-- Calculate Modal -->
<div class="modal fade" id="calcModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">حساب تسوية</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="mb-3"><label class="form-label">الوكيل *</label>
                    <select class="form-select" id="fAgentId">
                        <option value="">اختر الوكيل</option>
                    </select>
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-6"><label class="form-label">من تاريخ</label><input type="date" class="form-control" id="fDateFrom"></div>
                    <div class="col-6"><label class="form-label">إلى تاريخ</label><input type="date" class="form-control" id="fDateTo"></div>
                </div>
                <button class="btn btn-outline-success w-100" onclick="calculateSettlement()"><i class="bi bi-calculator"></i> حساب</button>
                <div id="calcResult" class="mt-3" style="display:none"></div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button></div>
        </div>
    </div>
</div>
@include('components.confirmation-modal')
<script>
const API='/api/v1/agent-settlements';let currentPage=1,lastPage=1,searchTimer;
function debounceSearch(){clearTimeout(searchTimer);searchTimer=setTimeout(()=>{currentPage=1;loadData()},400);}
async function loadData(){const s=document.getElementById('searchInput').value,df=document.getElementById('dateFrom').value,dt=document.getElementById('dateTo').value;let u=`${API}?per_page=20&page=${currentPage}`;if(s)u+=`&search=${encodeURIComponent(s)}`;if(df)u+=`&date_from=${df}`;if(dt)u+=`&date_to=${dt}`;try{const r=await fetch(u,{headers:{Accept:'application/json'}});const j=await r.json();renderTable(j.data||[]);lastPage=j.last_page||1;renderPagination(j);}catch(e){document.getElementById('rows').innerHTML='<tr><td colspan="8" class="text-center text-danger">خطأ في تحميل البيانات</td></tr>';}}
function renderTable(rows){document.getElementById('rows').innerHTML=rows.length?rows.map(r=>`<tr>
<td>${r.id}</td><td class="fw-semibold">${r.settlement_no||'#'+r.id}</td>
<td>${r.agent_name||r.agent?.name||'-'}</td><td>${r.settlement_date||r.created_at?.substring(0,10)||'-'}</td>
<td>${formatMoney(r.total_sales)}</td><td>${formatMoney(r.commission)}</td>
<td class="fw-bold">${formatMoney(r.net_amount)}</td>
<td><div class="btn-group btn-group-sm">
<button class="btn btn-outline-danger" onclick="delItem(${r.id})"><i class="bi bi-trash"></i></button>
</div></td></tr>`).join(''):'<tr><td colspan="8" class="text-center text-muted py-4">لا توجد بيانات</td></tr>';
document.getElementById('mobileCards').innerHTML=rows.map(r=>`<div class="mobile-card"><div class="mobile-card-title"><span>${r.agent_name||'-'}</span></div><div class="mobile-card-row"><span>المبيعات</span><span>${formatMoney(r.total_sales)}</span></div><div class="mobile-card-row"><span>الصافي</span><span class="fw-bold">${formatMoney(r.net_amount)}</span></div><div class="d-flex gap-1 mt-2"><button class="btn btn-sm btn-outline-danger" onclick="delItem(${r.id})"><i class="bi bi-trash"></i></button></div></div>`).join('');}
function renderPagination(j){const p=document.getElementById('pagination');p.innerHTML=`<span class="text-muted small">عرض ${j.from||0}-${j.to||0} من ${j.total||0}</span><div class="btn-group btn-group-sm"><button class="btn btn-outline-success" ${currentPage<=1?'disabled':''} onclick="goPage(${currentPage-1})"><i class="bi bi-chevron-right"></i></button><span class="btn btn-success disabled">${currentPage}/${lastPage}</span><button class="btn btn-outline-success" ${currentPage>=lastPage?'disabled':''} onclick="goPage(${currentPage+1})"><i class="bi bi-chevron-left"></i></button></div>`;}
function goPage(p){currentPage=p;loadData();}
async function openCalcModal(){try{const r=await fetch('/api/v1/agents?per_page=100&status=active',{headers:{Accept:'application/json'}});const j=await r.json();const sel=document.getElementById('fAgentId');sel.innerHTML='<option value="">اختر الوكيل</option>'+(j.data||[]).map(a=>`<option value="${a.id}">${a.name}</option>`).join('');}catch(e){}const today=new Date().toISOString().substring(0,10);document.getElementById('fDateFrom').value=today.substring(0,8)+'01';document.getElementById('fDateTo').value=today;document.getElementById('calcResult').style.display='none';new bootstrap.Modal(document.getElementById('calcModal')).show();}
async function calculateSettlement(){const agent_id=document.getElementById('fAgentId').value;if(!agent_id)return showToast('تنبيه','اختر الوكيل','warning');const d={agent_id,date_from:document.getElementById('fDateFrom').value,date_to:document.getElementById('fDateTo').value};try{const o={method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF_TOKEN,Accept:'application/json'},body:JSON.stringify(d)};const r=await fetch(`${API}/calculate`,o);const j=await r.json();const box=document.getElementById('calcResult');box.style.display='block';box.innerHTML=`<div class="alert alert-success"><h6>نتيجة الحساب</h6><div class="row"><div class="col-6">المبيعات: <strong>${formatMoney(j.total_sales)}</strong></div><div class="col-6">العمولة: <strong>${formatMoney(j.commission)}</strong></div><div class="col-6">المردودات: <strong>${formatMoney(j.returns)}</strong></div><div class="col-6">الصافي: <strong class="text-success">${formatMoney(j.net_amount)}</strong></div></div><button class="btn btn-success w-100 mt-2" onclick="saveSettlement()"><i class="bi bi-save"></i> حفظ التسوية</button></div>`;}catch(e){showToast('خطأ','خطأ في الحساب','danger');}}
async function saveSettlement(){const agent_id=document.getElementById('fAgentId').value;const d={agent_id,date_from:document.getElementById('fDateFrom').value,date_to:document.getElementById('fDateTo').value};try{const o={method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF_TOKEN,Accept:'application/json'},body:JSON.stringify(d)};const r=await fetch(`${API}/save`,o);const j=await r.json();if(j.id||j.success){bootstrap.Modal.getInstance(document.getElementById('calcModal')).hide();loadData();showToast('نجاح','تم حفظ التسوية','success');}else showToast('خطأ',j.message||'حدث خطأ','danger');}catch(e){showToast('خطأ','خطأ في الاتصال','danger');}}
async function delItem(id){if(!confirmDelete())return;try{await fetch(`${API}/${id}`,{method:'DELETE',headers:{'X-CSRF-TOKEN':CSRF_TOKEN,Accept:'application/json'}});loadData();showToast('نجاح','تم الحذف','success');}catch(e){showToast('خطأ','حدث خطأ','danger');}}
async function exportData(){try{const r=await fetch(`${API}?per_page=1000`,{headers:{Accept:'application/json'}});const j=await r.json();exportCsv(j.data||[],'agent-settlements.csv');}catch(e){showToast('خطأ','خطأ في التصدير','danger');}}
loadData();
</script>
@endsection