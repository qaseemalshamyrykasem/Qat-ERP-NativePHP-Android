@extends('layouts.app')
@section('title', 'مركز الرسائل - واتساب')
@section('content')
<div class="container-fluid py-4">
    <h2 class="mb-4"><i class="bi bi-whatsapp text-success"></i> مركز الرسائل - واتساب</h2>

    <div class="row g-4">
        <!-- Quick Send -->
        <div class="col-lg-7">
            <div class="card shadow-sm">
                <div class="card-header"><i class="bi bi-send"></i> إرسال سريع</div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">المستلم</label>
                        <div class="row g-2">
                            <div class="col-md-5">
                                <select class="form-select" id="waEntityType">
                                    <option value="customer">عميل</option>
                                    <option value="supplier">مورد</option>
                                    <option value="agent">وكيل</option>
                                </select>
                            </div>
                            <div class="col-md-7">
                                <input type="text" class="form-control" id="waEntitySearch" placeholder="ابحث بالاسم أو الرقم...">
                                <div class="dropdown-menu" id="waEntityDropdown" style="width:100%;max-height:200px;overflow-y:auto;display:none;"></div>
                            </div>
                        </div>
                        <input type="hidden" id="waEntityId" value="">
                        <input type="hidden" id="waEntityPhone" value="">
                        <div id="waEntityInfo" class="mt-2 small text-muted"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">قالب الرسالة</label>
                        <select class="form-select" id="waTemplate">
                            <option value="">اختر قالب أو اكتب رسالة حرة</option>
                            <option value="debt_reminder">تذكير بالدين</option>
                            <option value="payment_confirmation">تأكيد الدفع</option>
                            <option value="sale_invoice">إشعار فاتورة</option>
                            <option value="daily_prices">أسعار اليوم</option>
                            <option value="settlement">تسوية حساب وكيل</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">الرسالة</label>
                        <textarea class="form-control" id="waMessage" rows="5" placeholder="اكتب الرسالة هنا..."></textarea>
                    </div>
                    <button class="btn btn-success" onclick="sendWhatsApp()">
                        <i class="bi bi-whatsapp"></i> إرسال عبر واتساب
                    </button>
                </div>
            </div>
        </div>

        <!-- Bulk Send -->
        <div class="col-lg-5">
            <div class="card shadow-sm mb-4">
                <div class="card-header"><i class="bi bi-people"></i> إرسال جماعي</div>
                <div class="card-body">
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" id="waBulkDebtors" checked>
                        <label class="form-check-label" for="waBulkDebtors">العملاء المدينين</label>
                    </div>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" id="waBulkAll">
                        <label class="form-check-label" for="waBulkAll">جميع العملاء</label>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">الرسالة</label>
                        <textarea class="form-control" id="waBulkMessage" rows="3" placeholder="رسالة الإرسال الجماعي..."></textarea>
                    </div>
                    <button class="btn btn-outline-success" onclick="bulkSendWhatsApp()">
                        <i class="bi bi-send"></i> إرسال للجميع
                    </button>
                    <small class="text-muted d-block mt-2">سيفتح واتساب لكل مستلم على حدة</small>
                </div>
            </div>

            <!-- Info Card -->
            <div class="card border-info">
                <div class="card-body text-center">
                    <i class="bi bi-info-circle text-info fs-3"></i>
                    <p class="mt-2 mb-0 small">يتم الإرسال عبر واتساب على هاتفك مباشرة<br>رقم المرسل: رقم المتجر (من الإعدادات)</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const templates = {
    debt_reminder: 'عزيزي {اسم}،\n\nتذكير بأن لديك دين مستحق بقيمة {المبلغ} ريال، مستحق بتاريخ {التاريخ}.\n\n- {المتجر}',
    payment_confirmation: 'عزيزي {اسم}،\n\nتم استلام دفعة بقيمة {المبلغ} ريال.\nالمتبقي: {المتبقي} ريال\n\nشكراً لتعاونكم.\n- {المتجر}',
    sale_invoice: 'فاتورة بيع رقم {الرقم}\nالمبلغ: {المبلغ} ريال\nالمدفوع: {المدفوع} ريال\n- {المتجر}',
    daily_prices: 'أسعار اليوم ({التاريخ}):\n{المنتجات}\n- {المتجر}',
    settlement: 'تسوية حساب الوكيل {اسم}\nإجمالي المبيعات: {المبيعات}\nصافي الاستحقاق: {الصافي}\n- {المتجر}',
};

document.getElementById('waTemplate').addEventListener('change', function() {
    const tmpl = templates[this.value];
    if (tmpl) document.getElementById('waMessage').value = tmpl;
});

// Entity search
let waSearchTimer;
const waEntityType = document.getElementById('waEntityType');
const waEntitySearch = document.getElementById('waEntitySearch');
const waEntityDropdown = document.getElementById('waEntityDropdown');
const waEntityId = document.getElementById('waEntityId');
const waEntityPhone = document.getElementById('waEntityPhone');
const waEntityInfo = document.getElementById('waEntityInfo');

waEntitySearch.addEventListener('input', function() {
    clearTimeout(waSearchTimer);
    const q = this.value.trim();
    if (q.length < 1) { waEntityDropdown.style.display = 'none'; return; }
    waSearchTimer = setTimeout(() => {
        const type = waEntityType.value;
        fetch(`${API_BASE}/${type === 'customer' ? 'customers' : type === 'supplier' ? 'suppliers' : 'agents'}?search=${encodeURIComponent(q)}&per_page=10`, {headers:{Accept:'application/json'}})
        .then(r => r.json())
        .then(json => {
            const items = json.data || [];
            let html = '';
            items.forEach(item => {
                html += `<div class="dropdown-item" data-id="${item.id}" data-phone="${item.phone||''}" data-name="${item.name}">
                    <div class="fw-semibold">${item.name}</div><small class="text-muted">${item.phone||''}</small>
                </div>`;
            });
            waEntityDropdown.innerHTML = html || '<div class="text-center text-muted p-2 small">لا توجد نتائج</div>';
            waEntityDropdown.style.display = 'block';
            waEntityDropdown.querySelectorAll('.dropdown-item').forEach(el => {
                el.addEventListener('click', () => {
                    waEntityId.value = el.dataset.id;
                    waEntityPhone.value = el.dataset.phone;
                    waEntitySearch.value = el.dataset.name;
                    waEntityDropdown.style.display = 'none';
                    waEntityInfo.textContent = 'رقم الهاتف: ' + (el.dataset.phone || 'غير متوفر');
                });
            });
        });
    }, 300);
});

function sendWhatsApp() {
    const phone = waEntityPhone.value;
    const message = document.getElementById('waMessage').value.trim();
    if (!phone) return showToast('تنبيه', 'اختر المستلم أولاً', 'warning');
    if (!message) return showToast('تنبيه', 'اكتب الرسالة', 'warning');

    // Clean phone
    let cleanPhone = phone.replace(/[^0-9]/g, '');
    if (cleanPhone.length === 9) cleanPhone = '967' + cleanPhone;

    const url = 'https://wa.me/' + cleanPhone + '?text=' + encodeURIComponent(message);
    window.open(url, '_blank');
    showToast('نجاح', 'تم فتح واتساب', 'success');
}

async function bulkSendWhatsApp() {
    const message = document.getElementById('waBulkMessage').value.trim();
    if (!message) return showToast('تنبيه', 'اكتب الرسالة', 'warning');

    const endpoints = [];
    if (document.getElementById('waBulkDebtors').checked) {
        endpoints.push(`${API_BASE}/debts?status=active&per_page=50`);
    }

    for (const url of endpoints) {
        const res = await fetch(url, {headers:{Accept:'application/json'}});
        const json = await res.json();
        const items = json.data || [];
        for (const item of items) {
            const phone = item.customer_phone || item.phone;
            if (phone) {
                let cleanPhone = phone.replace(/[^0-9]/g, '');
                if (cleanPhone.length === 9) cleanPhone = '967' + cleanPhone;
                const waUrl = 'https://wa.me/' + cleanPhone + '?text=' + encodeURIComponent(message);
                window.open(waUrl, '_blank');
                await new Promise(r => setTimeout(r, 1000));
            }
        }
    }
    showToast('نجاح', 'تم فتح واتساب للمرسلين', 'success');
}
</script>
@endsection