@extends('layouts.app')
@section('title', 'ميزان المراجعة')
@section('content')
<div class="container-fluid py-4">
    <h2><i class="bi bi-balance-scale text-success"></i> ميزان المراجعة</h2>
    <div class="card shadow-sm mt-3"><div class="card-body" id="dataGrid">جارٍ التحميل...</div></div>
</div>
<script>
async function load() {
    const res = await fetch(`${API_BASE}/trial-balance`, {headers:{Accept:'application/json'}});
    const json = await res.json();
    const data = json.data || {};
    const rows = (data.rows||[]).map(r => `<tr><td>${r.code}</td><td>${r.name}</td><td>${Number(r.debit).toLocaleString()}</td><td>${Number(r.credit).toLocaleString()}</td></tr>`).join('');
    document.getElementById('dataGrid').innerHTML = `<table class="table"><thead><tr><th>الكود</th><th>الحساب</th><th>مدين</th><th>دائن</th></tr></thead><tbody>${rows}</tbody><tfoot><tr><th colspan="2">الإجمالي</th><th>${Number(data.total_debit).toLocaleString()}</th><th>${Number(data.total_credit).toLocaleString()}</th></tr></tfoot></table>`;
}
load();
</script>
@endsection
