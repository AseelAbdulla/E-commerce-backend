<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\Concerns\HasBilingualMessages;

class UpdateSettingRequest extends FormRequest
{
    use HasBilingualMessages;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'key' => 'sometimes|string|max:100|unique:settings,key,' . $this->setting?->id,
            'value' => 'nullable|array',
        ];
    }

    public function messages(): array
    {
        return $this->bilingual([
            'key.max' => ['ar' => 'المفتاح يجب ألا يتجاوز 100 حرف.', 'en' => 'Key may not exceed 100 characters.'],
            'key.unique' => ['ar' => 'هذا المفتاح موجود بالفعل.', 'en' => 'This key already exists.'],
            'value.array' => ['ar' => 'القيمة يجب أن تكون بصيغة صحيحة.', 'en' => 'Value must be a valid format.'],
        ]);
    }
}
