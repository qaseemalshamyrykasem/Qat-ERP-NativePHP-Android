@extends('layouts.app')
@section('title', 'التقارير')
@section('content')
<div class="container-fluid py-4">
    <h2 class="mb-4"><i class="bi bi-bar-chart text-success"></i> التقارير</h2>

    <!-- Report Type Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-6 col-lg-3">
            <div class="card shadow-sm h-100 border-0" onclick="loadReport('daily')" style="cursor:pointer">
                <div class="card-body text-center p-4">
                    <i class="bi bi-calendar-day display-4 text-primary"></i>
                    <h5 class="mt-3">تقرير يومي</h5>
                    <p class="text-muted small">ملخص مبيعات ومصروفات اليوم</p>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="card shadow-sm h-100 border-0" onclick="loadReport('monthly')" style="cursor:pointer">
                <div class="card-body text-center p-4">
                    <i class="bi bi-calendar-month display-4 text-success"></i>
                    <h5 class="mt-3">تقرير شهري</h5>
                    <p class="text-muted small">ملخص مبيعات ومصروفات الشهر</p>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="card shadow-sm h-100 border-0" onclick="loadReport('debts')" style="cursor:pointer">
                <div class="card-body text-center p-4">
                    <i class="bi bi-cash-coin display-4 text-danger"></i>
                    <h5 class="mt-3">تقرير الديون</h5>
                    <p class="text-muted small">جميع الديون غير المسددة</p>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="card shadow-sm h-100 border-0" onclick="showAgentReport()" style="cursor:pointer">
                <div class="card-body text-center p-4">
                    <i class="bi bi-person-lines-fill display-4 text-warning"></i>
                    <h5 class="mt-3">كشف وكيل</h5>
                    <p class="text-muted small">تفاصيل مبيعات وتسويات الوكيل</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Report Content Area -->
    <div id="reportArea"></div>
</div>

<!-- Agent Select Modal -->
<div class="modal fade" id="agentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">اختر الوكيل</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="mb-3"><label class="form-label">الوكيل</label>
                    <select class="form-select" id="fAgentId"><option value="">اختر</option></select>
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-6"><label class="form-label">من</label><input type="date" class="form-control" id="fDateFrom"></div>
                    <div class="col-6"><label class="form-label">إلى</label><input type="date" class="form-control" id="fDateTo"></div>
                </div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button><button type="button" class="btn btn-success" onclick="loadAgentReport()">عرض</button></div>
        </div>
    </div>
</div>

<script>
const REPORTS_API = '/api/v1/reports';

async function loadReport(type) {
    const area = document.getElementById('reportArea');
    area.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-success"></div><p class="mt-2 text-muted">جاري تحميل التقرير...</p></div>';
    try {
        let url = `${REPORTS_API}/${type}`;
        if (type === 'daily') url += `?date=${new Date().toISOString().substring(0,10)}`;
        if (type === 'monthly') url += `?month=${new Date().toISOString().substring(0,7)}`;
        const res = await fetch(url, {headers:{Accept:'application/json'}});
        const data = await res.json();
        renderReport(type, data);
    } catch(e) {
        area.innerHTML = '<div class="alert alert-danger">خطأ في تحميل التقرير</div>';
    }
}

function renderReport(type, d) {
    const area = document.getElementById('reportArea');
    if (type === 'daily') {
        area.innerHTML = `<div class="card shadow-sm"><div class="card-header bg-primary text-white"><i class="bi bi-calendar-day"></i> تقرير يومي - ${d.date||'اليوم'}</div><div class="card-body">
            <div class="row g-3 mb-4">
                <div class="col-md-3"><div class="card text-center p-3"><h6 class="text-muted">المبيعات</h6><h4 class="text-success">${formatMoney(d.total_sales)}</h4></div></div>
                <div class="col-md-3"><div class="card text-center p-3"><h6 class="text-muted">المصروفات</h6><h4 class="text-danger">${formatMoney(d.total_expenses)}</h4></div></div>
                <div class="col-md-3"><div class="card text-center p-3"><h6 class="text-muted">صافي الربح</h6><h4 class="fw-bold ${(d.total_sales||0)-(d.total_expenses||0)>=0?'text-success':'text-danger'}">${formatMoney((d.total_sales||0)-(d.total_expenses||0))}</h4></div></div>
                <div class="col-md-3"><div class="card text-center p-3"><h6 class="text-muted">عدد الفواتير</h6><h4>${d.sales_count||0}</h4></div></div>
            </div>
            ${d.sales&&d.sales.length?'<h5>تفاصيل المبيعات</h5><div class="table-responsive"><table class="table table-sm"><thead><tr><th>الفاتورة</th><th>العميل</th><th>المبلغ</th><th>الوقت</th></tr></thead><tbody>'+d.sales.map(s=>`<tr><td>${s.invoice_no||'#'+s.id}</td><td>${s.customer_name||'-'}</td><td>${formatMoney(s.total)}</td><td>${s.created_at?.substring(11,16)||'-'}</td></tr>`).join('')+'</tbody></table></div>':''}
        </div></div>`;
    } else if (type === 'monthly') {
        area.innerHTML = `<div class="card shadow-sm"><div class="card-header bg-success text-white"><i class="bi bi-calendar-month"></i> تقرير شهري - ${d.month||'الشهر'}</div><div class="card-body">
            <div class="row g-3 mb-4">
                <div class="col-md-3"><div class="card text-center p-3"><h6 class="text-muted">إجمالي المبيعات</h6><h4 class="text-success">${formatMoney(d.total_sales)}</h4></div></div>
                <div class="col-md-3"><div class="card text-center p-3"><h6 class="text-muted">إجمالي المصروفات</h6><h4 class="text-danger">${formatMoney(d.total_expenses)}</h4></div></div>
                <div class="col-md-3"><div class="card text-center p-3"><h6 class="text-muted">صافي الربح</h6><h4 class="fw-bold">${formatMoney((d.total_sales||0)-(d.total_expenses||0))}</h4></div></div>
                <div class="col-md-3"><div class="card text-center p-3"><h6 class="text-muted">عدد الفواتير</h6><h4>${d.sales_count||0}</h4></div></div>
            </div>
            ${d.daily_breakdown&&d.daily_breakdown.length?'<h5>التفصيل اليومي</h5><div class="table-responsive"><table class="table table-sm"><thead><tr><th>التاريخ</th><th>المبيعات</th><th>المصروفات</th><th>الصافي</th></tr></thead><tbody>'+d.daily_breakdown.map(b=>`<tr><td>${b.date}</td><td class="text-success">${formatMoney(b.sales)}</td><td class="text-danger">${formatMoney(b.expenses)}</td><td class="fw-bold">${formatMoney(b.net)}</td></tr>`).join('')+'</tbody></table></div>':''}
        </div></div>`;
    } else if (type === 'debts') {
        const rows = d.data || d || [];
        const total = rows.reduce((s,r) => s+((r.amount||0)-(r.paid||0)), 0);
        area.innerHTML = `<div class="card shadow-sm"><div class="card-header bg-danger text-white"><i class="bi bi-cash-coin"></i> تقرير الديون - الإجمالي: ${formatMoney(total)}</div><div class="card-body">
            ${rows.length?'<div class="table-responsive"><table class="table table-hover"><thead><tr><th>العميل</th><th>المبلغ</th><th>المدفوع</th><th>المتبقي</th><th>الاستحقاق</th><th>الهاتف</th></tr></thead><tbody>'+rows.map(r=>{const rem=(r.amount||0)-(r.paid||0);return `<tr><td>${r.customer_name||'-'}</td><td>${formatMoney(r.amount)}</td><td>${formatMoney(r.paid)}</td><td class="${rem>0?'text-danger fw-bold':''}">${formatMoney(rem)}</td><td>${r.due_date||'-'}</td><td><span dir="ltr">${r.customer_phone||'-'}</span>${r.customer_phone?` <button class="btn btn-sm btn-outline-success" onclick="waSend('${r.customer_phone}')"><i class="bi bi-whatsapp"></i></button>`:''}</td></tr>`;}).join('')+'</tbody></table></div>':'<div class="text-center text-muted py-4">لا توجد ديون</div>'}
        </div></div>`;
    }
}

async function showAgentReport() {
    try {
        const r = await fetch('/api/v1/agents?per_page=100&status=active', {headers:{Accept:'application/json'}});
        const j = await r.json();
        document.getElementById('fAgentId').innerHTML = '<option value="">اختر</option>' + (j.data||[]).map(a => `<option value="${a.id}">${a.name}</option>`).join('');
    } catch(e) {}
    const today = new Date().toISOString().substring(0,10);
    document.getElementById('fDateFrom').value = today.substring(0,8) + '01';
    document.getElementById('fDateTo').value = today;
    new bootstrap.Modal(document.getElementById('agentModal')).show();
}

async function loadAgentReport() {
    const agentId = document.getElementById('fAgentId').value;
    if (!agentId) return showToast('تنبيه', 'اختر الوكيل', 'warning');
    bootstrap.Modal.getInstance(document.getElementById('agentModal')).hide();
    const area = document.getElementById('reportArea');
    area.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-success"></div></div>';
    try {
        const df = document.getElementById('fDateFrom').value;
        const dt = document.getElementById('fDateTo').value;
        let url = `${REPORTS_API}/agent-statement/${agentId}?date_from=${df}&date_to=${dt}`;
        const res = await fetch(url, {headers:{Accept:'application/json'}});
        const d = await res.json();
        area.innerHTML = `<div class="card shadow-sm"><div class="card-header bg-warning text-dark"><i class="bi bi-person-lines-fill"></i> كشف وكيل - ${d.agent_name||'-'}</div><div class="card-body">
            <div class="row g-3 mb-4">
                <div class="col-md-3"><div class="card text-center p-3"><h6 class="text-muted">المبيعات</h6><h4>${formatMoney(d.total_sales)}</h4></div></div>
                <div class="col-md-3"><div class="card text-center p-3"><h6 class="text-muted">المرتجعات</h6><h4>${formatMoney(d.total_returns)}</h4></div></div>
                <div class="col-md-3"><div class="card text-center p-3"><h6 class="text-muted">العمولة</h6><h4>${formatMoney(d.commission)}</h4></div></div>
                <div class="col-md-3"><div class="card text-center p-3"><h6 class="text-muted">صافي المستحق</h6><h4 class="fw-bold text-success">${formatMoney(d.net_amount)}</h4></div></div>
            </div>
            ${d.sales&&d.sales.length?'<h5>تفاصيل المبيعات</h5><div class="table-responsive"><table class="table table-sm"><thead><tr><th>الفاتورة</th><th>التاريخ</th><th>المبلغ</th></tr></thead><tbody>'+d.sales.map(s=>`<tr><td>${s.invoice_no||'#'+s.id}</td><td>${s.sale_date||s.created_at?.substring(0,10)||'-'}</td><td>${formatMoney(s.total)}</td></tr>`).join('')+'</tbody></table></div>':''}
        </div></div>`;
    } catch(e) {
        area.innerHTML = '<div class="alert alert-danger">خطأ في تحميل التقرير</div>';
    }
}

function waSend(phone) {
    let p = phone.replace(/[^0-9]/g, '');
    if (p.length === 9) p = '967' + p;
    window.open('https://wa.me/' + p, '_blank');
}
</script>
@endsection