@extends('layouts.app')
@section('title', 'دليل الحسابات')
@section('content')
<div class="container-fluid py-4">
    <h2 class="mb-3"><i class="bi bi-journal-bookmark text-success"></i> دليل الحسابات</h2>
    <div class="card shadow-sm">
        <div class="card-body">
            <p class="text-muted">يتم تحميل البيانات من <code>/api/v1/chart-of-accounts</code></p>
            <div id="dataGrid">جارٍ التحميل...</div>
        </div>
    </div>
</div>
<script>
async function loadData() {
    try {
        const res = await fetch(`${API_BASE}/chart-of-accounts?per_page=50`, {headers: {Accept:'application/json'}});
        const json = await res.json();
        const rows = json.data || [];
        document.getElementById('dataGrid').innerHTML = rows.length
            ? '<pre class="mb-0">' + JSON.stringify(rows.slice(0,3), null, 2) + '</pre><p class="text-muted small mt-2">عرض ' + rows.length + ' عنصر</p>'
            : '<div class="text-muted text-center py-4">لا توجد بيانات</div>';
    } catch (e) {
        document.getElementById('dataGrid').innerHTML = '<div class="alert alert-warning mb-0">حدث خطأ: ' + e.message + '</div>';
    }
}
loadData();
</script>
@endsection
