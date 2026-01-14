<?php

namespace Database\Seeders;

use App\Models\Author;
use App\Models\Book;
use App\Models\City;
use App\Models\Company;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        City::factory()
            ->count(10)
            ->create()
            ->each(function (City $city) {
                Company::factory()
                    ->count(fake()->numberBetween(1, 3))
                    ->create(['city_id' => $city->id]);
            });

        $companyIds = Company::query()->pluck('id');

        Author::factory()
            ->count(50)
            ->create()
            ->each(function (Author $author) use ($companyIds) {
                if ($author->active) {
                    $count = fake()->numberBetween(2, 4);
                    for ($i = 0; $i < $count; $i++) {
                        Book::factory()->create([
                            'author_id'  => $author->id,
                            'company_id' => $companyIds->random(),
                        ]);
                    }
                }
            });
    }
}
