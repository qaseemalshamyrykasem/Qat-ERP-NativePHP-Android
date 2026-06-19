<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head><meta charset="UTF-8"><title>سند قبض</title>
<style>*{margin:0;padding:0;box-sizing:border-box}body{font-family:'Cairo',sans-serif;padding:20mm 25mm;color:#333;font-size:11pt;direction:rtl}.header{display:flex;justify-content:space-between;align-items:center;border-bottom:3px solid #1B5E20;padding-bottom:15pt;margin-bottom:20pt}.store-name{font-size:16pt;font-weight:bold;color:#1B5E20}.doc-title{text-align:center;font-size:14pt;font-weight:bold;color:#1B5E20;margin:10pt 0;padding:8pt;background:#E8F5E9;border-radius:8pt}.info-box{background:#f9f9f9;padding:15pt;border-radius:8pt;margin:15pt 0;border:1px solid #eee}.info-row{display:flex;justify-content:space-between;padding:5pt 0;border-bottom:1px solid #f0f0f0;font-size:12pt}.info-row:last-child{border:none}.info-label{color:#666}.info-value{font-weight:bold}.amount-box{text-align:center;padding:20pt;margin:15pt 0;background:#E8F5E9;border:2px solid #1B5E20;border-radius:12pt}.amount-label{font-size:10pt;color:#666}.amount-value{font-size:24pt;font-weight:bold;color:#1B5E20}.signatures{display:flex;justify-content:space-between;margin-top:40pt}.sig-box{text-align:center;width:40%}.sig-line{border-top:1px solid #333;margin-top:40pt;padding-top:5pt;font-size:10pt}.footer{text-align:center;margin-top:30pt;font-size:9pt;color:#999;border-top:1px solid #eee;padding-top:10pt}</style></head>
<body>
<div class="header"><div><div class="store-name">{{ config('app.name', 'المتجر') }}</div></div><div style="text-align:left"><div style="font-weight:bold">سند قبض</div><div style="color:#666;font-size:10pt">رقم: {{ $voucher->voucher_no ?? '---' }}</div><div style="color:#666;font-size:10pt">التاريخ: {{ ($voucher->voucher_date ?? now()->format('Y-m-d')) }}</div></div></div>
<div class="doc-title">سند قبض</div>
<div class="info-box">
<div class="info-row"><span class="info-label">الحساب</span><span class="info-value">{{ $voucher->account->name ?? '---' }}</span></div>
@if($voucher->customer)<div class="info-row"><span class="info-label">العميل</span><span class="info-value">{{ $voucher->customer->name }}</span></div>@endif
<div class="info-row"><span class="info-label">طريقة الدفع</span><span class="info-value">{{ $voucher->payment_method ?? '---' }}</span></div>
<div class="info-row"><span class="info-label">الوصف</span><span class="info-value">{{ $voucher->description ?? '---' }}</span></div>
</div>
<div class="amount-box"><div class="amount-label">المبلغ المستلم</div><div class="amount-value">{{ number_format($voucher->amount, 0) }} ريال</div></div>
<div class="signatures"><div class="sig-box"><div class="sig-line">المستلم</div></div><div class="sig-box"><div class="sig-line">المحاسب</div></div></div>
<div class="footer">تم الإعداد بواسطة نظام Qat ERP - {{ date('Y/m/d H:i') }}</div>
</body></html>
