<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Http\Requests\Concerns\HasBilingualMessages;

class StoreUserRequest extends FormRequest
{
    use HasBilingualMessages;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['required', 'string', 'max:20', 'unique:users,phone'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],

            'role' => [
                'required',
                'string',
                Rule::exists('roles', 'name')->where('guard_name', 'sanctum'),
            ],

            'status' => ['nullable', 'in:active,inactive'],
        ];
    }

    public function messages(): array
    {
        return $this->bilingual([
            'name.required' => ['ar' => 'يرجى إدخال اسم المستخدم.', 'en' => 'Please enter the user name.'],

            'email.required' => ['ar' => 'يرجى إدخال البريد الإلكتروني.', 'en' => 'Please enter the email.'],
            'email.email' => ['ar' => 'صيغة البريد الإلكتروني غير صحيحة.', 'en' => 'Email format is invalid.'],
            'email.unique' => ['ar' => 'البريد الإلكتروني مستخدم بالفعل.', 'en' => 'Email is already in use.'],

            'phone.required' => ['ar' => 'يرجى إدخال رقم الجوال.', 'en' => 'Please enter the phone number.'],
            'phone.unique' => ['ar' => 'رقم الجوال مستخدم بالفعل.', 'en' => 'Phone number is already in use.'],

            'password.required' => ['ar' => 'يرجى إدخال كلمة المرور.', 'en' => 'Please enter the password.'],
            'password.min' => ['ar' => 'كلمة المرور يجب أن تكون 8 أحرف على الأقل.', 'en' => 'Password must be at least 8 characters.'],
            'password.confirmed' => ['ar' => 'كلمتا المرور غير متطابقتين.', 'en' => 'Passwords do not match.'],

            'role.required' => ['ar' => 'يرجى اختيار دور المستخدم.', 'en' => 'Please select a user role.'],
            'role.exists' => ['ar' => 'الدور المحدد غير موجود.', 'en' => 'The selected role does not exist.'],

            'status.in' => ['ar' => 'حالة الحساب غير صحيحة.', 'en' => 'Account status is invalid.'],
        ]);
    }
}
