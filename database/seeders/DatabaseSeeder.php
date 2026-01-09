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
            ->has(Company::factory()->count(rand(1,3)), 'companies')
            ->create();

        Author::factory()
            ->count(50)
            ->create()
            ->each(function (Author $author) {
                if ($author->active) {
                    $companyIds = Company::query()->inRandomOrder()->limit(5)->pluck('id');
                    $count = rand(2,4);
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
