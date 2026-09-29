<?php

namespace Database\Factories;

use App\Models\Institute;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Institute>
 */
class InstituteFactory extends Factory
{
    protected $model = Institute::class;

    public function definition(): array
    {
        return [
            'name' => fake()->company() . ' School',
            'code' => strtoupper(fake()->unique()->bothify('INS-####')),
            'email' => fake()->unique()->companyEmail(),
            'phone' => fake()->numerify('##########'),
            'address' => fake()->address(),
            'logo' => null,
            'status' => Institute::STATUS_ACTIVE,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['status' => Institute::STATUS_INACTIVE]);
    }
}