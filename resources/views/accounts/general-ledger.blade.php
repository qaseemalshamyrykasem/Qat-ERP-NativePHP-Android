@extends('layouts.app')
@section('title', 'الأستاذ العام')
@section('content')
<div class="container-fluid py-4">
    <h2><i class="bi bi-journal-text text-success"></i> الأستاذ العام</h2>
    <div class="card shadow-sm mt-3"><div class="card-body" id="dataGrid">جارٍ التحميل...</div></div>
</div>
<script>
async function load() {
    const res = await fetch(`${API_BASE}/general-ledger`, {headers:{Accept:'application/json'}});
    const json = await res.json();
    const rows = (json.rows||[]).slice(0,100).map(r => `<tr><td>${r.entry_no}</td><td>${r.entry_date}</td><td>${r.account_code}</td><td>${r.account_name}</td><td>${r.description||''}</td><td>${Number(r.debit).toLocaleString()}</td><td>${Number(r.credit).toLocaleString()}</td><td>${Number(r.balance).toLocaleString()}</td></tr>`).join('');
    document.getElementById('dataGrid').innerHTML = `<table class="table table-sm"><thead><tr><th>القيد</th><th>التاريخ</th><th>الكود</th><th>الحساب</th><th>الوصف</th><th>مدين</th><th>دائن</th><th>الرصيد</th></tr></thead><tbody>${rows}</tbody></table>`;
}
load();
</script>
@endsection
