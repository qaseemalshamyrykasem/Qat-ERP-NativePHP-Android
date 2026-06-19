@extends('layouts.app')
@section('title', 'لوحة التحكم')
@section('content')
<div class="container-fluid py-4">
    <h2 class="mb-4"><i class="bi bi-speedometer2 text-success"></i> لوحة التحكم</h2>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-success shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted small">مبيعات اليوم</div>
                            <div class="h4 mb-0 text-success">{{ number_format($overview['today_sales'], 0) }}</div>
                        </div>
                        <i class="bi bi-cart-check fs-1 text-success opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-info shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted small">مشتريات اليوم</div>
                            <div class="h4 mb-0 text-info">{{ number_format($overview['today_purchases'], 0) }}</div>
                        </div>
                        <i class="bi bi-bag-plus fs-1 text-info opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-warning shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted small">مصروفات اليوم</div>
                            <div class="h4 mb-0 text-warning">{{ number_format($overview['today_expenses'], 0) }}</div>
                        </div>
                        <i class="bi bi-cash-coin fs-1 text-warning opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-danger shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted small">ديون متبقية</div>
                            <div class="h4 mb-0 text-danger">{{ number_format($overview['debts_total'], 0) }}</div>
                        </div>
                        <i class="bi bi-credit-card fs-1 text-danger opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-light"><i class="bi bi-graph-up"></i> ملخص الشهر</div>
                <div class="card-body">
                    <table class="table table-sm mb-0">
                        <tr><td>مبيعات الشهر</td><td class="text-end fw-bold text-success">{{ number_format($overview['month_sales'], 0) }}</td></tr>
                        <tr><td>مصروفات الشهر</td><td class="text-end fw-bold text-warning">{{ number_format($overview['month_expenses'], 0) }}</td></tr>
                        <tr><td>ديون متأخرة</td><td class="text-end fw-bold text-danger">{{ $overview['debts_overdue'] }}</td></tr>
                        <tr><td>منتجات منخفضة المخزون</td><td class="text-end fw-bold text-warning">{{ $overview['products_low_stock'] }}</td></tr>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-light"><i class="bi bi-people"></i> الإحصائيات</div>
                <div class="card-body">
                    <table class="table table-sm mb-0">
                        <tr><td>الوكلاء النشطون</td><td class="text-end fw-bold">{{ $overview['agents_count'] }}</td></tr>
                        <tr><td>الموردون</td><td class="text-end fw-bold">{{ $overview['suppliers_count'] }}</td></tr>
                        <tr><td>العملاء</td><td class="text-end fw-bold">{{ $overview['customers_count'] }}</td></tr>
                        <tr><td>المنتجات</td><td class="text-end fw-bold">{{ $overview['products_count'] }}</td></tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
