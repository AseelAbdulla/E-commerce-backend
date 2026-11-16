<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\Concerns\HasBilingualMessages;

class UpdateReviewRequest extends FormRequest
{
    use HasBilingualMessages;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['sometimes', 'required', 'in:pending,approved,rejected'],
            'admin_note' => ['sometimes', 'nullable', 'string', 'max:2000'],
            'visitor_name' => ['sometimes', 'string', 'max:255'],
            'visitor_email' => ['sometimes', 'email', 'max:255'],
            'rating' => ['sometimes', 'integer', 'min:1', 'max:5'],
            'comment' => ['sometimes', 'string', 'min:3', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return $this->bilingual([
            'status.required' => ['ar' => 'الحالة مطلوبة.', 'en' => 'Status is required.'],
            'status.in' => ['ar' => 'الحالة يجب أن تكون قيد الانتظار أو موافق عليها أو مرفوضة.', 'en' => 'Status must be pending, approved, or rejected.'],

            'admin_note.max' => ['ar' => 'ملاحظة المشرف طويلة جداً.', 'en' => 'Admin note is too long.'],

            'visitor_name.max' => ['ar' => 'الاسم طويل جداً.', 'en' => 'Name is too long.'],

            'visitor_email.email' => ['ar' => 'البريد الإلكتروني غير صحيح.', 'en' => 'Email is invalid.'],
            'visitor_email.max' => ['ar' => 'البريد الإلكتروني طويل جداً.', 'en' => 'Email is too long.'],

            'rating.integer' => ['ar' => 'التقييم غير صحيح.', 'en' => 'Rating is invalid.'],
            'rating.min' => ['ar' => 'يجب اختيار نجمة واحدة على الأقل.', 'en' => 'At least one star is required.'],
            'rating.max' => ['ar' => 'لا يمكن أن يزيد التقييم عن 5 نجوم.', 'en' => 'Rating cannot exceed 5 stars.'],

            'comment.min' => ['ar' => 'التعليق قصير جداً.', 'en' => 'Comment is too short.'],
            'comment.max' => ['ar' => 'التعليق طويل جداً.', 'en' => 'Comment is too long.'],
        ]);
    }
}
