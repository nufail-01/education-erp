<?php

namespace Database\Factories;

use App\Models\Institute;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected $model = User::class;

    protected static ?string $password = null;

    public function definition(): array
    {
        return [
            'institute_id' => null,
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'mobile_no' => fake()->numerify('##########'),
            'email_verified_at' => now(),
            
            'password' => static::$password ??= Hash::make('password'),
            'status' => User::STATUS_ACTIVE,
            'remember_token' => Str::random(10),
        ];
    }

    public function forInstitute(Institute|int $institute): static
    {
        return $this->state(fn () => [
            'institute_id' => $institute instanceof Institute ? $institute->id : $institute,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['status' => User::STATUS_INACTIVE]);
    }

    public function unverified(): static
    {
        return $this->state(fn () => ['email_verified_at' => null]);
    }
}