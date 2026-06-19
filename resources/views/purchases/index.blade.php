@extends('layouts.app')
@section('title', 'المشتريات')
@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="bi bi-cart-check text-success"></i> المشتريات</h2>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-secondary btn-sm" onclick="exportData()"><i class="bi bi-download"></i> تصدير</button>
            <button class="btn btn-success" onclick="openAddModal()"><i class="bi bi-plus-lg"></i> إضافة عملية شراء</button>
        </div>
    </div>
    <div class="card shadow-sm">
        <div class="card-body">
            <div class="d-flex gap-2 mb-3 flex-wrap">
                <input type="text" class="form-control form-control-sm" style="max-width:250px" placeholder="بحث برقم الفاتورة..." id="searchInput" oninput="debounceSearch()">
                <input type="date" class="form-control form-control-sm" style="max-width:180px" id="dateFrom" onchange="loadData()">
                <input type="date" class="form-control form-control-sm" style="max-width:180px" id="dateTo" onchange="loadData()">
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead><tr><th>#</th><th>الفاتورة</th><th>التاريخ</th><th>المورد</th><th>الإجمالي</th><th>المدفوع</th><th>المتبقي</th><th>إجراءات</th></tr></thead>
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
            <div class="modal-header"><h5 class="modal-title" id="modalTitle">إضافة عملية شراء</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="mb-3"><label class="form-label">المورد *</label>
                    <input type="text" class="form-control" id="fSupplierSearch" placeholder="ابحث عن مورد..." oninput="searchSuppliers(this.value)">
                    <input type="hidden" id="fSupplierId">
                    <div id="supplierResults" class="dropdown-menu show position-relative w-100 d-none"></div>
                </div>
                <div class="mb-3"><label class="form-label">تاريخ الشراء</label><input type="date" class="form-control" id="fDate"></div>
                <div class="mb-3"><label class="form-label">ملاحظات</label><textarea class="form-control" id="fNotes" rows="2"></textarea></div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button><button type="button" class="btn btn-success" onclick="saveItem()" id="saveBtn">حفظ</button></div>
        </div>
    </div>
</div>
<div class="modal fade" id="viewModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">تفاصيل الفاتورة</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body" id="viewContent"></div>
        </div>
    </div>
</div>
@include('components.confirmation-modal')
<script>
const API='/api/v1/purchases';let currentPage=1,lastPage=1,searchTimer;
function debounceSearch(){clearTimeout(searchTimer);searchTimer=setTimeout(()=>{currentPage=1;loadData()},400);}
async function loadData(){const s=document.getElementById('searchInput').value,df=document.getElementById('dateFrom').value,dt=document.getElementById('dateTo').value;let u=`${API}?per_page=20&page=${currentPage}`;if(s)u+=`&search=${encodeURIComponent(s)}`;if(df)u+=`&date_from=${df}`;if(dt)u+=`&date_to=${dt}`;try{const r=await fetch(u,{headers:{Accept:'application/json'}});const j=await r.json();renderTable(j.data||[]);lastPage=j.last_page||1;renderPagination(j);}catch(e){document.getElementById('rows').innerHTML='<tr><td colspan="8" class="text-center text-danger">خطأ في تحميل البيانات</td></tr>';}}
function renderTable(rows){document.getElementById('rows').innerHTML=rows.length?rows.map(r=>`<tr>
<td>${r.id}</td><td class="fw-semibold">${r.invoice_no||'#'+r.id}</td><td>${r.purchase_date||r.created_at?.substring(0,10)||'-'}</td><td>${r.supplier_name||r.supplier?.name||'-'}</td>
<td>${formatMoney(r.total)}</td><td>${formatMoney(r.paid)}</td><td><span class="${(r.remaining||0)>0?'text-danger fw-bold':'text-success'}">${formatMoney(r.remaining)}</span></td>
<td><div class="btn-group btn-group-sm">
<button class="btn btn-outline-info" onclick="viewItem(${r.id})" title="عرض"><i class="bi bi-eye"></i></button>
<button class="btn btn-outline-danger" onclick="delItem(${r.id})"><i class="bi bi-trash"></i></button>
</div></td></tr>`).join(''):'<tr><td colspan="8" class="text-center text-muted py-4">لا توجد بيانات</td></tr>';
document.getElementById('mobileCards').innerHTML=rows.map(r=>`<div class="mobile-card"><div class="mobile-card-title"><span>${r.invoice_no||'#'+r.id}</span></div><div class="mobile-card-row"><span>المورد</span><span>${r.supplier_name||'-'}</span></div><div class="mobile-card-row"><span>الإجمالي</span><span>${formatMoney(r.total)}</span></div><div class="d-flex gap-1 mt-2"><button class="btn btn-sm btn-outline-info" onclick="viewItem(${r.id})"><i class="bi bi-eye"></i></button><button class="btn btn-sm btn-outline-danger" onclick="delItem(${r.id})"><i class="bi bi-trash"></i></button></div></div>`).join('');}
function renderPagination(j){const p=document.getElementById('pagination');p.innerHTML=`<span class="text-muted small">عرض ${j.from||0}-${j.to||0} من ${j.total||0}</span><div class="btn-group btn-group-sm"><button class="btn btn-outline-success" ${currentPage<=1?'disabled':''} onclick="goPage(${currentPage-1})"><i class="bi bi-chevron-right"></i></button><span class="btn btn-success disabled">${currentPage}/${lastPage}</span><button class="btn btn-outline-success" ${currentPage>=lastPage?'disabled':''} onclick="goPage(${currentPage+1})"><i class="bi bi-chevron-left"></i></button></div>`;}
function goPage(p){currentPage=p;loadData();}
function openAddModal(){document.getElementById('fSupplierId').value='';document.getElementById('fSupplierSearch').value='';document.getElementById('fDate').value=new Date().toISOString().substring(0,10);document.getElementById('fNotes').value='';new bootstrap.Modal(document.getElementById('itemModal')).show();}
async function searchSuppliers(q){const box=document.getElementById('supplierResults');if(q.length<1){box.classList.add('d-none');return;}try{const r=await fetch(`/api/v1/suppliers?search=${encodeURIComponent(q)}&per_page=10`,{headers:{Accept:'application/json'}});const j=await r.json();const items=j.data||[];if(!items.length){box.classList.add('d-none');return;}box.classList.remove('d-none');box.innerHTML=items.map(s=>`<a class="dropdown-item" href="#" onclick="selectSupplier(${s.id},'${s.name}')">${s.name} <small class="text-muted">${s.phone||''}</small></a>`).join('');}catch(e){box.classList.add('d-none');}}
function selectSupplier(id,name){document.getElementById('fSupplierId').value=id;document.getElementById('fSupplierSearch').value=name;document.getElementById('supplierResults').classList.add('d-none');}
async function saveItem(){const supplier_id=document.getElementById('fSupplierId').value;if(!supplier_id)return showToast('تنبيه','اختر المورد','warning');const d={supplier_id,purchase_date:document.getElementById('fDate').value,notes:document.getElementById('fNotes').value};const btn=document.getElementById('saveBtn');btn.classList.add('btn-loading');btn.disabled=true;try{const o={method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF_TOKEN,Accept:'application/json'},body:JSON.stringify(d)};const r=await fetch(API,o);const j=await r.json();if(j.id||j.success){bootstrap.Modal.getInstance(document.getElementById('itemModal')).hide();loadData();showToast('نجاح','تم إنشاء عملية الشراء','success');}else showToast('خطأ',j.message||'حدث خطأ','danger');}catch(e){showToast('خطأ','خطأ في الاتصال','danger');}btn.classList.remove('btn-loading');btn.disabled=false;}
async function viewItem(id){try{const r=await fetch(`${API}/${id}`,{headers:{Accept:'application/json'}});const d=await r.json();let html=`<div class="row mb-3"><div class="col-6"><strong>الفاتورة:</strong> ${d.invoice_no||'#'+d.id}</div><div class="col-6"><strong>التاريخ:</strong> ${d.purchase_date||'-'}</div></div><div class="row mb-3"><div class="col-6"><strong>المورد:</strong> ${d.supplier_name||'-'}</div><div class="col-6"><strong>الإجمالي:</strong> ${formatMoney(d.total)}</div></div>`;if(d.items&&d.items.length){html+='<h6>المنتجات</h6><table class="table table-sm"><thead><tr><th>المنتج</th><th>الكمية</th><th>السعر</th><th>المجموع</th></tr></thead><tbody>';html+=d.items.map(i=>`<tr><td>${i.product_name||i.name||'-'}</td><td>${i.quantity}</td><td>${formatMoney(i.price)}</td><td>${formatMoney(i.total||i.price*i.quantity)}</td></tr>`).join('');html+='</tbody></table>';}document.getElementById('viewContent').innerHTML=html;new bootstrap.Modal(document.getElementById('viewModal')).show();}catch(e){showToast('خطأ','حدث خطأ','danger');}}
async function delItem(id){if(!confirmDelete())return;try{await fetch(`${API}/${id}`,{method:'DELETE',headers:{'X-CSRF-TOKEN':CSRF_TOKEN,Accept:'application/json'}});loadData();showToast('نجاح','تم الحذف','success');}catch(e){showToast('خطأ','حدث خطأ','danger');}}
async function exportData(){try{const r=await fetch(`${API}?per_page=1000`,{headers:{Accept:'application/json'}});const j=await r.json();exportCsv(j.data||[],'purchases.csv');}catch(e){showToast('خطأ','خطأ في التصدير','danger');}}
loadData();
</script>
@endsection