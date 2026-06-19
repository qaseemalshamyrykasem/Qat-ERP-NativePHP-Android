@extends('layouts.app')
@section('title', 'التحويلات')
@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="bi bi-arrow-repeat text-success"></i> التحويلات</h2>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-secondary btn-sm" onclick="exportData()"><i class="bi bi-download"></i> تصدير</button>
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
                    <thead><tr><th>#</th><th>الرقم</th><th>من حساب</th><th>إلى حساب</th><th>المبلغ</th><th>سعر الصرف</th><th>التاريخ</th><th>إجراءات</th></tr></thead>
                    <tbody id="rows"></tbody>
                </table>
            </div>
            <div class="mobile-cards" id="mobileCards"></div>
            <div class="d-flex justify-content-between align-items-center mt-3" id="pagination"></div>
        </div>
    </div>
</div>
@include('components.confirmation-modal')
<script>
const API='/api/v1/transfers';let currentPage=1,lastPage=1,searchTimer;
function debounceSearch(){clearTimeout(searchTimer);searchTimer=setTimeout(()=>{currentPage=1;loadData()},400);}
async function loadData(){const s=document.getElementById('searchInput').value,df=document.getElementById('dateFrom').value,dt=document.getElementById('dateTo').value;let u=`${API}?per_page=20&page=${currentPage}`;if(s)u+=`&search=${encodeURIComponent(s)}`;if(df)u+=`&date_from=${df}`;if(dt)u+=`&date_to=${dt}`;try{const r=await fetch(u,{headers:{Accept:'application/json'}});const j=await r.json();renderTable(j.data||[]);lastPage=j.last_page||1;renderPagination(j);}catch(e){document.getElementById('rows').innerHTML='<tr><td colspan="8" class="text-center text-danger">خطأ في تحميل البيانات</td></tr>';}}
function renderTable(rows){document.getElementById('rows').innerHTML=rows.length?rows.map(r=>`<tr>
<td>${r.id}</td><td class="fw-semibold">${r.transfer_no||'#'+r.id}</td>
<td>${r.from_account_name||r.from_account?.name||'-'}</td>
<td>${r.to_account_name||r.to_account?.name||'-'}</td>
<td class="fw-semibold">${formatMoney(r.amount)}</td><td>${r.exchange_rate||'-'}</td>
<td>${r.transfer_date||r.created_at?.substring(0,10)||'-'}</td>
<td><div class="btn-group btn-group-sm">
<button class="btn btn-outline-danger" onclick="delItem(${r.id})"><i class="bi bi-trash"></i></button>
</div></td></tr>`).join(''):'<tr><td colspan="8" class="text-center text-muted py-4">لا توجد بيانات</td></tr>';
document.getElementById('mobileCards').innerHTML=rows.map(r=>`<div class="mobile-card"><div class="mobile-card-title"><span>${r.transfer_no||'#'+r.id}</span></div><div class="mobile-card-row"><span>من</span><span>${r.from_account_name||'-'}</span></div><div class="mobile-card-row"><span>إلى</span><span>${r.to_account_name||'-'}</span></div><div class="mobile-card-row"><span>المبلغ</span><span class="fw-bold">${formatMoney(r.amount)}</span></div><div class="d-flex gap-1 mt-2"><button class="btn btn-sm btn-outline-danger" onclick="delItem(${r.id})"><i class="bi bi-trash"></i></button></div></div>`).join('');}
function renderPagination(j){const p=document.getElementById('pagination');p.innerHTML=`<span class="text-muted small">عرض ${j.from||0}-${j.to||0} من ${j.total||0}</span><div class="btn-group btn-group-sm"><button class="btn btn-outline-success" ${currentPage<=1?'disabled':''} onclick="goPage(${currentPage-1})"><i class="bi bi-chevron-right"></i></button><span class="btn btn-success disabled">${currentPage}/${lastPage}</span><button class="btn btn-outline-success" ${currentPage>=lastPage?'disabled':''} onclick="goPage(${currentPage+1})"><i class="bi bi-chevron-left"></i></button></div>`;}
function goPage(p){currentPage=p;loadData();}
async function delItem(id){if(!confirmDelete())return;try{await fetch(`${API}/${id}`,{method:'DELETE',headers:{'X-CSRF-TOKEN':CSRF_TOKEN,Accept:'application/json'}});loadData();showToast('نجاح','تم الحذف','success');}catch(e){showToast('خطأ','حدث خطأ','danger');}}
async function exportData(){try{const r=await fetch(`${API}?per_page=1000`,{headers:{Accept:'application/json'}});const j=await r.json();exportCsv(j.data||[],'transfers.csv');}catch(e){showToast('خطأ','خطأ في التصدير','danger');}}
loadData();
</script>
@endsection