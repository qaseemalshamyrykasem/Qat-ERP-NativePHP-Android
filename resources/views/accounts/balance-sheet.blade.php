@extends('layouts.app')
@section('title', 'الميزانية العمومية')
@section('content')
<div class="container-fluid py-4">
    <h2><i class="bi bi-table text-success"></i> الميزانية العمومية</h2>
    <div class="card shadow-sm mt-3"><div class="card-body" id="dataGrid">جارٍ التحميل...</div></div>
</div>
<script>
async function load() {
    const res = await fetch(`${API_BASE}/balance-sheet`, {headers:{Accept:'application/json'}});
    const json = await res.json();
    const d = json.data || {};
    document.getElementById('dataGrid').innerHTML = `<table class="table">
        <tr><td>الأصول</td><td class="text-end">${Number(d.assets).toLocaleString()}</td></tr>
        <tr><td>الخصوم</td><td class="text-end">${Number(d.liabilities).toLocaleString()}</td></tr>
        <tr><td>حقوق الملكية</td><td class="text-end">${Number(d.equity).toLocaleString()}</td></tr>
        <tr><td>صافي الربح</td><td class="text-end">${Number(d.net_profit).toLocaleString()}</td></tr>
        <tr class="table-success"><td><strong>إجمالي الخصوم وحقوق الملكية</strong></td><td class="text-end"><strong>${Number(d.total_liab_equity).toLocaleString()}</strong></td></tr>
    </table>`;
}
load();
</script>
@endsection
