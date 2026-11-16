<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\Concerns\HasBilingualMessages;

class StoreProductRequest extends FormRequest
{
    use HasBilingualMessages;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_id' => ['required', 'exists:categories,id'],
            'name_ar' => ['required', 'string', 'max:255'],
            'name_en' => ['required', 'string', 'max:255'],
            'description_ar' => ['nullable', 'string'],
            'description_en' => ['nullable', 'string'],
            'sku' => ['required', 'string', 'unique:products,sku'],
            'base_price' => ['required', 'numeric', 'min:0'],
            'discount_price' => ['nullable', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'low_stock_threshold' => ['nullable', 'integer', 'min:0'],
            'status' => ['nullable', 'in:active,inactive'],
            'has_discount' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return $this->bilingual([
            'category_id.required' => ['ar' => 'التصنيف مطلوب.', 'en' => 'Category is required.'],
            'category_id.exists' => ['ar' => 'التصنيف غير موجود.', 'en' => 'Category does not exist.'],

            'name_ar.required' => ['ar' => 'الاسم العربي مطلوب.', 'en' => 'Arabic name is required.'],
            'name_en.required' => ['ar' => 'الاسم الإنجليزي مطلوب.', 'en' => 'English name is required.'],

            'sku.required' => ['ar' => 'رمز المنتج (SKU) مطلوب.', 'en' => 'SKU is required.'],
            'sku.unique' => ['ar' => 'رمز المنتج مستخدم بالفعل.', 'en' => 'SKU is already in use.'],

            'base_price.required' => ['ar' => 'السعر الأساسي مطلوب.', 'en' => 'Base price is required.'],
            'base_price.numeric' => ['ar' => 'السعر يجب أن يكون رقماً.', 'en' => 'Price must be a number.'],
            'base_price.min' => ['ar' => 'السعر يجب ألا يقل عن صفر.', 'en' => 'Price must be at least 0.'],

            'discount_price.numeric' => ['ar' => 'سعر الخصم يجب أن يكون رقماً.', 'en' => 'Discount price must be a number.'],
            'discount_price.min' => ['ar' => 'سعر الخصم يجب ألا يقل عن صفر.', 'en' => 'Discount price must be at least 0.'],

            'stock.required' => ['ar' => 'الكمية بالمخزون مطلوبة.', 'en' => 'Stock quantity is required.'],
            'stock.integer' => ['ar' => 'الكمية يجب أن تكون رقماً صحيحاً.', 'en' => 'Stock must be an integer.'],
            'stock.min' => ['ar' => 'الكمية يجب ألا تقل عن صفر.', 'en' => 'Stock must be at least 0.'],

            'low_stock_threshold.integer' => ['ar' => 'حد التنبيه للمخزون يجب أن يكون رقماً صحيحاً.', 'en' => 'Low stock threshold must be an integer.'],

            'status.in' => ['ar' => 'الحالة يجب أن تكون active أو inactive.', 'en' => 'Status must be active or inactive.'],

            'has_discount.boolean' => ['ar' => 'قيمة الخصم غير صحيحة.', 'en' => 'Discount flag value is invalid.'],
        ]);
    }
}
