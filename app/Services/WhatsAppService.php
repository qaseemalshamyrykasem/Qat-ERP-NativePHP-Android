<?php

namespace App\Services;

use App\Models\MessageTemplate;
use App\Models\Customer;
use App\Models\Supplier;
use App\Models\Agent;
use App\Models\Debt;
use App\Models\Sale;

class WhatsAppService
{
    /**
     * Generate a wa.me deep link
     */
    public function generateLink(string $phone, string $message = ''): string
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);

        // If 9 digits, prepend Yemen country code 967
        if (strlen($phone) === 9 && !str_starts_with($phone, '0')) {
            $phone = '967' . $phone;
        }
        // Remove leading +
        $phone = ltrim($phone, '+');

        $url = 'https://wa.me/' . $phone;
        if ($message) {
            $url .= '?text=' . urlencode($message);
        }
        return $url;
    }

    /**
     * Apply template variables
     */
    public function applyTemplate(MessageTemplate $template, array $variables): string
    {
        $message = $template->content;
        foreach ($variables as $key => $value) {
            $message = str_replace('{' . $key . '}', $value, $message);
        }
        return $message;
    }

    /**
     * Generate debt reminder message
     */
    public function generateDebtReminder(Debt $debt): string
    {
        $customer = $debt->customer;
        $storeName = config('app.name', 'المتجر');
        $amount = number_format($debt->remaining_amount, 0);
        $dueDate = $debt->due_date ? date('Y/m/d', strtotime($debt->due_date)) : '';

        return "عزيزي {$customer->name}،\n\n" .
               "تذكير بأن لديك دين مستحق بقيمة {$amount} ريال" .
               ($dueDate ? "، مستحق بتاريخ {$dueDate}" : "") . "\n\n" .
               "- {$storeName}";
    }

    /**
     * Generate sale invoice message
     */
    public function generateSaleMessage(Sale $sale): string
    {
        $customer = $sale->customer;
        $storeName = config('app.name', 'المتجر');
        $amount = number_format($sale->final_amount, 0);
        $paid = number_format($sale->paid_amount, 0);
        $remaining = number_format($sale->final_amount - $sale->paid_amount, 0);

        $msg = "فاتورة بيع رقم {$sale->invoice_no}\n";
        $msg .= "التاريخ: {$sale->sale_date}\n";
        if ($customer) $msg .= "العميل: {$customer->name}\n";
        $msg .= "\nالمبلغ: {$amount} ريال\n";
        $msg .= "المدفوع: {$paid} ريال\n";
        $msg .= "المتبقي: {$remaining} ريال\n";
        $msg .= "\n- {$storeName}";

        return $msg;
    }

    /**
     * Generate payment confirmation message
     */
    public function generatePaymentConfirmation(string $customerName, float $amount, float $remaining = 0): string
    {
        $storeName = config('app.name', 'المتجر');
        $msg = "عزيزي {$customerName}،\n\n";
        $msg .= "تم استلام دفعة بقيمة " . number_format($amount, 0) . " ريال";
        if ($remaining > 0) {
            $msg .= "\nالمتبقي: " . number_format($remaining, 0) . " ريال";
        }
        $msg .= "\n\nشكراً لتعاونكم.\n- {$storeName}";
        return $msg;
    }

    /**
     * Get store phone from settings or current user
     */
    public function getStorePhone(): ?string
    {
        // Try to get from settings
        $setting = \App\Models\Setting::where('setting_key', 'store_phone')->first();
        if ($setting) return $setting->setting_value;

        // Fall back to current user phone
        return auth()->user()->phone ?? null;
    }
}