<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\Concerns\HasBilingualMessages;

class UpdateBannerRequest extends FormRequest
{
    use HasBilingualMessages;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'image' => 'sometimes|nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'slogan' => 'sometimes|nullable|array',
            'slogan.ar' => 'sometimes|nullable|string',
            'slogan.en' => 'sometimes|nullable|string',
            'sort_order' => 'sometimes|nullable|integer',
            'status' => 'sometimes|in:active,inactive',
        ];
    }

    public function messages(): array
    {
        return $this->bilingual([
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
