<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\Concerns\HasBilingualMessages;

class UpdateCategoryRequest extends FormRequest
{
    use HasBilingualMessages;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name_ar' => ['required', 'string', 'max:255'],
            'name_en' => ['required', 'string', 'max:255'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return $this->bilingual([
            'name_ar.required' => ['ar' => 'الاسم العربي مطلوب.', 'en' => 'Arabic name is required.'],
            'name_en.required' => ['ar' => 'الاسم الإنجليزي مطلوب.', 'en' => 'English name is required.'],
            'image.image' => ['ar' => 'الملف يجب أن يكون صورة.', 'en' => 'The file must be an image.'],
            'image.mimes' => ['ar' => 'صيغة الصورة غير مدعومة.', 'en' => 'Unsupported image format.'],
            'image.max' => ['ar' => 'حجم الصورة يجب ألا يتجاوز 2 ميجابايت.', 'en' => 'Image size must not exceed 2MB.'],
        ]);
    }
}
