<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Http\Requests\Concerns\HasBilingualMessages;

class StoreOrderRequest extends FormRequest
{
    use HasBilingualMessages;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'address_id' => ['required', 'exists:addresses,id'],
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_phone' => ['required', 'string', 'max:50'],
            'customer_email' => ['required', 'email', 'max:255'],
            'notes' => ['nullable', 'string'],
            'payment_method' => ['required', Rule::in(['cash_on_delivery', 'wallet'])],
            'payment_receipt' => ['nullable', 'required_if:payment_method,wallet', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'wallet_number' => ['nullable', 'required_if:payment_method,wallet', 'string', 'max:50'],
            'shipping_fee' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return $this->bilingual([
            'address_id.required' => ['ar' => 'العنوان مطلوب.', 'en' => 'Address is required.'],
            'address_id.exists' => ['ar' => 'العنوان المحدد غير موجود.', 'en' => 'The selected address does not exist.'],

            'customer_name.required' => ['ar' => 'اسم العميل مطلوب.', 'en' => 'Customer name is required.'],

            'customer_phone.required' => ['ar' => 'رقم الهاتف مطلوب.', 'en' => 'Phone number is required.'],

            'customer_email.required' => ['ar' => 'البريد الإلكتروني مطلوب.', 'en' => 'Email is required.'],
            'customer_email.email' => ['ar' => 'البريد الإلكتروني غير صحيح.', 'en' => 'Email is invalid.'],

            'payment_method.required' => ['ar' => 'طريقة الدفع مطلوبة.', 'en' => 'Payment method is required.'],
            'payment_method.in' => ['ar' => 'طريقة الدفع غير صحيحة.', 'en' => 'Payment method is invalid.'],

            'payment_receipt.required_if' => ['ar' => 'إيصال الدفع مطلوب عند اختيار المحفظة.', 'en' => 'Payment receipt is required when paying by wallet.'],
            'payment_receipt.image' => ['ar' => 'إيصال الدفع يجب أن يكون صورة.', 'en' => 'Payment receipt must be an image.'],
            'payment_receipt.mimes' => ['ar' => 'صيغة الإيصال يجب أن تكون jpg أو jpeg أو png أو webp.', 'en' => 'Receipt format must be jpg, jpeg, png, or webp.'],
            'payment_receipt.max' => ['ar' => 'حجم الإيصال يجب ألا يتجاوز 2 ميجابايت.', 'en' => 'Receipt size must not exceed 2MB.'],

            'wallet_number.required_if' => ['ar' => 'رقم المحفظة مطلوب عند اختيار الدفع بالمحفظة.', 'en' => 'Wallet number is required when paying by wallet.'],

            'shipping_fee.numeric' => ['ar' => 'رسوم الشحن يجب أن تكون رقماً.', 'en' => 'Shipping fee must be a number.'],
            'shipping_fee.min' => ['ar' => 'رسوم الشحن يجب ألا تقل عن صفر.', 'en' => 'Shipping fee must be at least 0.'],
        ]);
    }
}
