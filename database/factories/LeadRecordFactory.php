<?php

namespace Database\Factories;

use App\Models\LeadForm;
use App\Models\LeadRecord;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<LeadRecord>
 */
class LeadRecordFactory extends Factory
{
    protected $model = LeadRecord::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'lead_form_id' => LeadForm::factory(),
            'name' => $this->faker->name(),
            'email' => $this->faker->safeEmail(),
            'phone' => $this->faker->phoneNumber(),
            'payload' => [
                'service' => $this->faker->randomElement(['Bathroom', 'Kitchen', 'Roof']),
                'budget' => $this->faker->randomElement(['5k-10k', '10k-25k']),
            ],
            'source' => 'widget',
        ];
    }
}
