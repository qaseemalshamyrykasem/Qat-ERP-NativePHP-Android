<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Models\ReceiptVoucher;
use App\Models\PaymentVoucher;
use Barryvdh\DomPDF\Facade\Pdf;

class PrintController extends Controller
{
    public function saleInvoice(Sale $sale)
    {
        $sale->load(['items.product', 'customer']);
        return view('prints.sale-invoice', compact('sale'));
    }

    public function saleInvoicePdf(Sale $sale)
    {
        $sale->load(['items.product', 'customer']);
        $pdf = Pdf::loadView('prints.sale-invoice', compact('sale'));
        return $pdf->download("فاتورة-{$sale->invoice_no}.pdf");
    }

    public function receiptVoucher(ReceiptVoucher $voucher)
    {
        $voucher->load(['customer', 'account']);
        return view('prints.receipt-voucher', compact('voucher'));
    }

    public function receiptVoucherPdf(ReceiptVoucher $voucher)
    {
        $voucher->load(['customer', 'account']);
        $pdf = Pdf::loadView('prints.receipt-voucher', compact('voucher'));
        return $pdf->download("سند-قبض-{$voucher->voucher_no}.pdf");
    }

    public function paymentVoucher(PaymentVoucher $voucher)
    {
        $voucher->load(['supplier', 'account']);
        return view('prints.payment-voucher', compact('voucher'));
    }

    public function paymentVoucherPdf(PaymentVoucher $voucher)
    {
        $voucher->load(['supplier', 'account']);
        $pdf = Pdf::loadView('prints.payment-voucher', compact('voucher'));
        return $pdf->download("سند-صرف-{$voucher->voucher_no}.pdf");
    }
}
