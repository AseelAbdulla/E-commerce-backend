<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\Concerns\HasBilingualMessages;

class StoreReviewRequest extends FormRequest
{
    use HasBilingualMessages;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'visitor_name' => ['required', 'string', 'max:255'],
            'visitor_email' => ['required', 'email', 'max:255'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['required', 'string', 'min:3', 'max:2000'],
            'product_id' => ['nullable', 'exists:products,id'],
        ];
    }

    public function messages(): array
    {
        return $this->bilingual([
            'visitor_name.required' => ['ar' => 'الاسم مطلوب.', 'en' => 'Name is required.'],
            'visitor_name.string' => ['ar' => 'الاسم يجب أن يكون نصاً.', 'en' => 'Name must be a string.'],
            'visitor_name.max' => ['ar' => 'الاسم طويل جداً.', 'en' => 'Name is too long.'],

            'visitor_email.required' => ['ar' => 'البريد الإلكتروني مطلوب.', 'en' => 'Email is required.'],
            'visitor_email.email' => ['ar' => 'البريد الإلكتروني غير صحيح.', 'en' => 'Email is invalid.'],
            'visitor_email.max' => ['ar' => 'البريد الإلكتروني طويل جداً.', 'en' => 'Email is too long.'],

            'rating.required' => ['ar' => 'الرجاء اختيار التقييم.', 'en' => 'Please select a rating.'],
            'rating.integer' => ['ar' => 'التقييم غير صحيح.', 'en' => 'Rating is invalid.'],
            'rating.min' => ['ar' => 'يجب اختيار نجمة واحدة على الأقل.', 'en' => 'At least one star is required.'],
            'rating.max' => ['ar' => 'لا يمكن أن يزيد التقييم عن 5 نجوم.', 'en' => 'Rating cannot exceed 5 stars.'],

            'comment.required' => ['ar' => 'التعليق مطلوب.', 'en' => 'Comment is required.'],
            'comment.string' => ['ar' => 'التعليق يجب أن يكون نصاً.', 'en' => 'Comment must be a string.'],
            'comment.min' => ['ar' => 'التعليق قصير جداً.', 'en' => 'Comment is too short.'],
            'comment.max' => ['ar' => 'التعليق طويل جداً.', 'en' => 'Comment is too long.'],

            'product_id.exists' => ['ar' => 'المنتج المحدد غير موجود.', 'en' => 'The selected product does not exist.'],
        ]);
    }
}
