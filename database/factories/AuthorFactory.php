<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class AuthorFactory extends Factory
{
    public function definition(): array
    {
        return [
            'first_name'  => $this->faker->firstName(),
            'last_name'   => $this->faker->lastName(),
            'father_name' => $this->faker->firstName(),
            'birth_date'  => $this->faker->date('Y-m-d', '-18 years'),
            'biography'   => $this->faker->paragraphs(3, true),
            'gender'      => $this->faker->boolean(), // true/false
            'active'      => $this->faker->boolean(), // true/false
        ];
    }
}
