<?php

namespace App\Http\Requests\Settings;

use Illuminate\Foundation\Http\FormRequest;

class CompanySettingsUpdateRequest extends FormRequest
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
            'description' => ['nullable', 'string', 'max:5000'],
            'phone' => ['nullable', 'string', 'max:40'],
            'email' => ['nullable', 'email:rfc', 'max:255'],
            'website' => ['nullable', 'url', 'max:255'],
            'industry' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:500'],
            'served_areas' => ['nullable', 'array', 'max:20'],
            'served_areas.*' => ['string', 'max:120'],
            'logo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,svg,webp', 'max:2048'],
            'primary_color' => ['nullable', 'string', 'max:20'],
            'secondary_color' => ['nullable', 'string', 'max:20'],
            'quote_prefix' => ['required', 'string', 'max:10'],
            'invoice_prefix' => ['required', 'string', 'max:10'],
            'tax1_name' => ['nullable', 'string', 'max:50'],
            'tax1_rate' => ['nullable', 'numeric', 'between:0,100'],
            'tax2_name' => ['nullable', 'string', 'max:50'],
            'tax2_rate' => ['nullable', 'numeric', 'between:0,100'],
            'currency' => ['required', 'string', 'size:3'],
            'terms_and_conditions' => ['nullable', 'string', 'max:5000'],
            'legal_mentions' => ['nullable', 'array', 'max:10'],
            'legal_mentions.*.key' => ['required_with:legal_mentions', 'string', 'max:50'],
            'legal_mentions.*.value' => ['required_with:legal_mentions', 'string', 'max:150'],
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
            'tax1_rate.between' => 'Le taux de la taxe 1 doit être situé entre 0 et 100.',
            'tax2_rate.between' => 'Le taux de la taxe 2 doit être situé entre 0 et 100.',
            'website.url' => 'Le site web doit être une URL valide (ex: https://maboutique.com).',
            'logo.image' => 'Le logo doit être une image.',
            'logo.max' => 'Le logo ne doit pas dépasser 2Mo.',
        ];
    }
}
