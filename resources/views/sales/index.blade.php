@extends('layouts.app')
@section('title', 'المبيعات')
@section('content')
<div class="container-fluid py-4">
    <h2 class="mb-3"><i class="bi bi-cart text-success"></i> المبيعات</h2>
    <div class="card shadow-sm">
        <div class="card-body">
            <p class="text-muted">يتم تحميل البيانات من <code>/api/v1/sales</code></p>
            <div class="d-flex gap-2 mb-3">
                <input type="date" id="from" class="form-control" style="max-width:160px;">
                <input type="date" id="to" class="form-control" style="max-width:160px;">
                <button class="btn btn-success" onclick="loadSales()">تصفية</button>
            </div>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead><tr><th>#</th><th>الفاتورة</th><th>التاريخ</th><th>الوكيل</th><th>العميل</th><th>الإجمالي</th><th>الدفع</th><th>إجراءات</th></tr></thead>
                    <tbody id="rows"></tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<script>
async function loadSales() {
    const from = document.getElementById('from').value;
    const to = document.getElementById('to').value;
    const qs = new URLSearchParams({per_page: 50, from, to});
    const res = await fetch(`${API_BASE}/sales?${qs}`, {headers: {Accept:'application/json'}});
    const json = await res.json();
    const rows = document.getElementById('rows');
    rows.innerHTML = (json.data||[]).map(s => `<tr>
        <td>${s.id}</td>
        <td>${s.invoice_no}</td>
        <td>${s.sale_date}</td>
        <td>${s.agent_name||'-'}</td>
        <td>${s.customer_name||'-'}</td>
        <td>${Number(s.final_amount).toLocaleString()}</td>
        <td>${s.payment_method}</td>
        <td><button class="btn btn-sm btn-danger" onclick="delSale(${s.id})"><i class="bi bi-trash"></i></button></td>
    </tr>`).join('');
}
async function delSale(id) {
    if (!confirm('حذف الفاتورة؟')) return;
    await fetch(`${API_BASE}/sales/${id}`, {method:'DELETE', headers:{'X-CSRF-TOKEN':CSRF_TOKEN, Accept:'application/json'}});
    loadSales();
}
loadSales();
</script>
@endsection
