<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\Concerns\HasBilingualMessages;

class StoreFeatureRequest extends FormRequest
{
    use HasBilingualMessages;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'icon' => 'required|string|max:100',
            'title' => 'required|array',
            'description' => 'required|array',
            'sort_order' => 'nullable|integer',
            'status' => 'nullable|in:active,inactive',
        ];
    }

    public function messages(): array
    {
        return $this->bilingual([
            'icon.required' => ['ar' => 'الأيقونة مطلوبة.', 'en' => 'Icon is required.'],
            'icon.max' => ['ar' => 'اسم الأيقونة طويل جداً.', 'en' => 'Icon name is too long.'],

            'title.required' => ['ar' => 'العنوان مطلوب.', 'en' => 'Title is required.'],
            'title.array' => ['ar' => 'العنوان يجب أن يكون بصيغة صحيحة.', 'en' => 'Title must be a valid format.'],

            'description.required' => ['ar' => 'الوصف مطلوب.', 'en' => 'Description is required.'],
            'description.array' => ['ar' => 'الوصف يجب أن يكون بصيغة صحيحة.', 'en' => 'Description must be a valid format.'],

            'sort_order.integer' => ['ar' => 'ترتيب العرض يجب أن يكون رقماً صحيحاً.', 'en' => 'Sort order must be an integer.'],
            'status.in' => ['ar' => 'الحالة يجب أن تكون active أو inactive.', 'en' => 'Status must be active or inactive.'],
        ]);
    }
}
