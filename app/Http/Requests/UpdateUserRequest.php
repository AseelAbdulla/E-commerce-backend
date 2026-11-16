<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Http\Requests\Concerns\HasBilingualMessages;

class UpdateUserRequest extends FormRequest
{
    use HasBilingualMessages;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('user');

        return [
            'name' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')->ignore($id)],
            'phone' => ['nullable', 'string', 'max:20', Rule::unique('users', 'phone')->ignore($id)],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'status' => ['nullable', 'in:active,inactive'],

            'role' => [
                'nullable',
                'string',
                Rule::exists('roles', 'name')->where('guard_name', 'sanctum'),
            ],
        ];
    }

    public function messages(): array
    {
        return $this->bilingual([
            'email.email' => ['ar' => 'صيغة البريد الإلكتروني غير صحيحة.', 'en' => 'Email format is invalid.'],
            'email.unique' => ['ar' => 'البريد الإلكتروني مستخدم بالفعل.', 'en' => 'Email is already in use.'],

            'phone.unique' => ['ar' => 'رقم الجوال مستخدم بالفعل.', 'en' => 'Phone number is already in use.'],

            'password.min' => ['ar' => 'كلمة المرور يجب أن تكون 8 أحرف على الأقل.', 'en' => 'Password must be at least 8 characters.'],
            'password.confirmed' => ['ar' => 'كلمتا المرور غير متطابقتين.', 'en' => 'Passwords do not match.'],

            'role.exists' => ['ar' => 'الدور المحدد غير موجود.', 'en' => 'The selected role does not exist.'],

            'status.in' => ['ar' => 'حالة الحساب غير صحيحة.', 'en' => 'Account status is invalid.'],
        ]);
    }
}
