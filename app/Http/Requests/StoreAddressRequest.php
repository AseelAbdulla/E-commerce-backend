<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\Concerns\HasBilingualMessages;

class StoreAddressRequest extends FormRequest
{
    use HasBilingualMessages;

    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'country' => ['required', 'string', 'max:100'],
            'city' => ['required', 'string', 'max:100'],
            'region' => ['required', 'string', 'max:100'],
            'street' => ['required', 'string', 'max:255'],
            'building' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return $this->bilingual([
            'country.required' => ['ar' => 'حقل الدولة مطلوب.', 'en' => 'Country is required.'],
            'country.max' => ['ar' => 'اسم الدولة طويل جداً.', 'en' => 'Country name is too long.'],

            'city.required' => ['ar' => 'حقل المدينة مطلوب.', 'en' => 'City is required.'],
            'city.max' => ['ar' => 'اسم المدينة طويل جداً.', 'en' => 'City name is too long.'],

            'region.required' => ['ar' => 'حقل المنطقة مطلوب.', 'en' => 'Region is required.'],
            'region.max' => ['ar' => 'اسم المنطقة طويل جداً.', 'en' => 'Region name is too long.'],

            'street.required' => ['ar' => 'حقل الشارع مطلوب.', 'en' => 'Street is required.'],
            'street.max' => ['ar' => 'اسم الشارع طويل جداً.', 'en' => 'Street is too long.'],

            'building.max' => ['ar' => 'اسم المبنى طويل جداً.', 'en' => 'Building is too long.'],
        ]);
    }
}