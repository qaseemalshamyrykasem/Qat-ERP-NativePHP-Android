@extends('layouts.app')
@section('title', 'قائمة الدخل')
@section('content')
<div class="container-fluid py-4">
    <h2><i class="bi bi-file-earmark-bar-graph text-success"></i> قائمة الدخل</h2>
    <div class="card shadow-sm mt-3"><div class="card-body" id="dataGrid">جارٍ التحميل...</div></div>
</div>
<script>
async function load() {
    const res = await fetch(`${API_BASE}/income-statement`, {headers:{Accept:'application/json'}});
    const json = await res.json();
    const d = json.data || {};
    document.getElementById('dataGrid').innerHTML = `<table class="table">
        <tr><td>الإيرادات</td><td class="text-end">${Number(d.revenue).toLocaleString()}</td></tr>
        <tr><td>تكلفة البضاعة المباعة</td><td class="text-end">(${Number(d.cogs).toLocaleString()})</td></tr>
        <tr><td><strong>مجمل الربح</strong></td><td class="text-end"><strong>${Number(d.gross_profit).toLocaleString()}</strong></td></tr>
        <tr><td>المصروفات التشغيلية</td><td class="text-end">(${Number(d.operating).toLocaleString()})</td></tr>
        <tr class="table-success"><td><strong>صافي الربح</strong></td><td class="text-end"><strong>${Number(d.net_profit).toLocaleString()}</strong></td></tr>
    </table>`;
}
load();
</script>
@endsection
