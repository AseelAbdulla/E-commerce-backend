<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\Concerns\HasBilingualMessages;

class UpdateProductImageRequest extends FormRequest
{
    use HasBilingualMessages;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_id' => ['sometimes', 'exists:products,id'],
            'image' => ['sometimes', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048'],
            'is_main' => ['sometimes', 'boolean'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return $this->bilingual([
            'product_id.exists' => ['ar' => 'المنتج غير موجود.', 'en' => 'Product does not exist.'],

            'image.image' => ['ar' => 'الملف يجب أن يكون صورة.', 'en' => 'The file must be an image.'],
            'image.mimes' => ['ar' => 'صيغة الصورة غير مدعومة.', 'en' => 'Unsupported image format.'],
            'image.max' => ['ar' => 'حجم الصورة يجب ألا يتجاوز 2 ميجابايت.', 'en' => 'Image size must not exceed 2MB.'],

            'is_main.boolean' => ['ar' => 'قيمة الصورة الرئيسية غير صحيحة.', 'en' => 'Main image value is invalid.'],

            'sort_order.integer' => ['ar' => 'ترتيب العرض يجب أن يكون رقماً صحيحاً.', 'en' => 'Sort order must be an integer.'],
            'sort_order.min' => ['ar' => 'ترتيب العرض يجب ألا يقل عن صفر.', 'en' => 'Sort order must be at least 0.'],
        ]);
    }
}
