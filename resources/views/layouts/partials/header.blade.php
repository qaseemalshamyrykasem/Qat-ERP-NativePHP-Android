<header class="navbar navbar-dark sticky-top px-3" style="background:#1a1a2e;margin-right:260px;z-index:1040;height:56px;">
    {{-- Mobile Toggle --}}
    <button class="btn btn-sm btn-outline-light d-md-none me-2" id="sidebarToggle" type="button">
        <i class="bi bi-list fs-5"></i>
    </button>

    {{-- Page Title --}}
    <span class="navbar-text text-white fw-semibold mb-0">{{ @yield('title', 'لوحة التحكم') }}</span>

    {{-- Right Side Items --}}
    <div class="d-flex align-items-center gap-3 ms-auto">

        {{-- Global Search --}}
        <div class="position-relative d-none d-sm-block">
            <i class="bi bi-search position-absolute end-0 top-50 translate-middle-y text-white-50" style="right:10px;font-size:.8rem;z-index:2;"></i>
            <input type="text" id="globalSearch" class="form-control form-control-sm bg-dark text-white border-secondary rounded-pill" placeholder="بحث سريع (Ctrl+K)" style="max-width:220px;padding-right:30px;font-size:.85rem;height:36px;">
            <div class="global-search-results" id="globalSearchResults"></div>
        </div>

        {{-- Reminders Badge --}}
        <a href="{{ route('reminders.index') }}" class="position-relative text-white-50 text-decoration-none d-none d-sm-block" title="التذكيرات">
            <i class="bi bi-bell" style="font-size:1.1rem;"></i>
            <span id="headerRemindersBadge" class="position-absolute badge rounded-pill bg-warning text-dark" style="top:-6px;left:-8px;font-size:.6rem;display:none;">0</span>
        </a>

        {{-- Notifications --}}
        <div class="position-relative">
            <button class="btn btn-sm btn-link text-white-50 text-decoration-none p-0 position-relative" id="notifBell" type="button">
                <i class="bi bi-bell-fill" style="font-size:1.15rem;"></i>
                <span id="unreadCount" class="position-absolute badge rounded-pill bg-danger" style="top:-6px;left:-8px;font-size:.6rem;display:none;">0</span>
            </button>

            {{-- Notification Dropdown --}}
            <div class="notif-dropdown" id="notifDropdown">
                <div class="d-flex justify-content-between align-items-center px-3 py-2 border-bottom">
                    <strong class="text-dark small">الإشعارات</strong>
                    <span class="text-muted small" id="notifTotalCount">0</span>
                    <a href="#" class="text-decoration-none small" style="color:var(--qat-primary);" id="markAllRead">قراءة الكل</a>
                </div>
                <div id="notifList" style="max-height:300px;overflow-y:auto;">
                    <div class="text-center text-muted py-4 small">لا توجد إشعارات</div>
                </div>
                <div class="text-center py-2 border-top">
                    <a href="{{ route('notifications.index') }}" class="text-decoration-none small" style="color:var(--qat-primary);">عرض جميع الإشعارات</a>
                </div>
            </div>
        </div>

        {{-- User Name --}}
        <div class="d-none d-md-flex align-items-center gap-2 text-white">
            <div class="rounded-circle bg-success d-flex align-items-center justify-content-center text-white fw-bold" style="width:30px;height:30px;min-width:30px;font-size:.8rem;">
                {{ Auth::check() ? mb_substr(Auth::user()->name, 0, 1) : '' }}
            </div>
            <span class="small">{{ Auth::check() ? Auth::user()->name : '' }}</span>
        </div>
    </div>
</header>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Notification toggle
    const notifBell = document.getElementById('notifBell');
    const notifDropdown = document.getElementById('notifDropdown');

    if (notifBell && notifDropdown) {
        notifBell.addEventListener('click', function(e) {
            e.stopPropagation();
            notifDropdown.classList.toggle('show');
        });

        document.addEventListener('click', function(e) {
            if (!notifDropdown.contains(e.target) && !notifBell.contains(e.target)) {
                notifDropdown.classList.remove('show');
            }
        });
    }

    // Mobile sidebar toggle
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebar = document.getElementById('sidebar');
    const sidebarOverlay = document.getElementById('sidebarOverlay');

    if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('show');
            if (sidebarOverlay) sidebarOverlay.classList.toggle('show');
        });
    }

    if (sidebarOverlay && sidebar) {
        sidebarOverlay.addEventListener('click', function() {
            sidebar.classList.remove('show');
            sidebarOverlay.classList.remove('show');
        });
    }

    // Ctrl+K global search focus
    document.addEventListener('keydown', function(e) {
        if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
            e.preventDefault();
            const gs = document.getElementById('globalSearch');
            if (gs) gs.focus();
        }
    });
});
</script>