<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\Concerns\HasBilingualMessages;

class ReviewImportRequest extends FormRequest
{
    use HasBilingualMessages;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'action'               => 'required|in:approve,reject',
            'rejection_reason'     => 'required_if:action,reject|nullable|string',
            'shipping_method'      => 'required_if:action,approve|nullable|string',
            'offered_shipping_fee' => 'required_if:action,approve|nullable|numeric|min:0',
            'offer_expires_at'     => 'required_if:action,approve|nullable|date|after:now',
            'items'                => 'required_if:action,approve|array',
            'items.*.id'           => 'required_with:items|exists:import_request_items,id',
            'items.*.offered_unit_price' => 'required_with:items|numeric|min:0',
        ];
    }

    public function messages(): array
    {
        return $this->bilingual([
            'action.required' => ['ar' => 'الإجراء مطلوب.', 'en' => 'Action is required.'],
            'action.in' => ['ar' => 'الإجراء يجب أن يكون موافقة أو رفض.', 'en' => 'Action must be approve or reject.'],

            'rejection_reason.required_if' => ['ar' => 'سبب الرفض مطلوب.', 'en' => 'Rejection reason is required.'],

            'shipping_method.required_if' => ['ar' => 'طريقة الشحن مطلوبة.', 'en' => 'Shipping method is required.'],

            'offered_shipping_fee.required_if' => ['ar' => 'رسوم الشحن المعروضة مطلوبة.', 'en' => 'Offered shipping fee is required.'],
            'offered_shipping_fee.numeric' => ['ar' => 'رسوم الشحن يجب أن تكون رقماً.', 'en' => 'Offered shipping fee must be a number.'],
            'offered_shipping_fee.min' => ['ar' => 'رسوم الشحن يجب ألا تقل عن صفر.', 'en' => 'Offered shipping fee must be at least 0.'],

            'offer_expires_at.required_if' => ['ar' => 'تاريخ انتهاء العرض مطلوب.', 'en' => 'Offer expiry date is required.'],
            'offer_expires_at.date' => ['ar' => 'تاريخ انتهاء العرض غير صحيح.', 'en' => 'Offer expiry date is invalid.'],
            'offer_expires_at.after' => ['ar' => 'تاريخ انتهاء العرض يجب أن يكون بعد الآن.', 'en' => 'Offer expiry date must be after now.'],

            'items.required_if' => ['ar' => 'العناصر مطلوبة.', 'en' => 'Items are required.'],
            'items.array' => ['ar' => 'العناصر يجب أن تكون قائمة.', 'en' => 'Items must be an array.'],

            'items.*.id.required_with' => ['ar' => 'معرف العنصر مطلوب.', 'en' => 'Item id is required.'],
            'items.*.id.exists' => ['ar' => 'أحد العناصر المحددة غير موجود.', 'en' => 'One of the selected items does not exist.'],

            'items.*.offered_unit_price.required_with' => ['ar' => 'سعر الوحدة المعروض مطلوب.', 'en' => 'Offered unit price is required.'],
            'items.*.offered_unit_price.numeric' => ['ar' => 'سعر الوحدة يجب أن يكون رقماً.', 'en' => 'Offered unit price must be a number.'],
            'items.*.offered_unit_price.min' => ['ar' => 'سعر الوحدة يجب ألا يقل عن صفر.', 'en' => 'Offered unit price must be at least 0.'],
        ]);
    }
}
