<aside id="sidebar" class="d-flex flex-column" style="position:fixed;right:0;top:0;bottom:0;width:260px;background:#1a1a2e;z-index:1000;overflow-y:auto;overflow-x:hidden;">
    {{-- Brand --}}
    <div class="px-3 py-3 text-center border-bottom border-secondary">
        <a href="{{ route('dashboard') }}" class="text-decoration-none">
            <span class="text-success fs-4 me-1"><i class="bi bi-flower1"></i></span>
            <span class="text-white fw-bold fs-5">Qat ERP</span>
            <br>
            <small class="text-white-50" style="font-size:.75rem;">نظام تاجر القات</small>
        </a>
    </div>

    {{-- Search --}}
    <div class="px-3 pt-3 pb-2">
        <div class="position-relative">
            <i class="bi bi-search position-absolute end-0 top-50 translate-middle-y text-white-50" style="right:10px;font-size:.85rem;"></i>
            <input type="text" id="sidebarSearch" class="form-control form-control-sm pe-8 rounded-pill" placeholder="بحث في القائمة..." style="padding-right:30px;">
        </div>
    </div>

    {{-- Quick Buttons --}}
    <div class="px-3 pb-2 d-flex gap-2">
        <a href="{{ route('dashboard') }}" class="btn btn-sm btn-outline-light flex-fill rounded-pill sidebar-quick-btn text-nowrap {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <i class="bi bi-grid-1x2-fill me-1"></i> لوحة التحكم
        </a>
        <a href="{{ route('pos.index') }}" class="btn btn-sm btn-success flex-fill rounded-pill sidebar-quick-btn text-nowrap {{ request()->routeIs('pos.index') ? 'active' : '' }}">
            <i class="bi bi-basket-fill me-1"></i> نقطة البيع
        </a>
    </div>

    {{-- Navigation --}}
    <nav class="flex-fill px-2 py-1" id="sidebarNav">
        <div class="accordion accordion-flush" id="sidebarAccordion">

            {{-- Group 1: المبيعات والعمليات --}}
            <div class="accordion-item bg-transparent border-0 sidebar-group" data-group>
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed bg-transparent text-white-50 fw-semibold sidebar-group-header py-2 px-2 shadow-none {{ (request()->routeIs('sales.*') || request()->routeIs('purchases.*') || request()->routeIs('distributions.*') || request()->routeIs('debts.*')) ? '' : 'collapsed' }}"
                        type="button" data-bs-toggle="collapse" data-bs-target="#groupSales"
                        aria-expanded="{{ (request()->routeIs('sales.*') || request()->routeIs('purchases.*') || request()->routeIs('distributions.*') || request()->routeIs('debts.*')) ? 'true' : 'false' }}">
                        <i class="bi bi-cart-fill me-2 text-success"></i>
                        المبيعات والعمليات
                        <i class="bi bi-chevron-left ms-auto small"></i>
                    </button>
                </h2>
                <div id="groupSales" class="accordion-collapse collapse {{ (request()->routeIs('sales.*') || request()->routeIs('purchases.*') || request()->routeIs('distributions.*') || request()->routeIs('debts.*')) ? 'show' : '' }}" data-bs-parent="#sidebarAccordion">
                    <div class="accordion-body p-1 ps-3">
                        <ul class="nav flex-column gap-1">
                            <li><a href="{{ route('sales.index') }}" class="nav-link text-white {{ request()->routeIs('sales.*') ? 'active bg-success rounded' : '' }}"><i class="bi bi-receipt me-2"></i>المبيعات</a></li>
                            <li><a href="{{ route('purchases.index') }}" class="nav-link text-white {{ request()->routeIs('purchases.*') ? 'active bg-success rounded' : '' }}"><i class="bi bi-bag-check me-2"></i>المشتريات</a></li>
                            <li><a href="{{ route('distributions.index') }}" class="nav-link text-white {{ request()->routeIs('distributions.*') ? 'active bg-success rounded' : '' }}"><i class="bi bi-truck me-2"></i>التوزيع</a></li>
                            <li><a href="{{ route('debts.index') }}" class="nav-link text-white {{ request()->routeIs('debts.*') ? 'active bg-success rounded' : '' }}"><i class="bi bi-exclamation-triangle me-2"></i>الديون</a></li>
                        </ul>
                    </div>
                </div>
            </div>

            {{-- Group 2: الأشخاص --}}
            <div class="accordion-item bg-transparent border-0 sidebar-group" data-group>
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed bg-transparent text-white-50 fw-semibold sidebar-group-header py-2 px-2 shadow-none"
                        type="button" data-bs-toggle="collapse" data-bs-target="#groupPeople"
                        aria-expanded="{{ (request()->routeIs('customers.*') || request()->routeIs('suppliers.*') || request()->routeIs('agents.*')) ? 'true' : 'false' }}">
                        <i class="bi bi-people-fill me-2 text-success"></i>
                        الأشخاص
                        <i class="bi bi-chevron-left ms-auto small"></i>
                    </button>
                </h2>
                <div id="groupPeople" class="accordion-collapse collapse {{ (request()->routeIs('customers.*') || request()->routeIs('suppliers.*') || request()->routeIs('agents.*')) ? 'show' : '' }}" data-bs-parent="#sidebarAccordion">
                    <div class="accordion-body p-1 ps-3">
                        <ul class="nav flex-column gap-1">
                            <li><a href="{{ route('customers.index') }}" class="nav-link text-white {{ request()->routeIs('customers.*') ? 'active bg-success rounded' : '' }}"><i class="bi bi-person me-2"></i>العملاء</a></li>
                            <li><a href="{{ route('suppliers.index') }}" class="nav-link text-white {{ request()->routeIs('suppliers.*') ? 'active bg-success rounded' : '' }}"><i class="bi bi-person-gear me-2"></i>الموردون</a></li>
                            <li><a href="{{ route('agents.index') }}" class="nav-link text-white {{ request()->routeIs('agents.*') ? 'active bg-success rounded' : '' }}"><i class="bi bi-person-badge me-2"></i>الوكلاء</a></li>
                        </ul>
                    </div>
                </div>
            </div>

            {{-- Group 3: المنتجات والمخزون --}}
            <div class="accordion-item bg-transparent border-0 sidebar-group" data-group>
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed bg-transparent text-white-50 fw-semibold sidebar-group-header py-2 px-2 shadow-none"
                        type="button" data-bs-toggle="collapse" data-bs-target="#groupProducts"
                        aria-expanded="{{ (request()->routeIs('products.*') || request()->routeIs('currencies.*')) ? 'true' : 'false' }}">
                        <i class="bi bi-box-seam-fill me-2 text-success"></i>
                        المنتجات والمخزون
                        <i class="bi bi-chevron-left ms-auto small"></i>
                    </button>
                </h2>
                <div id="groupProducts" class="accordion-collapse collapse {{ (request()->routeIs('products.*') || request()->routeIs('currencies.*')) ? 'show' : '' }}" data-bs-parent="#sidebarAccordion">
                    <div class="accordion-body p-1 ps-3">
                        <ul class="nav flex-column gap-1">
                            <li><a href="{{ route('products.index') }}" class="nav-link text-white {{ request()->routeIs('products.*') ? 'active bg-success rounded' : '' }}"><i class="bi bi-box me-2"></i>المنتجات</a></li>
                            <li><a href="{{ route('currencies.index') }}" class="nav-link text-white {{ request()->routeIs('currencies.*') ? 'active bg-success rounded' : '' }}"><i class="bi bi-currency-exchange me-2"></i>العملات</a></li>
                        </ul>
                    </div>
                </div>
            </div>

            {{-- Group 4: المالية --}}
            <div class="accordion-item bg-transparent border-0 sidebar-group" data-group>
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed bg-transparent text-white-50 fw-semibold sidebar-group-header py-2 px-2 shadow-none"
                        type="button" data-bs-toggle="collapse" data-bs-target="#groupFinance"
                        aria-expanded="{{ (request()->routeIs('daily-session.*') || request()->routeIs('expenses.*') || request()->routeIs('agent-settlements.*') || request()->routeIs('receipt-vouchers.*') || request()->routeIs('payment-vouchers.*') || request()->routeIs('transfers.*')) ? 'true' : 'false' }}">
                        <i class="bi bi-cash-coin me-2 text-success"></i>
                        المالية
                        <i class="bi bi-chevron-left ms-auto small"></i>
                    </button>
                </h2>
                <div id="groupFinance" class="accordion-collapse collapse {{ (request()->routeIs('daily-session.*') || request()->routeIs('expenses.*') || request()->routeIs('agent-settlements.*') || request()->routeIs('receipt-vouchers.*') || request()->routeIs('payment-vouchers.*') || request()->routeIs('transfers.*')) ? 'show' : '' }}" data-bs-parent="#sidebarAccordion">
                    <div class="accordion-body p-1 ps-3">
                        <ul class="nav flex-column gap-1">
                            <li><a href="{{ route('daily-session.index') }}" class="nav-link text-white {{ request()->routeIs('daily-session.*') ? 'active bg-success rounded' : '' }}"><i class="bi bi-calendar-day me-2"></i>الجلسة اليومية</a></li>
                            <li><a href="{{ route('expenses.index') }}" class="nav-link text-white {{ request()->routeIs('expenses.*') ? 'active bg-success rounded' : '' }}"><i class="bi bi-wallet2 me-2"></i>المصروفات</a></li>
                            <li><a href="{{ route('agent-settlements.index') }}" class="nav-link text-white {{ request()->routeIs('agent-settlements.*') ? 'active bg-success rounded' : '' }}"><i class="bi bi-arrow-left-right me-2"></i>تحاسب الوكلاء</a></li>
                            <li><a href="{{ route('receipt-vouchers.index') }}" class="nav-link text-white {{ request()->routeIs('receipt-vouchers.*') ? 'active bg-success rounded' : '' }}"><i class="bi bi-arrow-down-circle me-2"></i>سندات القبض</a></li>
                            <li><a href="{{ route('payment-vouchers.index') }}" class="nav-link text-white {{ request()->routeIs('payment-vouchers.*') ? 'active bg-success rounded' : '' }}"><i class="bi bi-arrow-up-circle me-2"></i>سندات الصرف</a></li>
                            <li><a href="{{ route('transfers.index') }}" class="nav-link text-white {{ request()->routeIs('transfers.*') ? 'active bg-success rounded' : '' }}"><i class="bi bi-send me-2"></i>التحويلات</a></li>
                        </ul>
                    </div>
                </div>
            </div>

            {{-- Group 5: المحاسبة والتقارير --}}
            <div class="accordion-item bg-transparent border-0 sidebar-group" data-group>
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed bg-transparent text-white-50 fw-semibold sidebar-group-header py-2 px-2 shadow-none"
                        type="button" data-bs-toggle="collapse" data-bs-target="#groupAccounting"
                        aria-expanded="{{ (request()->routeIs('reports.*') || request()->routeIs('accounts.*') || request()->routeIs('journal-entries.*')) ? 'true' : 'false' }}">
                        <i class="bi bi-bar-chart-fill me-2 text-success"></i>
                        المحاسبة والتقارير
                        <i class="bi bi-chevron-left ms-auto small"></i>
                    </button>
                </h2>
                <div id="groupAccounting" class="accordion-collapse collapse {{ (request()->routeIs('reports.*') || request()->routeIs('accounts.*') || request()->routeIs('journal-entries.*')) ? 'show' : '' }}" data-bs-parent="#sidebarAccordion">
                    <div class="accordion-body p-1 ps-3">
                        <ul class="nav flex-column gap-1">
                            <li><a href="{{ route('reports.index') }}" class="nav-link text-white {{ request()->routeIs('reports.*') ? 'active bg-success rounded' : '' }}"><i class="bi bi-graph-up me-2"></i>التقارير</a></li>
                            <li><a href="{{ route('accounts.index') }}" class="nav-link text-white {{ request()->routeIs('accounts.*') ? 'active bg-success rounded' : '' }}"><i class="bi bi-journal-bookmark-fill me-2"></i>دليل الحسابات</a></li>
                            <li><a href="{{ route('journal-entries.index') }}" class="nav-link text-white {{ request()->routeIs('journal-entries.*') ? 'active bg-success rounded' : '' }}"><i class="bi bi-journal-text me-2"></i>القيود اليومية</a></li>
                        </ul>
                    </div>
                </div>
            </div>

            {{-- Group 6: النظام --}}
            <div class="accordion-item bg-transparent border-0 sidebar-group" data-group>
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed bg-transparent text-white-50 fw-semibold sidebar-group-header py-2 px-2 shadow-none"
                        type="button" data-bs-toggle="collapse" data-bs-target="#groupSystem"
                        aria-expanded="{{ (request()->routeIs('reminders.*') || request()->routeIs('settings.*') || request()->routeIs('users.*')) ? 'true' : 'false' }}">
                        <i class="bi bi-gear-fill me-2 text-success"></i>
                        النظام
                        <i class="bi bi-chevron-left ms-auto small"></i>
                    </button>
                </h2>
                <div id="groupSystem" class="accordion-collapse collapse {{ (request()->routeIs('reminders.*') || request()->routeIs('settings.*') || request()->routeIs('users.*')) ? 'show' : '' }}" data-bs-parent="#sidebarAccordion">
                    <div class="accordion-body p-1 ps-3">
                        <ul class="nav flex-column gap-1">
                            <li class="position-relative">
                                <a href="{{ route('reminders.index') }}" class="nav-link text-white {{ request()->routeIs('reminders.*') ? 'active bg-success rounded' : '' }}">
                                    <i class="bi bi-bell me-2"></i>التذكيرات
                                </a>
                                <span id="remindersBadge" class="position-absolute badge rounded-pill bg-danger" style="top:4px;left:12px;font-size:.65rem;display:none;">0</span>
                            </li>
                            <li><a href="{{ route('settings.index') }}" class="nav-link text-white {{ request()->routeIs('settings.*') ? 'active bg-success rounded' : '' }}"><i class="bi bi-sliders me-2"></i>الإعدادات</a></li>
                            <li><a href="{{ route('users.index') }}" class="nav-link text-white {{ request()->routeIs('users.*') ? 'active bg-success rounded' : '' }}"><i class="bi bi-person-circle me-2"></i>المستخدمون</a></li>
                        </ul>
                    </div>
                </div>
            </div>

        </div>
    </nav>

    {{-- User Info --}}
    <div class="border-top border-secondary px-3 py-2 mt-auto">
        <div class="d-flex align-items-center gap-2">
            <div class="rounded-circle bg-success d-flex align-items-center justify-content-center text-white fw-bold" style="width:36px;height:36px;min-width:36px;">
                {{ Auth::check() ? mb_substr(Auth::user()->name, 0, 1) : '' }}
            </div>
            <div class="flex-fill overflow-hidden">
                <div class="text-white text-truncate small fw-semibold">{{ Auth::check() ? Auth::user()->name : '' }}</div>
                <div class="text-white-50 text-truncate" style="font-size:.72rem;">{{ Auth::check() ? (Auth::user()->role ?? 'مستخدم') : '' }}</div>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn btn-sm btn-outline-secondary rounded-circle" style="width:32px;height:32px;padding:0;" title="تسجيل الخروج">
                    <i class="bi bi-box-arrow-left" style="font-size:.85rem;"></i>
                </button>
            </form>
        </div>
    </div>
</aside>

{{-- Sidebar Search Filter Script --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('sidebarSearch');
    if (!searchInput) return;

    searchInput.addEventListener('input', function() {
        const query = this.value.trim().toLowerCase();
        const groups = document.querySelectorAll('#sidebarNav [data-group]');

        groups.forEach(function(group) {
            const items = group.querySelectorAll('.nav-link');
            const header = group.querySelector('.sidebar-group-header');
            let hasVisible = false;

            items.forEach(function(item) {
                const text = item.textContent.toLowerCase();
                if (!query || text.includes(query)) {
                    item.closest('li').style.display = '';
                    hasVisible = true;
                } else {
                    item.closest('li').style.display = 'none';
                }
            });

            if (!query) {
                group.style.display = '';
            } else {
                group.style.display = hasVisible ? '' : 'none';
                // Expand group if it has matching items
                if (hasVisible) {
                    const collapse = group.querySelector('.accordion-collapse');
                    if (collapse && !collapse.classList.contains('show')) {
                        const bsCollapse = bootstrap.Collapse.getOrCreateInstance(collapse, { toggle: false });
                        bsCollapse.show();
                    }
                }
            }
        });
    });
});
</script>