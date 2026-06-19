@extends('layouts.app')
@section('title', 'التوزيعات')
@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="bi bi-arrow-left-right text-success"></i> التوزيعات</h2>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-secondary btn-sm" onclick="exportData()"><i class="bi bi-download"></i> تصدير</button>
            <button class="btn btn-success" onclick="openAddModal()"><i class="bi bi-plus-lg"></i> إضافة توزيعة</button>
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
                    <thead><tr><th>#</th><th>الرقم</th><th>التاريخ</th><th>الوكيل</th><th>الإجمالي</th><th>إجراءات</th></tr></thead>
                    <tbody id="rows"></tbody>
                </table>
            </div>
            <div class="mobile-cards" id="mobileCards"></div>
            <div class="d-flex justify-content-between align-items-center mt-3" id="pagination"></div>
        </div>
    </div>
</div>
<div class="modal fade" id="viewModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">تفاصيل التوزيعة</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body" id="viewContent"></div>
        </div>
    </div>
</div>
@include('components.confirmation-modal')
<script>
const API='/api/v1/distributions';let currentPage=1,lastPage=1,searchTimer;
function debounceSearch(){clearTimeout(searchTimer);searchTimer=setTimeout(()=>{currentPage=1;loadData()},400);}
async function loadData(){const s=document.getElementById('searchInput').value,df=document.getElementById('dateFrom').value,dt=document.getElementById('dateTo').value;let u=`${API}?per_page=20&page=${currentPage}`;if(s)u+=`&search=${encodeURIComponent(s)}`;if(df)u+=`&date_from=${df}`;if(dt)u+=`&date_to=${dt}`;try{const r=await fetch(u,{headers:{Accept:'application/json'}});const j=await r.json();renderTable(j.data||[]);lastPage=j.last_page||1;renderPagination(j);}catch(e){document.getElementById('rows').innerHTML='<tr><td colspan="6" class="text-center text-danger">خطأ في تحميل البيانات</td></tr>';}}
function renderTable(rows){document.getElementById('rows').innerHTML=rows.length?rows.map(r=>`<tr>
<td>${r.id}</td><td class="fw-semibold">${r.distribution_no||'#'+r.id}</td><td>${r.distribution_date||r.created_at?.substring(0,10)||'-'}</td><td>${r.agent_name||r.agent?.name||'-'}</td>
<td>${formatMoney(r.total)}</td>
<td><div class="btn-group btn-group-sm">
<button class="btn btn-outline-info" onclick="viewItem(${r.id})"><i class="bi bi-eye"></i></button>
<button class="btn btn-outline-danger" onclick="delItem(${r.id})"><i class="bi bi-trash"></i></button>
</div></td></tr>`).join(''):'<tr><td colspan="6" class="text-center text-muted py-4">لا توجد بيانات</td></tr>';
document.getElementById('mobileCards').innerHTML=rows.map(r=>`<div class="mobile-card"><div class="mobile-card-title"><span>${r.distribution_no||'#'+r.id}</span></div><div class="mobile-card-row"><span>الوكيل</span><span>${r.agent_name||'-'}</span></div><div class="mobile-card-row"><span>الإجمالي</span><span>${formatMoney(r.total)}</span></div><div class="d-flex gap-1 mt-2"><button class="btn btn-sm btn-outline-info" onclick="viewItem(${r.id})"><i class="bi bi-eye"></i></button><button class="btn btn-sm btn-outline-danger" onclick="delItem(${r.id})"><i class="bi bi-trash"></i></button></div></div>`).join('');}
function renderPagination(j){const p=document.getElementById('pagination');p.innerHTML=`<span class="text-muted small">عرض ${j.from||0}-${j.to||0} من ${j.total||0}</span><div class="btn-group btn-group-sm"><button class="btn btn-outline-success" ${currentPage<=1?'disabled':''} onclick="goPage(${currentPage-1})"><i class="bi bi-chevron-right"></i></button><span class="btn btn-success disabled">${currentPage}/${lastPage}</span><button class="btn btn-outline-success" ${currentPage>=lastPage?'disabled':''} onclick="goPage(${currentPage+1})"><i class="bi bi-chevron-left"></i></button></div>`;}
function goPage(p){currentPage=p;loadData();}
function openAddModal(){window.location.href='/distributions/create';}
async function viewItem(id){try{const r=await fetch(`${API}/${id}`,{headers:{Accept:'application/json'}});const d=await r.json();let html=`<div class="row mb-3"><div class="col-6"><strong>الرقم:</strong> ${d.distribution_no||'#'+d.id}</div><div class="col-6"><strong>التاريخ:</strong> ${d.distribution_date||'-'}</div></div><div class="row mb-3"><div class="col-6"><strong>الوكيل:</strong> ${d.agent_name||'-'}</div><div class="col-6"><strong>الإجمالي:</strong> ${formatMoney(d.total)}</div></div>`;if(d.items&&d.items.length){html+='<h6>المنتجات</h6><table class="table table-sm"><thead><tr><th>المنتج</th><th>الكمية</th><th>السعر</th><th>المجموع</th></tr></thead><tbody>';html+=d.items.map(i=>`<tr><td>${i.product_name||i.name||'-'}</td><td>${i.quantity}</td><td>${formatMoney(i.price)}</td><td>${formatMoney(i.total||i.price*i.quantity)}</td></tr>`).join('');html+='</tbody></table>';}document.getElementById('viewContent').innerHTML=html;new bootstrap.Modal(document.getElementById('viewModal')).show();}catch(e){showToast('خطأ','حدث خطأ','danger');}}
async function delItem(id){if(!confirmDelete())return;try{await fetch(`${API}/${id}`,{method:'DELETE',headers:{'X-CSRF-TOKEN':CSRF_TOKEN,Accept:'application/json'}});loadData();showToast('نجاح','تم الحذف','success');}catch(e){showToast('خطأ','حدث خطأ','danger');}}
async function exportData(){try{const r=await fetch(`${API}?per_page=1000`,{headers:{Accept:'application/json'}});const j=await r.json();exportCsv(j.data||[],'distributions.csv');}catch(e){showToast('خطأ','خطأ في التصدير','danger');}}
loadData();
</script>
@endsection