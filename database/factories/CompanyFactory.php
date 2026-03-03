<?php

namespace Database\Factories;

use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<Company>
 */
class CompanyFactory extends Factory
{
    protected $model = Company::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->company(),
            'phone' => $this->faker->phoneNumber(),
            'email' => $this->faker->companyEmail(),
            'industry' => $this->faker->randomElement(['Construction', 'Beauty', 'Services']),
            'address' => $this->faker->address(),
            'served_areas' => [$this->faker->city(), $this->faker->city()],
            'primary_color' => '#1D4ED8',
            'secondary_color' => '#0F172A',
        ];
    }
}
