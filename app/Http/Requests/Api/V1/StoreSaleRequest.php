<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class StoreSaleRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'items'                    => 'required|array|min:1',
            'items.*.product_id'       => 'nullable|integer|exists:products,id',
            'items.*.description'      => 'required|string|max:255',
            'items.*.quality'          => 'nullable|string|max:50',
            'items.*.quantity'         => 'required|numeric|min:0.01',
            'items.*.unit'             => 'nullable|string|max:20',
            'items.*.unit_price'       => 'required|numeric|min:0',
            'items.*.notes'            => 'nullable|string',

            'payment_method'  => 'required|in:cash,credit,transfer',
            'wallet_type'     => 'nullable|string|max:50',
            'discount'        => 'nullable|numeric|min:0',
            'paid_amount'     => 'nullable|numeric|min:0',
            'sale_date'       => 'nullable|date',
            'agent_id'        => 'nullable|integer|exists:agents,id',
            'customer_id'     => 'nullable|integer|exists:customers,id|required_if:payment_method,credit',
            'notes'           => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'items.required'                => 'يرجى إضافة عنصر واحد على الأقل',
            'items.*.description.required'  => 'وصف العنصر مطلوب',
            'items.*.quantity.required'     => 'الكمية مطلوبة',
            'items.*.quantity.min'          => 'الكمية يجب أن تكون أكبر من صفر',
            'items.*.unit_price.required'   => 'السعر مطلوب',
            'payment_method.required'       => 'طريقة الدفع مطلوبة',
            'payment_method.in'             => 'طريقة الدفع غير صحيحة',
            'customer_id.required_if'       => 'يجب اختيار عميل للبيع الآجل',
        ];
    }
}
