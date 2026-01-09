<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class AuthorFactory extends Factory
{
    public function definition(): array
    {
        return [
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'father_name' => $this->faker->name(),
            'birth_date' => $this->faker->dateTimeBetween('-50 years', '-18 years'),
            'biography' => $this->faker->text(),
            'gender' => rand(0, 1),
            'active' => rand(0, 1),
        ];
    }
}
