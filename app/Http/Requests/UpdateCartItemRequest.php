<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\Concerns\HasBilingualMessages;

class UpdateCartItemRequest extends FormRequest
{
    use HasBilingualMessages;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'quantity' => ['required', 'integer', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return $this->bilingual([
            'quantity.required' => ['ar' => 'الكمية مطلوبة.', 'en' => 'Quantity is required.'],
            'quantity.integer' => ['ar' => 'الكمية يجب أن تكون رقماً صحيحاً.', 'en' => 'Quantity must be an integer.'],
            'quantity.min' => ['ar' => 'يجب أن تكون الكمية أكبر من صفر.', 'en' => 'Quantity must be greater than 0.'],
        ]);
    }
}
