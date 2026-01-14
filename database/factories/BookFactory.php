<?php

namespace Database\Factories;

use App\Models\Author;
use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

class BookFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title'         => $this->faker->sentence(3),
            'description'   => $this->faker->paragraphs(3, true),
            'created_date'  => $this->faker->dateTimeBetween()->format('Y-m-d'),
            'author_id'     => Author::factory(),
            'company_id'    => Company::factory(),
        ];
    }
}
