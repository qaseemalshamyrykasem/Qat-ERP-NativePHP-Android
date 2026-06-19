<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\MessageTemplate;
use App\Models\Customer;
use App\Models\Supplier;
use App\Models\Agent;
use App\Models\Debt;
use App\Models\Sale;
use App\Models\Reminder;
use App\Services\WhatsAppService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WhatsAppController extends Controller
{
    public function __construct(private WhatsAppService $whatsappService) {}

    /**
     * Generate wa.me link
     */
    public function generateLink(Request $request): JsonResponse
    {
        $request->validate([
            'phone' => 'required|string',
            'message' => 'nullable|string|max:5000',
        ]);

        $link = $this->whatsappService->generateLink(
            $request->phone,
            $request->message ?? ''
        );

        return response()->json(['success' => true, 'link' => $link]);
    }

    /**
     * Get message templates
     */
    public function templates(): JsonResponse
    {
        $templates = MessageTemplate::where('status', true)->get();
        return response()->json(['success' => true, 'data' => $templates]);
    }

    /**
     * Preview message with template + entity
     */
    public function preview(Request $request): JsonResponse
    {
        $request->validate([
            'template' => 'required|string',
            'entity_type' => 'required|string',
            'entity_id' => 'required|integer',
        ]);

        $template = MessageTemplate::where('name', $request->template)->first();
        if (!$template) {
            return response()->json(['success' => false, 'message' => 'القالب غير موجود'], 404);
        }

        $variables = $this->getEntityVariables($request->entity_type, $request->entity_id);
        $message = $this->whatsappService->applyTemplate($template, $variables);

        return response()->json(['success' => true, 'message' => $message]);
    }

    /**
     * Send debt reminder
     */
    public function debtReminder(Debt $debt): JsonResponse
    {
        $message = $this->whatsappService->generateDebtReminder($debt);
        $phone = $debt->customer?->phone;

        if (!$phone) {
            return response()->json(['success' => false, 'message' => 'لا يوجد رقم هاتف للعميل'], 400);
        }

        $link = $this->whatsappService->generateLink($phone, $message);
        return response()->json(['success' => true, 'link' => $link, 'message' => $message]);
    }

    /**
     * Send sale invoice
     */
    public function saleInvoice(Sale $sale): JsonResponse
    {
        $message = $this->whatsappService->generateSaleMessage($sale);
        $phone = $sale->customer?->phone;

        if (!$phone) {
            return response()->json(['success' => false, 'message' => 'لا يوجد رقم هاتف للعميل'], 400);
        }

        $link = $this->whatsappService->generateLink($phone, $message);
        return response()->json(['success' => true, 'link' => $link, 'message' => $message]);
    }

    private function getEntityVariables(string $type, int $id): array
    {
        switch ($type) {
            case 'customer':
                $c = Customer::find($id);
                return $c ? ['اسم_العميل' => $c->name, 'هاتف' => $c->phone, 'رصيد' => number_format($c->remaining, 0)] : [];
            case 'supplier':
                $s = Supplier::find($id);
                return $s ? ['اسم_المورد' => $s->name, 'هاتف' => $s->phone, 'رصيد' => number_format($s->total_remaining, 0)] : [];
            case 'agent':
                $a = Agent::find($id);
                return $a ? ['اسم_الوكيل' => $a->name, 'هاتف' => $a->phone, 'المنطقة' => $a->area] : [];
            case 'debt':
                $d = Debt::find($id);
                return $d ? [
                    'اسم_العميل' => $d->customer?->name ?? '',
                    'المبلغ' => number_format($d->remaining_amount, 0),
                    'التاريخ' => $d->due_date ? date('Y/m/d', strtotime($d->due_date)) : '',
                ] : [];
            default:
                return [];
        }
    }
}