<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\Concerns\HasBilingualMessages;

class StoreImportRequest extends FormRequest
{
    use HasBilingualMessages;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'currency'          => 'required|string|max:10',
            'phone'             => 'required|string|max:20',
            'address_details'   => 'required|string|max:500',
            'notes'             => 'nullable|string',
            'items'             => 'required|array|min:1',
            'items.*.product_id'=> 'required|exists:products,id',
            'items.*.unit_id'   => 'required|exists:product_units,id',
            'items.*.quantity'  => 'required|integer|min:1',
        ];
    }

    public function messages(): array
    {
        return $this->bilingual([
            'currency.required' => ['ar' => 'العملة مطلوبة.', 'en' => 'Currency is required.'],

            'phone.required' => ['ar' => 'رقم الهاتف مطلوب.', 'en' => 'Phone number is required.'],

            'address_details.required' => ['ar' => 'تفاصيل العنوان مطلوبة.', 'en' => 'Address details are required.'],
            'address_details.max' => ['ar' => 'تفاصيل العنوان طويلة جداً.', 'en' => 'Address details are too long.'],

            'items.required' => ['ar' => 'العناصر مطلوبة.', 'en' => 'Items are required.'],
            'items.array' => ['ar' => 'العناصر يجب أن تكون قائمة.', 'en' => 'Items must be an array.'],
            'items.min' => ['ar' => 'يجب إضافة عنصر واحد على الأقل.', 'en' => 'At least one item is required.'],

            'items.*.product_id.required' => ['ar' => 'المنتج مطلوب.', 'en' => 'Product is required.'],
            'items.*.product_id.exists' => ['ar' => 'أحد المنتجات المحددة غير موجود.', 'en' => 'One of the selected products does not exist.'],

            'items.*.unit_id.required' => ['ar' => 'الوحدة مطلوبة.', 'en' => 'Unit is required.'],
            'items.*.unit_id.exists' => ['ar' => 'إحدى الوحدات المحددة غير موجودة.', 'en' => 'One of the selected units does not exist.'],

            'items.*.quantity.required' => ['ar' => 'الكمية مطلوبة.', 'en' => 'Quantity is required.'],
            'items.*.quantity.integer' => ['ar' => 'الكمية يجب أن تكون رقماً صحيحاً.', 'en' => 'Quantity must be an integer.'],
            'items.*.quantity.min' => ['ar' => 'الكمية يجب أن تكون أكبر من صفر.', 'en' => 'Quantity must be greater than 0.'],
        ]);
    }
}
