<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\Concerns\HasBilingualMessages;

class StoreCartItemRequest extends FormRequest
{
    use HasBilingualMessages;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_id' => ['required', 'exists:products,id'],
            'unit_id'    => ['required', 'exists:product_units,id'],
            'quantity'   => ['required', 'integer', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return $this->bilingual([
            'product_id.required' => ['ar' => 'يجب اختيار المنتج.', 'en' => 'Product must be selected.'],
            'product_id.exists' => ['ar' => 'المنتج غير موجود.', 'en' => 'Product does not exist.'],

            'unit_id.required' => ['ar' => 'يجب اختيار الوحدة.', 'en' => 'Unit must be selected.'],
            'unit_id.exists' => ['ar' => 'الوحدة غير موجودة.', 'en' => 'Unit does not exist.'],

            'quantity.required' => ['ar' => 'الكمية مطلوبة.', 'en' => 'Quantity is required.'],
            'quantity.integer' => ['ar' => 'الكمية يجب أن تكون رقماً صحيحاً.', 'en' => 'Quantity must be an integer.'],
            'quantity.min' => ['ar' => 'يجب أن تكون الكمية أكبر من صفر.', 'en' => 'Quantity must be greater than 0.'],
        ]);
    }
}
