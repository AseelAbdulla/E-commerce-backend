<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\Concerns\HasBilingualMessages;

class StoreRoleRequest extends FormRequest
{
    use HasBilingualMessages;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100', 'unique:roles,name'],
            'display_name' => ['required', 'array'],
        ];
    }

    public function messages(): array
    {
        return $this->bilingual([
            'name.required' => ['ar' => 'اسم الدور مطلوب.', 'en' => 'Role name is required.'],
            'name.string' => ['ar' => 'اسم الدور يجب أن يكون نصاً.', 'en' => 'Role name must be a string.'],
            'name.max' => ['ar' => 'اسم الدور يجب ألا يتجاوز 100 حرف.', 'en' => 'Role name may not exceed 100 characters.'],
            'name.unique' => ['ar' => 'هذا الدور موجود بالفعل.', 'en' => 'This role already exists.'],

            'display_name.required' => ['ar' => 'الاسم المعروض مطلوب.', 'en' => 'Display name is required.'],
            'display_name.array' => ['ar' => 'الاسم المعروض يجب أن يكون بصيغة صحيحة.', 'en' => 'Display name must be an array.'],
        ]);
    }
}
