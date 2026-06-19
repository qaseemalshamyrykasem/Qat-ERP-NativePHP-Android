<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
<meta charset="UTF-8">
<title>فاتورة بيع</title>
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:'Cairo',sans-serif;padding:20mm 25mm;color:#333;font-size:11pt;direction:rtl}
.header{display:flex;justify-content:space-between;align-items:center;border-bottom:3px solid #1B5E20;padding-bottom:15pt;margin-bottom:15pt}
.store-name{font-size:18pt;font-weight:bold;color:#1B5E20}
.doc-title{text-align:center;font-size:16pt;font-weight:bold;color:#1B5E20;margin:10pt 0;padding:8pt;background:#E8F5E9;border-radius:8pt}
.info-grid{display:grid;grid-template-columns:1fr 1fr;gap:10pt;margin:10pt 0}
.info-box{background:#f9f9f9;padding:10pt;border-radius:6pt;border:1px solid #eee}
.info-box h4{font-size:10pt;color:#888;margin-bottom:5pt}
table{width:100%;border-collapse:collapse;margin:15pt 0}
th{background:#1B5E20;color:#fff;padding:10pt;text-align:right;font-size:10pt}
td{padding:8pt 10pt;border-bottom:1px solid #eee;font-size:10pt}
tr:nth-child(even){background:#f9f9f9}
.totals{margin-top:15pt;padding:12pt;background:#f0f7f0;border:1px solid #1B5E20;border-radius:8pt}
.totals .row{display:flex;justify-content:space-between;padding:4pt 0;font-size:11pt}
.totals .grand{font-size:14pt;font-weight:bold;color:#1B5E20;border-top:2px solid #1B5E20;padding-top:8pt;margin-top:5pt}
.footer{text-align:center;margin-top:30pt;font-size:9pt;color:#999;border-top:1px solid #eee;padding-top:10pt}
.notes{margin-top:10pt;padding:10pt;background:#fffde7;border:1px solid #ffe082;border-radius:6pt;font-size:10pt}
</style>
</head>
<body>
<div class="header">
    <div>
        <div class="store-name">{{ config('app.name', 'المتجر') }}</div>
        <div style="color:#666;font-size:10pt">هاتف: {{ config('app.phone', '') }}</div>
    </div>
    <div style="text-align:left">
        <div style="font-weight:bold;font-size:13pt">فاتورة بيع</div>
        <div style="color:#666;font-size:10pt">رقم: {{ $sale->invoice_no ?? '---' }}</div>
        <div style="color:#666;font-size:10pt">التاريخ: {{ ($sale->sale_date ?? now()->format('Y-m-d')) }}</div>
    </div>
</div>

<div class="info-grid">
    <div class="info-box">
        <h4>معلومات العميل</h4>
        <div><strong>الاسم:</strong> {{ $sale->customer->name ?? '---' }}</div>
        <div><strong>الهاتف:</strong> {{ $sale->customer->phone ?? '---' }}</div>
    </div>
    <div class="info-box">
        <h4>معلومات الفاتورة</h4>
        <div><strong>رقم الفاتورة:</strong> {{ $sale->invoice_no ?? '---' }}</div>
        <div><strong>طريقة الدفع:</strong> {{ $sale->payment_method ?? '---' }}</div>
    </div>
</div>

<table>
    <thead>
        <tr><th>#</th><th>المنتج</th><th>النوع</th><th>الكمية</th><th>الوحدة</th><th>سعر الوحدة</th><th>المجموع</th></tr>
    </thead>
    <tbody>
        @foreach(($sale->items ?? []) as $index => $item)
        <tr>
            <td>{{ $index + 1 }}</td>
            <td>{{ $item->description ?? $item->product->name ?? '---' }}</td>
            <td>{{ $item->quality ?? '' }}</td>
            <td>{{ $item->quantity }}</td>
            <td>{{ $item->unit ?? '' }}</td>
            <td>{{ number_format($item->unit_price, 0) }}</td>
            <td>{{ number_format($item->total_price, 0) }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

<div class="totals">
    <div class="row"><span>الإجمالي</span><span>{{ number_format($sale->total_amount ?? 0, 0) }} ريال</span></div>
    @if($sale->discount_amount > 0)
    <div class="row"><span>الخصم</span><span>({{ number_format($sale->discount_amount, 0) }}) ريال</span></div>
    @endif
    <div class="row grand"><span>المبلغ النهائي</span><span>{{ number_format($sale->final_amount ?? 0, 0) }} ريال</span></div>
    <div class="row"><span>المدفوع</span><span>{{ number_format($sale->paid_amount ?? 0, 0) }} ريال</span></div>
    <div class="row"><span>المتبقي</span><span style="color:{{ ($sale->final_amount - $sale->paid_amount) > 0 ? '#C62828' : '#2E7D32' }}">{{ number_format(($sale->final_amount ?? 0) - ($sale->paid_amount ?? 0), 0) }} ريال</span></div>
</div>

@if($sale->notes)
<div class="notes"><strong>ملاحظات:</strong> {{ $sale->notes }}</div>
@endif

<div class="footer">تم الإعداد بواسطة نظام Qat ERP - {{ date('Y/m/d H:i') }}</div>
</body>
</html>
