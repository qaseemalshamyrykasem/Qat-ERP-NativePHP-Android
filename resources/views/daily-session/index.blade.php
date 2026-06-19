@extends('layouts.app')
@section('title', 'الجلسات اليومية')
@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="bi bi-calendar-check text-success"></i> الجلسات اليومية</h2>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-secondary btn-sm" onclick="exportData()"><i class="bi bi-download"></i> تصدير</button>
            <button class="btn btn-success" onclick="openSession()"><i class="bi bi-play-circle"></i> فتح جلسة جديدة</button>
        </div>
    </div>
    <!-- Current Session Card -->
    <div class="card shadow-sm mb-4 border-success" id="currentSessionCard" style="display:none">
        <div class="card-header bg-success text-white"><i class="bi bi-broadcast"></i> الجلسة الحالية</div>
        <div class="card-body" id="currentSession"></div>
    </div>
    <div class="card shadow-sm">
        <div class="card-body">
            <div class="d-flex gap-2 mb-3 flex-wrap">
                <input type="date" class="form-control form-control-sm" style="max-width:180px" id="dateFrom" onchange="loadData()">
                <input type="date" class="form-control form-control-sm" style="max-width:180px" id="dateTo" onchange="loadData()">
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead><tr><th>#</th><th>التاريخ</th><th>الرصيد الافتتاحي</th><th>المبيعات</th><th>المصروفات</th><th>صافي الربح</th><th>المتوقع</th><th>الفعلي</th><th>الحالة</th><th>إجراءات</th></tr></thead>
                    <tbody id="rows"></tbody>
                </table>
            </div>
            <div class="mobile-cards" id="mobileCards"></div>
            <div class="d-flex justify-content-between align-items-center mt-3" id="pagination"></div>
        </div>
    </div>
</div>
<!-- Open Session Modal -->
<div class="modal fade" id="openModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">فتح جلسة يومية</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="mb-3"><label class="form-label">الرصيد الافتتاحي</label><input type="number" class="form-control" id="fOpeningBalance" step="0.01" value="0"></div>
                <div class="mb-3"><label class="form-label">ملاحظات</label><textarea class="form-control" id="fNotes" rows="2"></textarea></div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button><button type="button" class="btn btn-success" onclick="submitOpen()" id="openBtn">فتح الجلسة</button></div>
        </div>
    </div>
</div>
<div class="modal fade" id="closeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">إغلاق الجلسة</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <input type="hidden" id="closeId">
                <div class="mb-3"><label class="form-label">المبلغ الفعلي في الصندوق *</label><input type="number" class="form-control" id="fActualAmount" step="0.01"></div>
                <div class="mb-3"><label class="form-label">ملاحظات الإغلاق</label><textarea class="form-control" id="fCloseNotes" rows="2"></textarea></div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button><button type="button" class="btn btn-danger" onclick="submitClose()">إغلاق الجلسة</button></div>
        </div>
    </div>
</div>
@include('components.confirmation-modal')
<script>
const API='/api/v1/daily-sessions';let currentPage=1,lastPage=1;
async function loadData(){const df=document.getElementById('dateFrom').value,dt=document.getElementById('dateTo').value;let u=`${API}?per_page=20&page=${currentPage}`;if(df)u+=`&date_from=${df}`;if(dt)u+=`&date_to=${dt}`;try{const r=await fetch(u,{headers:{Accept:'application/json'}});const j=await r.json();renderTable(j.data||[]);lastPage=j.last_page||1;renderPagination(j);loadCurrentSession();}catch(e){document.getElementById('rows').innerHTML='<tr><td colspan="10" class="text-center text-danger">خطأ في تحميل البيانات</td></tr>';}}
function sessionBadge(s){return s==='open'?'<span class="badge bg-success">مفتوحة</span>':'<span class="badge bg-secondary">مغلقة</span>';}
function renderTable(rows){document.getElementById('rows').innerHTML=rows.length?rows.map(r=>{const net=((r.total_sales||0)-(r.total_expenses||0));return `<tr>
<td>${r.id}</td><td>${r.session_date||r.created_at?.substring(0,10)||'-'}</td>
<td>${formatMoney(r.opening_balance)}</td><td class="text-success">${formatMoney(r.total_sales)}</td><td class="text-danger">${formatMoney(r.total_expenses)}</td>
<td class="fw-bold ${net>=0?'text-success':'text-danger'}">${formatMoney(net)}</td>
<td>${formatMoney(r.expected_amount)}</td><td>${formatMoney(r.actual_amount)}</td>
<td>${sessionBadge(r.status)}</td>
<td><div class="btn-group btn-group-sm">
${r.status==='open'?`<button class="btn btn-outline-danger" onclick="openCloseModal(${r.id})"><i class="bi bi-stop-circle"></i> إغلاق</button>`:''}
</div></td></tr>`;}).join(''):'<tr><td colspan="10" class="text-center text-muted py-4">لا توجد جلسات</td></tr>';
document.getElementById('mobileCards').innerHTML=rows.map(r=>`<div class="mobile-card"><div class="mobile-card-title"><span>${r.session_date||'-'}</span>${sessionBadge(r.status)}</div><div class="mobile-card-row"><span>المبيعات</span><span class="text-success">${formatMoney(r.total_sales)}</span></div><div class="mobile-card-row"><span>المصروفات</span><span class="text-danger">${formatMoney(r.total_expenses)}</span></div></div>`).join('');}
function renderPagination(j){const p=document.getElementById('pagination');p.innerHTML=`<span class="text-muted small">عرض ${j.from||0}-${j.to||0} من ${j.total||0}</span><div class="btn-group btn-group-sm"><button class="btn btn-outline-success" ${currentPage<=1?'disabled':''} onclick="goPage(${currentPage-1})"><i class="bi bi-chevron-right"></i></button><span class="btn btn-success disabled">${currentPage}/${lastPage}</span><button class="btn btn-outline-success" ${currentPage>=lastPage?'disabled':''} onclick="goPage(${currentPage+1})"><i class="bi bi-chevron-left"></i></button></div>`;}
function goPage(p){currentPage=p;loadData();}
async function loadCurrentSession(){try{const r=await fetch(`${API}?status=open&per_page=1`,{headers:{Accept:'application/json'}});const j=await r.json();const rows=j.data||[];if(rows.length){document.getElementById('currentSessionCard').style.display='block';const s=rows[0];document.getElementById('currentSession').innerHTML=`<div class="row text-center"><div class="col-3"><h6 class="text-muted">الافتتاحي</h6><h5>${formatMoney(s.opening_balance)}</h5></div><div class="col-3"><h6 class="text-muted">المبيعات</h6><h5 class="text-success">${formatMoney(s.total_sales)}</h5></div><div class="col-3"><h6 class="text-muted">المصروفات</h6><h5 class="text-danger">${formatMoney(s.total_expenses)}</h5></div><div class="col-3"><h6 class="text-muted">الصافي</h6><h5 class="fw-bold">${formatMoney((s.total_sales||0)-(s.total_expenses||0))}</h5></div></div>`;}else{document.getElementById('currentSessionCard').style.display='none';}}catch(e){}}
function openSession(){document.getElementById('fOpeningBalance').value='0';document.getElementById('fNotes').value='';new bootstrap.Modal(document.getElementById('openModal')).show();}
async function submitOpen(){const d={opening_balance:document.getElementById('fOpeningBalance').value,notes:document.getElementById('fNotes').value};const btn=document.getElementById('openBtn');btn.classList.add('btn-loading');btn.disabled=true;try{const o={method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF_TOKEN,Accept:'application/json'},body:JSON.stringify(d)};const r=await fetch(`${API}/open`,o);const j=await r.json();if(j.id||j.success){bootstrap.Modal.getInstance(document.getElementById('openModal')).hide();loadData();showToast('نجاح','تم فتح الجلسة','success');}else showToast('خطأ',j.message||'حدث خطأ','danger');}catch(e){showToast('خطأ','خطأ في الاتصال','danger');}btn.classList.remove('btn-loading');btn.disabled=false;}
function openCloseModal(id){document.getElementById('closeId').value=id;document.getElementById('fActualAmount').value='';document.getElementById('fCloseNotes').value='';new bootstrap.Modal(document.getElementById('closeModal')).show();}
async function submitClose(){const id=document.getElementById('closeId').value;const d={actual_amount:document.getElementById('fActualAmount').value,notes:document.getElementById('fCloseNotes').value};if(!d.actual_amount)return showToast('تنبيه','أدخل المبلغ الفعلي','warning');try{const o={method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF_TOKEN,Accept:'application/json'},body:JSON.stringify(d)};const r=await fetch(`${API}/${id}/close`,o);const j=await r.json();if(j.success||j.id){bootstrap.Modal.getInstance(document.getElementById('closeModal')).hide();loadData();showToast('نجاح','تم إغلاق الجلسة','success');}else showToast('خطأ',j.message||'حدث خطأ','danger');}catch(e){showToast('خطأ','خطأ في الاتصال','danger');}}
async function exportData(){try{const r=await fetch(`${API}?per_page=1000`,{headers:{Accept:'application/json'}});const j=await r.json();exportCsv(j.data||[],'daily-sessions.csv');}catch(e){showToast('خطأ','خطأ في التصدير','danger');}}
loadData();
</script>
@endsection