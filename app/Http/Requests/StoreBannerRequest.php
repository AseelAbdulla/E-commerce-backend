<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\Concerns\HasBilingualMessages;

class StoreBannerRequest extends FormRequest
{
    use HasBilingualMessages;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'image' => 'required|image|mimes:jpg,jpeg,png,webp|max:5120',

            'slogan' => 'nullable|array',
            'slogan.ar' => 'nullable|string',
            'slogan.en' => 'nullable|string',

            'sort_order' => 'nullable|integer',
            'status' => 'nullable|in:active,inactive',
        ];
    }

    public function messages(): array
    {
        return $this->bilingual([
            'image.required' => ['ar' => 'صورة البنر مطلوبة.', 'en' => 'Banner image is required.'],
            'image.image' => ['ar' => 'الملف يجب أن يكون صورة.', 'en' => 'The file must be an image.'],
            'image.mimes' => ['ar' => 'صيغة الصورة يجب أن تكون jpg أو jpeg أو png أو webp.', 'en' => 'Image format must be jpg, jpeg, png, or webp.'],
            'image.max' => ['ar' => 'حجم الصورة يجب ألا يتجاوز 5 ميجابايت.', 'en' => 'Image size must not exceed 5MB.'],

            'slogan.array' => ['ar' => 'النص يجب أن يكون بصيغة صحيحة.', 'en' => 'Slogan must be a valid format.'],
            'slogan.ar.string' => ['ar' => 'النص العربي يجب أن يكون نصاً.', 'en' => 'Arabic text must be a string.'],
            'slogan.en.string' => ['ar' => 'النص الإنجليزي يجب أن يكون نصاً.', 'en' => 'English text must be a string.'],

            'sort_order.integer' => ['ar' => 'ترتيب العرض يجب أن يكون رقماً صحيحاً.', 'en' => 'Sort order must be an integer.'],

            'status.in' => ['ar' => 'حالة البنر يجب أن تكون active أو inactive.', 'en' => 'Status must be active or inactive.'],
        ]);
    }
}
