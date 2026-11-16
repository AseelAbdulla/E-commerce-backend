<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\Concerns\HasBilingualMessages;

class StoreSettingRequest extends FormRequest
{
    use HasBilingualMessages;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'key' => 'required|string|max:100|unique:settings,key',
            'value' => 'nullable|array',
        ];
    }

    public function messages(): array
    {
        return $this->bilingual([
            'key.required' => ['ar' => 'المفتاح مطلوب.', 'en' => 'Key is required.'],
            'key.max' => ['ar' => 'المفتاح يجب ألا يتجاوز 100 حرف.', 'en' => 'Key may not exceed 100 characters.'],
            'key.unique' => ['ar' => 'هذا المفتاح موجود بالفعل.', 'en' => 'This key already exists.'],

            'value.array' => ['ar' => 'القيمة يجب أن تكون بصيغة صحيحة.', 'en' => 'Value must be a valid format.'],
        ]);
    }
}
