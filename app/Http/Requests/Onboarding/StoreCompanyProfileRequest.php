<?php

namespace App\Http\Requests\Onboarding;

use Illuminate\Foundation\Http\FormRequest;

class StoreCompanyProfileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:40'],
            'email' => ['nullable', 'email:rfc', 'max:255'],
            'industry' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:500'],
            'served_areas' => ['nullable', 'array', 'max:20'],
            'served_areas.*' => ['string', 'max:120'],
            'primary_color' => ['nullable', 'string', 'max:20'],
            'secondary_color' => ['nullable', 'string', 'max:20'],
            'quote_prefix' => ['required', 'string', 'max:10'],
            'invoice_prefix' => ['required', 'string', 'max:10'],
            'default_tax_rate' => ['nullable', 'numeric', 'between:0,100'],
            'currency' => ['required', 'string', 'size:3'],
            'terms_and_conditions' => ['nullable', 'string', 'max:5000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Le nom de l’entreprise est obligatoire.',
            'currency.size' => 'La devise doit contenir 3 caractères (ex: USD).',
            'default_tax_rate.between' => 'Le taux de taxe doit être entre 0 et 100.',
        ];
    }
}
