<?php

namespace Database\Factories;

use App\Models\LeadForm;
use App\Models\LeadFormField;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<LeadFormField>
 */
class LeadFormFieldFactory extends Factory
{
    protected $model = LeadFormField::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'lead_form_id' => LeadForm::factory(),
            'label' => $this->faker->randomElement(['Name', 'Phone', 'Email']),
            'field_key' => $this->faker->unique()->slug(2, '_'),
            'type' => $this->faker->randomElement(['text', 'email', 'tel']),
            'is_required' => $this->faker->boolean(70),
            'placeholder' => null,
            'options' => [],
            'sort_order' => $this->faker->numberBetween(0, 10),
        ];
    }
}
