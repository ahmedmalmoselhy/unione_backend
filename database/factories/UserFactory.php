<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'national_id'          => fake()->unique()->numerify('##############'),
            'first_name'           => fake()->firstName(),
            'last_name'            => fake()->lastName(),
            'email'                => fake()->unique()->safeEmail(),
            'email_verified_at'    => now(),
            'password'             => 'password', // hashed cast will hash this
            'phone'                => fake()->phoneNumber(),
            'gender'               => fake()->randomElement(['male', 'female']),
            'date_of_birth'        => fake()->dateTimeBetween('-50 years', '-18 years')->format('Y-m-d'),
            'is_active'            => true,
            'must_change_password' => false,
            'remember_token'       => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
