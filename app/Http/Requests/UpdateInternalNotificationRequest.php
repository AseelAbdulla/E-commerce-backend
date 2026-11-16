<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\Concerns\HasBilingualMessages;

class UpdateInternalNotificationRequest extends FormRequest
{
    use HasBilingualMessages;

    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->roles->contains('name', 'admin');
    }

    public function rules(): array
    {
        return [
            'title' => ['nullable', 'array'],
            'message' => ['nullable', 'array'],
            'type' => ['nullable', 'string', 'max:100'],
            'is_read' => ['nullable', 'boolean'],
            'sent_at' => ['nullable', 'date'],
        ];
    }

    public function messages(): array
    {
        return $this->bilingual([
            'title.array' => ['ar' => 'العنوان يجب أن يكون بصيغة صحيحة.', 'en' => 'Title must be an array.'],
            'message.array' => ['ar' => 'الرسالة يجب أن تكون بصيغة صحيحة.', 'en' => 'Message must be an array.'],
            'type.string' => ['ar' => 'نوع التنبيه يجب أن يكون نصاً.', 'en' => 'Notification type must be a string.'],
            'is_read.boolean' => ['ar' => 'حالة القراءة يجب أن تكون صحيحة أو خاطئة.', 'en' => 'Read status must be true or false.'],
            'sent_at.date' => ['ar' => 'تاريخ الإرسال غير صحيح.', 'en' => 'Sent date must be a valid date.'],
        ]);
    }
}
