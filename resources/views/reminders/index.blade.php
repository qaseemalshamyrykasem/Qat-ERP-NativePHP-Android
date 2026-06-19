@extends('layouts.app')
@section('title', 'التذكيرات')
@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="bi bi-bell text-success"></i> التذكيرات</h2>
        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addReminderModal">
            <i class="bi bi-plus-lg"></i> إضافة تذكير
        </button>
    </div>

    <div class="d-flex gap-2 mb-3 flex-wrap">
        <button class="btn btn-sm btn-success" onclick="loadReminders('pending')" id="filterPending">معلقة</button>
        <button class="btn btn-sm btn-outline-success" onclick="loadReminders('today')">اليوم</button>
        <button class="btn btn-sm btn-outline-success" onclick="loadReminders('completed')">مكتملة</button>
        <button class="btn btn-sm btn-outline-success" onclick="loadReminders('overdue')">متأخرة</button>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div id="remindersGrid">جارٍ التحميل...</div>
        </div>
    </div>
</div>

<!-- Add Reminder Modal -->
<div class="modal fade" id="addReminderModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">إضافة تذكير</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">العنوان</label>
                    <input type="text" class="form-control" id="reminderTitle" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">المرتبط بـ</label>
                    <select class="form-select" id="reminderEntityType">
                        <option value="general">عام</option>
                        <option value="customer">عميل</option>
                        <option value="supplier">مورد</option>
                        <option value="agent">وكيل</option>
                        <option value="debt">دين</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">نوع التذكير</label>
                    <select class="form-select" id="reminderType">
                        <option value="reminder">تذكير</option>
                        <option value="payment">دفع</option>
                        <option value="receipt">استلام</option>
                        <option value="event">حدث</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">تاريخ الاستحقاق</label>
                    <input type="date" class="form-control" id="reminderDate" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">المبلغ (اختياري)</label>
                    <input type="number" class="form-control" id="reminderAmount">
                </div>
                <div class="mb-3">
                    <label class="form-label">ملاحظات</label>
                    <textarea class="form-control" id="reminderNotes" rows="2"></textarea>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="reminderRepeat">
                    <label class="form-check-label" for="reminderRepeat">تكرار يومياً</label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                <button type="button" class="btn btn-success" onclick="saveReminder()">حفظ</button>
            </div>
        </div>
    </div>
</div>

<script>
let currentFilter = 'pending';

async function loadReminders(filter) {
    currentFilter = filter;
    document.querySelectorAll('.btn-outline-success').forEach(b => b.classList.remove('btn-success'));
    event?.target?.classList?.add('btn-success');

    try {
        let url = `${API_BASE}/reminders?per_page=50`;
        if (filter && filter !== 'pending') url += `&status=${filter}`;
        const res = await fetch(url, {headers:{Accept:'application/json'}});
        const json = await res.json();
        const items = json.data || [];

        const grid = document.getElementById('remindersGrid');
        if (!items.length) {
            grid.innerHTML = '<div class="text-center text-muted py-5">لا توجد تذكيرات</div>';
            return;
        }

        const statusColors = { pending: 'warning', today: 'info', completed: 'success', overdue: 'danger' };
        const statusLabels = { pending: 'معلق', today: 'اليوم', completed: 'مكتمل', overdue: 'متأخر' };

        grid.innerHTML = items.map(r => `
            <div class="d-flex align-items-center gap-3 p-3 border-bottom">
                <div class="flex-grow-1">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <span class="fw-semibold">${r.title || ''}</span>
                            <span class="badge bg-${statusColors[r.status] || 'secondary'} ms-2">${statusLabels[r.status] || r.status}</span>
                        </div>
                        <small class="text-muted">${r.due_date || ''}</small>
                    </div>
                    <div class="text-muted small mt-1">${r.notes || ''}</div>
                    ${r.amount ? `<div class="text-success small fw-bold mt-1">${Number(r.amount).toLocaleString()} ريال</div>` : ''}
                </div>
                <div class="d-flex gap-1">
                    <button class="btn btn-sm btn-outline-success" onclick="sendReminderWA(${r.id})" title="إرسال واتساب">
                        <i class="bi bi-whatsapp"></i>
                    </button>
                    ${r.status === 'pending' ? `<button class="btn btn-sm btn-outline-primary" onclick="completeReminder(${r.id})"><i class="bi bi-check"></i></button>` : ''}
                    <button class="btn btn-sm btn-outline-danger" onclick="deleteReminder(${r.id})"><i class="bi bi-trash"></i></button>
                </div>
            </div>
        `).join('');
    } catch (e) {
        document.getElementById('remindersGrid').innerHTML = '<div class="alert alert-danger">حدث خطأ</div>';
    }
}

async function saveReminder() {
    const data = {
        title: document.getElementById('reminderTitle').value,
        entity_type: document.getElementById('reminderEntityType').value,
        reminder_type: document.getElementById('reminderType').value,
        due_date: document.getElementById('reminderDate').value,
        amount: document.getElementById('reminderAmount').value || null,
        notes: document.getElementById('reminderNotes').value,
        repeat_daily: document.getElementById('reminderRepeat').checked,
    };
    if (!data.title || !data.due_date) return showToast('تنبيه', 'العنوان والتاريخ مطلوبان', 'warning');

    const res = await apiFetch('/reminders', { method: 'POST', body: JSON.stringify(data) });
    if (res.id) {
        bootstrap.Modal.getInstance(document.getElementById('addReminderModal')).hide();
        loadReminders(currentFilter);
        showToast('نجاح', 'تم إضافة التذكير', 'success');
    } else {
        showToast('خطأ', 'حدث خطأ أثناء الحفظ', 'danger');
    }
}

function sendReminderWA(id) {
    showToast('تنبيه', 'سيتم فتح واتساب لإرسال التذكير', 'info');
}

async function completeReminder(id) {
    if (!confirmDelete('حذف هذا التذكير؟')) return;
    await apiFetch(`/reminders/${id}`, { method: 'PUT', body: JSON.stringify({ status: 'completed' }) });
    loadReminders(currentFilter);
}

async function deleteReminder(id) {
    if (!confirmDelete('حذف هذا التذكير؟')) return;
    await apiFetch(`/reminders/${id}`, { method: 'DELETE' });
    loadReminders(currentFilter);
    showToast('نجاح', 'تم حذف التذكير', 'success');
}

loadReminders('pending');
</script>
@endsection