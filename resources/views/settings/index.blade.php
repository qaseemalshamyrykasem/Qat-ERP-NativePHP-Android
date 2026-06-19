@extends('layouts.app')
@section('title', 'الإعدادات')
@section('content')
<div class="container-fluid py-4">
    <h2 class="mb-4"><i class="bi bi-gear text-success"></i> الإعدادات</h2>
    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header"><h5 class="mb-0">إعدادات المتجر</h5></div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">اسم المتجر</label>
                            <input type="text" class="form-control" id="sStoreName">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">هاتف المتجر</label>
                            <input type="tel" class="form-control" id="sStorePhone">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">العملة الافتراضية</label>
                            <select class="form-select" id="sCurrency">
                                <option value="YER">ريال يمني (YER)</option>
                                <option value="USD">دولار أمريكي (USD)</option>
                                <option value="SAR">ريال سعودي (SAR)</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">صيغة التاريخ</label>
                            <select class="form-select" id="sDateFormat">
                                <option value="Y-m-d">YYYY-MM-DD</option>
                                <option value="d/m/Y">DD/MM/YYYY</option>
                                <option value="m/d/Y">MM/DD/YYYY</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">دقة المبالغ المالية</label>
                            <input type="number" class="form-control" id="sPrecision" min="0" max="4" value="0">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">واتساب</label>
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" id="sWhatsapp" role="switch">
                                <label class="form-check-label" for="sWhatsapp">تفعيل إرسال واتساب</label>
                            </div>
                        </div>
                    </div>
                    <div class="mt-4">
                        <button class="btn btn-success" onclick="saveSettings()" id="saveBtn"><i class="bi bi-save"></i> حفظ الإعدادات</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card shadow-sm mb-3">
                <div class="card-header"><h5 class="mb-0">معلومات النظام</h5></div>
                <div class="card-body">
                    <div class="mb-2"><span class="text-muted">الإصدار:</span> <strong>Qat ERP v1.0</strong></div>
                    <div class="mb-2"><span class="text-muted">Laravel:</span> <strong>{{ app()->version() }}</strong></div>
                    <div class="mb-2"><span class="text-muted">البيئة:</span> <strong>{{ app()->environment() }}</strong></div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
const API = '/api/v1/settings';

async function loadSettings() {
    try {
        const res = await fetch(API, {headers:{Accept:'application/json'}});
        const data = await res.json();
        const settings = data.data || data || {};
        document.getElementById('sStoreName').value = settings.store_name || '';
        document.getElementById('sStorePhone').value = settings.store_phone || '';
        document.getElementById('sCurrency').value = settings.default_currency || 'YER';
        document.getElementById('sDateFormat').value = settings.date_format || 'Y-m-d';
        document.getElementById('sPrecision').value = settings.money_precision || 0;
        document.getElementById('sWhatsapp').checked = settings.whatsapp_enabled == '1' || settings.whatsapp_enabled === true;
    } catch(e) {
        showToast('خطأ', 'خطأ في تحميل الإعدادات', 'danger');
    }
}

async function saveSettings() {
    const data = {
        store_name: document.getElementById('sStoreName').value,
        store_phone: document.getElementById('sStorePhone').value,
        default_currency: document.getElementById('sCurrency').value,
        date_format: document.getElementById('sDateFormat').value,
        money_precision: document.getElementById('sPrecision').value,
        whatsapp_enabled: document.getElementById('sWhatsapp').checked ? '1' : '0',
    };
    const btn = document.getElementById('saveBtn');
    btn.classList.add('btn-loading');
    btn.disabled = true;
    try {
        const res = await fetch(API, {
            method: 'POST',
            headers: {'Content-Type':'application/json','X-CSRF-TOKEN':CSRF_TOKEN,Accept:'application/json'},
            body: JSON.stringify(data)
        });
        const json = await res.json();
        if (json.success || json.message) {
            showToast('نجاح', 'تم حفظ الإعدادات', 'success');
        } else {
            showToast('خطأ', json.message || 'حدث خطأ', 'danger');
        }
    } catch(e) {
        showToast('خطأ', 'خطأ في الاتصال', 'danger');
    }
    btn.classList.remove('btn-loading');
    btn.disabled = false;
}

loadSettings();
</script>
@endsection