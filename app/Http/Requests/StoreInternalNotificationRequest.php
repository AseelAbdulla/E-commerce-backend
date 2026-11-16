<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\Concerns\HasBilingualMessages;

class StoreInternalNotificationRequest extends FormRequest
{
    use HasBilingualMessages;

    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->roles->contains('name', 'admin');
    }

    public function rules(): array
    {
        return [
            'user_id' => ['required', 'exists:users,id'],
            'title' => ['required'],
            'message' => ['required'],
            'type' => ['required', 'string', 'max:100'],
            'sent_at' => ['nullable', 'date'],
        ];
    }

    public function messages(): array
    {
        return $this->bilingual([
            'user_id.required' => ['ar' => 'المستخدم مطلوب.', 'en' => 'User is required.'],
            'user_id.exists' => ['ar' => 'المستخدم المحدد غير موجود.', 'en' => 'The selected user does not exist.'],

            'title.required' => ['ar' => 'العنوان مطلوب.', 'en' => 'Title is required.'],
            'title.array' => ['ar' => 'العنوان يجب أن يكون بصيغة صحيحة.', 'en' => 'Title must be an array.'],

            'message.required' => ['ar' => 'الرسالة مطلوبة.', 'en' => 'Message is required.'],
            'message.array' => ['ar' => 'الرسالة يجب أن تكون بصيغة صحيحة.', 'en' => 'Message must be an array.'],

            'type.required' => ['ar' => 'نوع التنبيه مطلوب.', 'en' => 'Notification type is required.'],
            'type.string' => ['ar' => 'نوع التنبيه يجب أن يكون نصاً.', 'en' => 'Notification type must be a string.'],

            'sent_at.date' => ['ar' => 'تاريخ الإرسال غير صحيح.', 'en' => 'Sent date must be a valid date.'],
        ]);
    }
}
