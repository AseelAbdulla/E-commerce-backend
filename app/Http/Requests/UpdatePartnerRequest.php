<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\Concerns\HasBilingualMessages;

class UpdatePartnerRequest extends FormRequest
{
    use HasBilingualMessages;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'nullable|array',
            'logo' => 'sometimes|image|max:255',
            'website_url' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer',
            'status' => 'nullable|string|max:50',
            'lat' => 'nullable|numeric',
            'lng' => 'nullable|numeric',
        ];
    }

    public function messages(): array
    {
        return $this->bilingual([
            'name.array' => ['ar' => 'الاسم يجب أن يكون بصيغة صحيحة.', 'en' => 'Name must be a valid format.'],

            'logo.image' => ['ar' => 'الشعار يجب أن يكون صورة.', 'en' => 'Logo must be an image.'],
            'logo.max' => ['ar' => 'حجم الشعار كبير جداً.', 'en' => 'Logo size is too large.'],

            'website_url.max' => ['ar' => 'رابط الموقع طويل جداً.', 'en' => 'Website URL is too long.'],

            'sort_order.integer' => ['ar' => 'ترتيب العرض يجب أن يكون رقماً صحيحاً.', 'en' => 'Sort order must be an integer.'],

            'lat.numeric' => ['ar' => 'خط العرض يجب أن يكون رقماً.', 'en' => 'Latitude must be a number.'],
            'lng.numeric' => ['ar' => 'خط الطول يجب أن يكون رقماً.', 'en' => 'Longitude must be a number.'],
        ]);
    }
}
