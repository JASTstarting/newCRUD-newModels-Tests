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
            'name'         => $this->faker->sentence(3),
            'description'  => $this->faker->paragraph(),
            'created_date' => $this->faker->date(),
            'author_id'    => Author::factory(),
            'company_id'   => Company::factory(),
        ];
    }
}
