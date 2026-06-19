@extends('layouts.app')
@section('title', 'العملات')
@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="bi bi-currency-exchange text-success"></i> العملات</h2>
        <div class="d-flex gap-2">
            <button class="btn btn-success" onclick="openAddModal()"><i class="bi bi-plus-lg"></i> إضافة عملة</button>
        </div>
    </div>
    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead><tr><th>#</th><th>الكود</th><th>الاسم</th><th>الرمز</th><th>سعر الصرف</th><th>الافتراضية</th><th>الحالة</th><th>إجراءات</th></tr></thead>
                    <tbody id="rows"></tbody>
                </table>
            </div>
            <div class="mobile-cards" id="mobileCards"></div>
        </div>
    </div>
</div>
<div class="modal fade" id="itemModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title" id="modalTitle">إضافة عملة</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <input type="hidden" id="editId">
                <div class="mb-3"><label class="form-label">الكود *</label><input type="text" class="form-control" id="fCode" placeholder="YER, USD, SAR"></div>
                <div class="mb-3"><label class="form-label">الاسم *</label><input type="text" class="form-control" id="fName" placeholder="ريال يمني"></div>
                <div class="mb-3"><label class="form-label">الرمز</label><input type="text" class="form-control" id="fSymbol" placeholder="ر.ي"></div>
                <div class="mb-3"><label class="form-label">سعر الصرف</label><input type="number" class="form-control" id="fRate" step="0.01" value="1"></div>
                <div class="form-check mb-3"><input class="form-check-input" type="checkbox" id="fDefault"><label class="form-check-label" for="fDefault">عملة افتراضية</label></div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button><button type="button" class="btn btn-success" onclick="saveItem()" id="saveBtn">حفظ</button></div>
        </div>
    </div>
</div>
@include('components.confirmation-modal')
<script>
const API='/api/v1/currencies';
async function loadData(){try{const r=await fetch(API,{headers:{Accept:'application/json'}});const j=await r.json();const rows=j.data||j||[];renderTable(Array.isArray(rows)?rows:[]);}catch(e){document.getElementById('rows').innerHTML='<tr><td colspan="8" class="text-center text-danger">خطأ في تحميل البيانات</td></tr>';}}
function renderTable(rows){document.getElementById('rows').innerHTML=rows.length?rows.map(r=>`<tr>
<td>${r.id}</td><td class="fw-semibold">${r.code}</td><td>${r.name}</td><td>${r.symbol||'-'}</td>
<td>${r.exchange_rate||'-'}</td>
<td>${r.is_default?'<span class="badge bg-success">نعم</span>':'<span class="badge bg-secondary">لا</span>'}</td>
<td>${statusBadge(r.status||'active')}</td>
<td><div class="btn-group btn-group-sm">
<button class="btn btn-outline-primary" onclick="editItem(${r.id})"><i class="bi bi-pencil"></i></button>
${!r.is_default?`<button class="btn btn-outline-warning" onclick="setDefault(${r.id})" title="تعيين كافتراضي"><i class="bi bi-star"></i></button>`:''}
<button class="btn btn-outline-danger" onclick="delItem(${r.id})"><i class="bi bi-trash"></i></button>
</div></td></tr>`).join(''):'<tr><td colspan="8" class="text-center text-muted py-4">لا توجد بيانات</td></tr>';
document.getElementById('mobileCards').innerHTML=rows.map(r=>`<div class="mobile-card"><div class="mobile-card-title"><span>${r.name} (${r.code})</span>${r.is_default?'<span class="badge bg-success">افتراضي</span>':''}</div><div class="mobile-card-row"><span>سعر الصرف</span><span>${r.exchange_rate||'-'}</span></div><div class="d-flex gap-1 mt-2"><button class="btn btn-sm btn-outline-primary" onclick="editItem(${r.id})"><i class="bi bi-pencil"></i></button><button class="btn btn-sm btn-outline-danger" onclick="delItem(${r.id})"><i class="bi bi-trash"></i></button></div></div>`).join('');}
function openAddModal(){document.getElementById('editId').value='';document.getElementById('modalTitle').textContent='إضافة عملة';['fCode','fName','fSymbol'].forEach(id=>document.getElementById(id).value='');document.getElementById('fRate').value='1';document.getElementById('fDefault').checked=false;new bootstrap.Modal(document.getElementById('itemModal')).show();}
async function editItem(id){try{const r=await fetch(`${API}/${id}`,{headers:{Accept:'application/json'}});const d=await r.json();document.getElementById('editId').value=d.id;document.getElementById('modalTitle').textContent='تعديل عملة';document.getElementById('fCode').value=d.code;document.getElementById('fName').value=d.name;document.getElementById('fSymbol').value=d.symbol||'';document.getElementById('fRate').value=d.exchange_rate||1;document.getElementById('fDefault').checked=!!d.is_default;new bootstrap.Modal(document.getElementById('itemModal')).show();}catch(e){showToast('خطأ','حدث خطأ','danger');}}
async function saveItem(){const id=document.getElementById('editId').value;const d={code:document.getElementById('fCode').value,name:document.getElementById('fName').value,symbol:document.getElementById('fSymbol').value,exchange_rate:document.getElementById('fRate').value,is_default:document.getElementById('fDefault').checked?1:0};if(!d.code||!d.name)return showToast('تنبيه','الكود والاسم مطلوبان','warning');const btn=document.getElementById('saveBtn');btn.classList.add('btn-loading');btn.disabled=true;try{const o={method:id?'PUT':'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF_TOKEN,Accept:'application/json'},body:JSON.stringify(d)};const r=await fetch(id?`${API}/${id}`:API,o);const j=await r.json();if(j.id||j.success){bootstrap.Modal.getInstance(document.getElementById('itemModal')).hide();loadData();showToast('نجاح',id?'تم التعديل':'تم الإضافة','success');}else showToast('خطأ',j.message||'حدث خطأ','danger');}catch(e){showToast('خطأ','خطأ في الاتصال','danger');}btn.classList.remove('btn-loading');btn.disabled=false;}
async function setDefault(id){try{await fetch(`${API}/${id}/set-default`,{method:'POST',headers:{'X-CSRF-TOKEN':CSRF_TOKEN,Accept:'application/json'}});loadData();showToast('نجاح','تم تعيين العملة كافتراضية','success');}catch(e){showToast('خطأ','حدث خطأ','danger');}}
async function delItem(id){if(!confirmDelete())return;try{await fetch(`${API}/${id}`,{method:'DELETE',headers:{'X-CSRF-TOKEN':CSRF_TOKEN,Accept:'application/json'}});loadData();showToast('نجاح','تم الحذف','success');}catch(e){showToast('خطأ','حدث خطأ','danger');}}
loadData();
</script>
@endsection