<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\LeadForm;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<LeadForm>
 */
class LeadFormFactory extends Factory
{
    protected $model = LeadForm::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'name' => $this->faker->words(3, true),
            'embed_key' => (string) $this->faker->unique()->uuid(),
            'is_active' => true,
            'submit_button_label' => 'Send request',
        ];
    }
}
