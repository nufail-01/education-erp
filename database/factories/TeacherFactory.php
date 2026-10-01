<?php

namespace Database\Factories;

use App\Models\Institute;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Teacher>
 */
class TeacherFactory extends Factory
{
    protected $model = Teacher::class;

    public function definition(): array
    {
        $institute = Institute::factory();

        return [
            'institute_id' => $institute,
            'user_id' => User::factory()->state(fn () => []),
            'employee_code' => strtoupper(fake()->unique()->bothify('EMP-####')),
            'qualification' => fake()->randomElement(['B.Ed', 'M.Ed', 'M.Sc', 'M.A', 'B.Sc', 'Ph.D']),
            'joining_date' => fake()->dateTimeBetween('-5 years', 'now')->format('Y-m-d'),
            'status' => Teacher::STATUS_ACTIVE,
        ];
    }

   
    public function forInstitute(Institute $institute): static
    {
        return $this->state(fn () => [
            'institute_id' => $institute->id,
            'user_id' => User::factory()->forInstitute($institute),
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['status' => Teacher::STATUS_INACTIVE]);
    }
}