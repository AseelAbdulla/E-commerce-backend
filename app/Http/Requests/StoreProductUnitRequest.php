<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\Concerns\HasBilingualMessages;

class StoreProductUnitRequest extends FormRequest
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
            'unit_name_ar' => ['required', 'string', 'max:255'],
            'unit_name_en' => ['required', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0'],
            'has_discount' => ['nullable', 'boolean'],
            'discount_price' => ['nullable', 'numeric', 'min:0', 'lt:price'],
            'stock' => ['nullable', 'integer', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return $this->bilingual([
            'product_id.required' => ['ar' => 'المنتج مطلوب.', 'en' => 'Product is required.'],
            'product_id.exists' => ['ar' => 'المنتج غير موجود.', 'en' => 'Product does not exist.'],

            'unit_name_ar.required' => ['ar' => 'اسم الوحدة بالعربي مطلوب.', 'en' => 'Arabic unit name is required.'],
            'unit_name_en.required' => ['ar' => 'اسم الوحدة بالإنجليزي مطلوب.', 'en' => 'English unit name is required.'],

            'price.required' => ['ar' => 'السعر مطلوب.', 'en' => 'Price is required.'],
            'price.numeric' => ['ar' => 'السعر يجب أن يكون رقماً.', 'en' => 'Price must be a number.'],
            'price.min' => ['ar' => 'السعر يجب ألا يقل عن صفر.', 'en' => 'Price must be at least 0.'],

            'discount_price.numeric' => ['ar' => 'سعر الخصم يجب أن يكون رقماً.', 'en' => 'Discount price must be a number.'],
            'discount_price.min' => ['ar' => 'سعر الخصم يجب ألا يقل عن صفر.', 'en' => 'Discount price must be at least 0.'],
            'discount_price.lt' => ['ar' => 'سعر الخصم يجب أن يكون أقل من السعر الأساسي.', 'en' => 'Discount price must be less than the base price.'],

            'stock.integer' => ['ar' => 'الكمية يجب أن تكون رقماً صحيحاً.', 'en' => 'Stock must be an integer.'],
            'stock.min' => ['ar' => 'الكمية يجب ألا تقل عن صفر.', 'en' => 'Stock must be at least 0.'],
        ]);
    }
}
