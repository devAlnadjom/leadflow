<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreLeadFormRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:120'],
            'is_active' => ['sometimes', 'boolean'],
            'submit_button_label' => ['nullable', 'string', 'max:50'],
            'layout_mode' => ['nullable', 'string', 'in:stack,slider'],
            'fields' => ['required', 'array', 'min:2', 'max:20'],
            'fields.*.label' => ['required', 'string', 'max:120'],
            'fields.*.type' => ['required', 'string', 'in:text,email,tel,textarea,select,radio,checkbox'],
            'fields.*.required' => ['sometimes', 'boolean'],
            'fields.*.placeholder' => ['nullable', 'string', 'max:160'],
            'fields.*.options' => ['nullable', 'array', 'max:30'],
            'fields.*.options.*' => ['string', 'max:80'],
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            /** @var array<int, array<string, mixed>> $fields */
            $fields = $this->input('fields', []);

            $hasNameField = collect($fields)->contains(
                fn (array $field): bool => str_contains(strtolower((string) ($field['label'] ?? '')), 'name')
                    || str_contains(strtolower((string) ($field['label'] ?? '')), 'nom'),
            );

            $hasContactField = collect($fields)->contains(
                fn (array $field): bool => in_array($field['type'] ?? null, ['email', 'tel'], true),
            );

            if (! $hasNameField) {
                $validator->errors()->add('fields', 'The widget must contain a name field.');
            }

            if (! $hasContactField) {
                $validator->errors()->add('fields', 'The widget must contain at least one contact field (email or phone).');
            }
        });
    }
}
