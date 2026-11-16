<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\Concerns\HasBilingualMessages;

class UpdateFeatureRequest extends FormRequest
{
    use HasBilingualMessages;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'icon' => 'sometimes|string|max:100',
            'title' => 'sometimes|array',
            'title.ar' => 'sometimes|string',
            'title.en' => 'sometimes|string',
            'description' => 'sometimes|array',
            'description.ar' => 'sometimes|string',
            'description.en' => 'sometimes|string',
            'sort_order' => 'nullable|integer',
            'status' => 'nullable|in:active,inactive',
        ];
    }

    public function messages(): array
    {
        return $this->bilingual([
            'icon.max' => ['ar' => 'اسم الأيقونة طويل جداً.', 'en' => 'Icon name is too long.'],

            'title.array' => ['ar' => 'العنوان يجب أن يكون بصيغة صحيحة.', 'en' => 'Title must be a valid format.'],
            'title.ar.string' => ['ar' => 'العنوان العربي يجب أن يكون نصاً.', 'en' => 'Arabic title must be a string.'],
            'title.en.string' => ['ar' => 'العنوان الإنجليزي يجب أن يكون نصاً.', 'en' => 'English title must be a string.'],

            'description.array' => ['ar' => 'الوصف يجب أن يكون بصيغة صحيحة.', 'en' => 'Description must be a valid format.'],
            'description.ar.string' => ['ar' => 'الوصف العربي يجب أن يكون نصاً.', 'en' => 'Arabic description must be a string.'],
            'description.en.string' => ['ar' => 'الوصف الإنجليزي يجب أن يكون نصاً.', 'en' => 'English description must be a string.'],

            'sort_order.integer' => ['ar' => 'ترتيب العرض يجب أن يكون رقماً صحيحاً.', 'en' => 'Sort order must be an integer.'],
            'status.in' => ['ar' => 'الحالة يجب أن تكون active أو inactive.', 'en' => 'Status must be active or inactive.'],
        ]);
    }
}
