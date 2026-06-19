@extends('layouts.app')
@section('title', 'مركز الإشعارات')
@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="bi bi-inbox text-success"></i> مركز الإشعارات</h2>
        <div class="d-flex gap-2">
            <button class="btn btn-sm btn-outline-primary" onclick="markAllRead()">
                <i class="bi bi-check2-all"></i> قراءة الكل
            </button>
        </div>
    </div>

    <!-- Filter tabs -->
    <div class="d-flex gap-2 mb-3 flex-wrap">
        <button class="btn btn-sm btn-success" onclick="loadNotifs('')" id="filterAll">الكل</button>
        <button class="btn btn-sm btn-outline-success" onclick="loadNotifs('sale')">المبيعات</button>
        <button class="btn btn-sm btn-outline-success" onclick="loadNotifs('debt')">الديون</button>
        <button class="btn btn-sm btn-outline-success" onclick="loadNotifs('payment')">المدفوعات</button>
        <button class="btn btn-sm btn-outline-success" onclick="loadNotifs('stock')">المخزون</button>
        <button class="btn btn-sm btn-outline-success" onclick="loadNotifs('system')">النظام</button>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div id="notifGrid">جارٍ التحميل...</div>
        </div>
    </div>

    <div class="text-center mt-3">
        <button class="btn btn-sm btn-outline-secondary" id="loadMoreBtn" onclick="loadMoreNotifs()" style="display:none">
            <i class="bi bi-arrow-down-circle"></i> تحميل المزيد
        </button>
    </div>
</div>

<script>
let currentFilter = '';
let currentPage = 1;
const perPage = 20;

async function loadNotifs(type, page = 1) {
    currentFilter = type;
    currentPage = page;
    document.querySelectorAll('.btn-outline-success').forEach(b => b.classList.remove('btn-success'));
    event?.target?.classList?.add('btn-success');

    try {
        let url = `${API_BASE}/notifications?per_page=${perPage}&page=${page}`;
        if (type) url += `&type=${type}`;
        const res = await fetch(url, {headers:{Accept:'application/json'}});
        const json = await res.json();
        const notifs = json.data || [];

        const grid = document.getElementById('notifGrid');
        if (!notifs.length) {
            grid.innerHTML = '<div class="text-center text-muted py-5">لا توجد إشعارات</div>';
            document.getElementById('loadMoreBtn').style.display = 'none';
            return;
        }

        grid.innerHTML = notifs.map(n => `
            <div class="d-flex align-items-start gap-3 p-3 border-bottom ${n.is_read ? '' : 'bg-light'}" data-id="${n.id}">
                <div class="notif-icon ${n.type || 'system'} flex-shrink-0">
                    <i class="bi bi-${getIcon(n.type)}"></i>
                </div>
                <div class="flex-grow-1">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="fw-semibold">${n.title}</div>
                        <small class="text-muted" style="font-size:.75rem">${n.created_at || ''}</small>
                    </div>
                    <div class="text-muted small mt-1">${n.message || ''}</div>
                </div>
                <div class="d-flex gap-1">
                    ${!n.is_read ? `<button class="btn btn-sm btn-outline-secondary" onclick="markRead(${n.id})"><i class="bi bi-check"></i></button>` : ''}
                </div>
            </div>
        `).join('');

        document.getElementById('loadMoreBtn').style.display = json.total > page * perPage ? '' : 'none';
    } catch (e) {
        document.getElementById('notifGrid').innerHTML = '<div class="alert alert-danger">حدث خطأ في تحميل الإشعارات</div>';
    }
}

async function markRead(id) {
    await apiFetch(`/notifications/${id}/mark-read`, { method: 'POST' });
    loadNotifs(currentFilter, currentPage);
    fetchUnreadCount?.();
}

async function markAllRead() {
    await apiFetch('/notifications/mark-all-read', { method: 'POST' });
    loadNotifs(currentFilter, currentPage);
    showToast('نجاح', 'تم تحديد الكل كمقروء', 'success');
}

function getIcon(type) {
    const icons = { sale:'cart-check', purchase:'bag-check', debt:'exclamation-triangle', payment:'cash-coin', expense:'receipt', reminder:'bell', stock:'box-seam', system:'gear', info:'info-circle', warning:'exclamation-circle', success:'check-circle', error:'x-circle' };
    return icons[type] || 'bell';
}

loadNotifs('');
</script>
@endsection