<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\Concerns\HasBilingualMessages;

class UpdateAddressRequest extends FormRequest
{
    use HasBilingualMessages;

    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'country' => ['sometimes', 'string', 'max:100'],
            'city' => ['sometimes', 'string', 'max:100'],
            'region' => ['sometimes', 'string', 'max:100'],
            'street' => ['sometimes', 'string', 'max:255'],
            'building' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return $this->bilingual([
            'country.max' => ['ar' => 'اسم الدولة طويل جداً.', 'en' => 'Country name is too long.'],
            'city.max' => ['ar' => 'اسم المدينة طويل جداً.', 'en' => 'City name is too long.'],
            'region.max' => ['ar' => 'اسم المنطقة طويل جداً.', 'en' => 'Region name is too long.'],
            'street.max' => ['ar' => 'اسم الشارع طويل جداً.', 'en' => 'Street is too long.'],
            'building.max' => ['ar' => 'اسم المبنى طويل جداً.', 'en' => 'Building is too long.'],
        ]);
    }
}
