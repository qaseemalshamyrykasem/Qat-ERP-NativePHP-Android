/* Qat ERP - Client JS - Enhanced */
(function () {
    'use strict';

    // ========== Sidebar Toggle (mobile) ==========
    const toggle = document.getElementById('sidebarToggle');
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');

    function openSidebar() {
        sidebar?.classList.add('show');
        overlay?.classList.add('show');
        document.body.style.overflow = 'hidden';
    }
    function closeSidebar() {
        sidebar?.classList.remove('show');
        overlay?.classList.remove('show');
        document.body.style.overflow = '';
    }

    toggle?.addEventListener('click', openSidebar);
    overlay?.addEventListener('click', closeSidebar);

    // Close sidebar on mobile when nav link clicked
    document.querySelectorAll('#sidebar .nav-link:not(.sidebar-group-header)').forEach(link => {
        link.addEventListener('click', () => {
            if (window.innerWidth <= 768) closeSidebar();
        });
    });

    // ========== Auto-hide alerts ==========
    setTimeout(() => {
        document.querySelectorAll('.alert-dismissible').forEach(a => a.classList.add('fade'));
    }, 5000);

    // ========== Notification Polling ==========
    let lastNotifCount = 0;
    const POLL_INTERVAL = 30000;
    let pollTimer = null;

    async function fetchUnreadCount() {
        if (document.hidden) return;
        try {
            const res = await fetch(`${API_BASE}/notifications/unread-count`, {
                headers: { Accept: 'application/json' },
                credentials: 'same-origin',
            });
            const json = await res.json();
            if (json.success && json.count !== undefined) {
                const badge = document.getElementById('unreadCount');
                if (json.count > 0) {
                    if (badge) {
                        badge.textContent = json.count > 9 ? '9+' : json.count;
                        badge.style.display = 'inline';
                        if (json.count > lastNotifCount) {
                            badge.classList.remove('badge-animate');
                            void badge.offsetWidth; // reflow
                            badge.classList.add('badge-animate');
                            playNotificationSound();
                            showToast('إشعار جديد', 'لديك إشعارات غير مقروءة', 'info');
                            updatePageTitle(json.count);
                        }
                    }
                } else {
                    if (badge) badge.style.display = 'none';
                    document.title = 'Qat ERP';
                }
                lastNotifCount = json.count;
            }
        } catch (e) { /* silent */ }
    }

    function startPolling() {
        fetchUnreadCount();
        pollTimer = setInterval(fetchUnreadCount, POLL_INTERVAL);
    }

    function stopPolling() {
        if (pollTimer) clearInterval(pollTimer);
    }

    // Visibility API - pause when tab hidden
    document.addEventListener('visibilitychange', () => {
        if (document.hidden) stopPolling();
        else startPolling();
    });

    startPolling();

    // ========== Notification Dropdown ==========
    const notifBtn = document.getElementById('notifToggle');
    const notifDropdown = document.getElementById('notifDropdown');

    if (notifBtn && notifDropdown) {
        notifBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            notifDropdown.classList.toggle('show');
            if (notifDropdown.classList.contains('show')) {
                loadRecentNotifications();
            }
        });
        document.addEventListener('click', (e) => {
            if (!notifDropdown.contains(e.target)) {
                notifDropdown.classList.remove('show');
            }
        });
    }

    async function loadRecentNotifications() {
        try {
            const res = await fetch(`${API_BASE}/notifications?per_page=5`, {
                headers: { Accept: 'application/json' },
                credentials: 'same-origin',
            });
            const json = await res.json();
            const list = document.getElementById('notifList');
            if (list && json.data) {
                list.innerHTML = json.data.length ? json.data.map(n => `
                    <div class="notif-item ${n.is_read ? '' : 'unread'}" data-id="${n.id}" data-type="${n.reference_type}" data-ref="${n.reference_id || ''}">
                        <div class="d-flex gap-2">
                            <div class="notif-icon ${n.type || 'system'}">
                                <i class="bi bi-${getNotifIcon(n.type)}"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="fw-semibold small">${n.title}</div>
                                <div class="text-muted small" style="font-size:.8rem">${n.message || ''}</div>
                                <div class="text-muted" style="font-size:.7rem">${n.time_ago || ''}</div>
                            </div>
                        </div>
                    </div>
                `).join('') : '<div class="text-center text-muted py-4 small">لا توجد إشعارات</div>';
            }
        } catch (e) { /* silent */ }
    }

    function getNotifIcon(type) {
        const icons = { sale: 'cart-check', purchase: 'bag-check', debt: 'exclamation-triangle', payment: 'cash-coin', expense: 'receipt', reminder: 'bell', stock: 'box-seam', system: 'gear', info: 'info-circle', warning: 'exclamation-circle', success: 'check-circle', error: 'x-circle' };
        return icons[type] || 'bell';
    }

    // ========== Sidebar Search ==========
    const sidebarSearch = document.getElementById('sidebarSearch');
    if (sidebarSearch) {
        sidebarSearch.addEventListener('input', function () {
            const q = this.value.trim().toLowerCase();
            document.querySelectorAll('#sidebar .nav-link').forEach(link => {
                const text = link.textContent.trim().toLowerCase();
                const item = link.closest('li') || link;
                if (!q || text.includes(q)) {
                    item.style.display = '';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    }

    // ========== Global Search (Ctrl+K) ==========
    const globalSearch = document.getElementById('globalSearch');
    const globalResults = document.getElementById('globalSearchResults');

    if (globalSearch && globalResults) {
        document.addEventListener('keydown', (e) => {
            if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                e.preventDefault();
                globalSearch.focus();
            }
            if (e.key === 'Escape' && document.activeElement === globalSearch) {
                globalResults.classList.remove('show');
                globalSearch.blur();
            }
        });

        let searchTimer;
        globalSearch.addEventListener('input', function () {
            clearTimeout(searchTimer);
            const q = this.value.trim();
            if (q.length < 2) { globalResults.classList.remove('show'); return; }
            searchTimer = setTimeout(() => performGlobalSearch(q), 400);
        });
    }

    async function performGlobalSearch(query) {
        try {
            const res = await fetch(`${API_BASE}/search?q=${encodeURIComponent(query)}&per_page=5`, {
                headers: { Accept: 'application/json' },
                credentials: 'same-origin',
            });
            const json = await res.json();
            let html = '';

            if (json.customers?.length) {
                html += '<div class="p-2 border-bottom"><small class="text-muted fw-bold"><i class="bi bi-people"></i> العملاء</small></div>';
                json.customers.forEach(c => {
                    html += `<a href="/customers" class="dropdown-item"><div class="fw-semibold">${c.name}</div><small class="text-muted">${c.phone || ''} - رصيد: ${Number(c.remaining||0).toLocaleString()}</small></a>`;
                });
            }
            if (json.products?.length) {
                html += '<div class="p-2 border-bottom"><small class="text-muted fw-bold"><i class="bi bi-box-seam"></i> المنتجات</small></div>';
                json.products.forEach(p => {
                    html += `<a href="/products" class="dropdown-item"><div class="fw-semibold">${p.name}</div><small class="text-muted">سعر: ${Number(p.sell_price||0).toLocaleString()} - متوفر: ${p.quantity||0}</small></a>`;
                });
            }
            if (json.sales?.length) {
                html += '<div class="p-2 border-bottom"><small class="text-muted fw-bold"><i class="bi bi-cart"></i> الفواتير</small></div>';
                json.sales.forEach(s => {
                    html += `<a href="/sales" class="dropdown-item"><div class="fw-semibold">${s.invoice_no}</div><small class="text-muted">${Number(s.final_amount||0).toLocaleString()} ريال</small></a>`;
                });
            }

            if (!html) html = '<div class="text-center text-muted p-3 small">لا توجد نتائج</div>';
            html += `<div class="p-2 text-center"><a href="#" class="small text-success" onclick="document.getElementById('globalSearchResults').classList.remove('show')">إغلاق</a></div>`;

            globalResults.innerHTML = html;
            globalResults.classList.add('show');
        } catch (e) { /* silent */ }
    }

    // ========== Notification Sound ==========
    function playNotificationSound() {
        try {
            const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
            const oscillator = audioCtx.createOscillator();
            const gainNode = audioCtx.createGain();
            oscillator.connect(gainNode);
            gainNode.connect(audioCtx.destination);
            oscillator.frequency.value = 800;
            oscillator.type = 'sine';
            gainNode.gain.value = 0.1;
            oscillator.start();
            gainNode.gain.exponentialRampToValueAtTime(0.001, audioCtx.currentTime + 0.3);
            oscillator.stop(audioCtx.currentTime + 0.3);
        } catch (e) { /* silent */ }
    }

    function updatePageTitle(count) {
        document.title = `(${count}) ${document.title.replace(/^\(\d+\)\s*/, '')}`;
    }

    // ========== Toast Notifications ==========
    window.showToast = function (title, message, type = 'success') {
        const colors = { success: '#2E7D32', danger: '#C62828', warning: '#F57F17', info: '#1565C0' };
        const icons = { success: 'check-circle', danger: 'x-circle', warning: 'exclamation-triangle', info: 'info-circle' };
        const toast = document.createElement('div');
        toast.className = 'toast-notification';
        toast.innerHTML = `<div class="d-flex align-items-center gap-2 px-4 py-3 rounded-3 shadow-lg text-white" style="background:${colors[type]||colors.info};min-width:280px">
            <i class="bi bi-${icons[type]||icons.info} fs-5"></i>
            <div><div class="fw-semibold" style="font-size:.9rem">${title}</div><div style="font-size:.8rem;opacity:.9">${message}</div></div>
        </div>`;
        document.body.appendChild(toast);
        requestAnimationFrame(() => toast.classList.add('show'));
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 400);
        }, 3000);
    };

    // ========== Generic Helpers ==========
    window.confirmDelete = function (message = 'تأكيد الحذف؟ لا يمكن التراجع عن هذا الإجراء.') {
        return confirm(message);
    };

    window.apiFetch = async function (path, options = {}) {
        const opts = { ...options, headers: { Accept: 'application/json', ...options.headers } };
        if (!opts.headers['X-CSRF-TOKEN'] && window.CSRF_TOKEN) opts.headers['X-CSRF-TOKEN'] = window.CSRF_TOKEN;
        const res = await fetch(`${API_BASE}${path}`, opts);
        return res.json();
    };

    // ========== Print (enhanced) ==========
    window.printArea = function (selector = '#printableArea') {
        const el = document.querySelector(selector);
        if (!el) return window.print();
        const w = window.open('', '_blank');
        w.document.write(`<!DOCTYPE html><html dir="rtl" lang="ar"><head><meta charset="UTF-8"><title>طباعة</title>
            <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
            <style>*{margin:0;padding:0;box-sizing:border-box}body{font-family:'Cairo',sans-serif;padding:20px;color:#333}
            table{width:100%;border-collapse:collapse;margin:10px 0}th{background:#1B5E20;color:#fff;padding:8px;text-align:right}
            td{padding:6px 8px;border-bottom:1px solid #eee}tr:nth-child(even){background:#f9f9f9}
            .header{display:flex;justify-content:space-between;align-items:center;border-bottom:2px solid #1B5E20;padding-bottom:10px;margin-bottom:15px}
            .footer{text-align:center;margin-top:20px;font-size:.85rem;color:#999;border-top:1px solid #eee;padding-top:10px}
            .totals{margin-top:10px;padding:10px;background:#f0f7f0;border:1px solid #1B5E20}
            .totals .row{display:flex;justify-content:space-between;padding:3px 0}
            @media print{body{padding:0}}</style></head>
            <body>${el.innerHTML}</body></html>`);
        w.document.close();
        setTimeout(() => w.print(), 500);
    };

    // ========== CSV Export ==========
    window.exportCsv = function (data, filename = 'export.csv') {
        if (!data || !data.length) return showToast('تنبيه', 'لا توجد بيانات للتصدير', 'warning');
        const headers = Object.keys(data[0]);
        const lines = [headers.join(',')].concat(
            data.map(row => headers.map(h => `"${String(row[h] ?? '').replace(/"/g, '""')}"`).join(','))
        );
        const blob = new Blob(['\ufeff' + lines.join('\n')], { type: 'text/csv;charset=utf-8' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url; a.download = filename;
        a.click();
        URL.revokeObjectURL(url);
        showToast('نجاح', 'تم التصدير بنجاح', 'success');
    };

    // ========== WhatsApp Helper ==========
    window.whatsappSend = async function (entityType, entityId, endpoint = null) {
        try {
            const url = endpoint || `${API_BASE}/whatsapp/${entityType}/${entityId}/reminder`;
            const json = await window.apiFetch(url);
            if (json.success && json.link) {
                window.open(json.link, '_blank');
            } else {
                showToast('خطأ', json.message || 'حدث خطأ أثناء إنشاء رابط الواتساب', 'danger');
            }
        } catch (e) {
            showToast('خطأ', 'حدث خطأ أثناء الاتصال', 'danger');
        }
    };

    // ========== Format Money ==========
    window.formatMoney = function (amount, currency = 'ريال') {
        return Number(amount || 0).toLocaleString('ar-SA') + ' ' + currency;
    };

    // ========== Status Badge Helper ==========
    window.statusBadge = function (status) {
        const map = {
            active: ['نشط', 'success'], paid: ['مدفوع', 'success'], completed: ['مكتمل', 'success'],
            pending: ['معلق', 'warning'], partial: ['جزئي', 'warning'], open: ['مفتوحة', 'info'],
            overdue: ['متأخر', 'danger'], cancelled: ['ملغي', 'danger'], closed: ['مغلقة', 'secondary'],
            draft: ['مسودة', 'secondary'], posted: ['مرحّل', 'info'],
        };
        const [label, color] = map[status] || [status, 'secondary'];
        return `<span class="badge bg-${color}">${label}</span>`;
    };

})();