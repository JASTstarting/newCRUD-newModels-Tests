<?php

namespace Tests\Feature;

use App\Models\Author;
use App\Models\Book;
use App\Models\City;
use App\Models\Company;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthorTest extends TestCase
{
    use RefreshDatabase;

    private function makeCompany(): Company
    {
        $city = City::factory()->create();
        return Company::factory()->create(['city_id' => $city->getKey()]);
    }

    public function test_author_store_update_delete(): void
    {
        // store
        $payload = [
            'first_name'  => 'Иван',
            'last_name'   => 'Иванов',
            'father_name' => 'Иванович',
            'birth_date'  => now()->subYears(30)->toDateString(),
            'biography'   => 'Краткая биография',
            'gender'      => 1,
            'active'      => 1,
        ];
        $this->post(route('authors.store'), $payload)->assertStatus(302);
        $this->assertDatabaseHas('authors', ['first_name' => 'Иван', 'last_name' => 'Иванов']);

        $author = Author::where('last_name', 'Иванов')->firstOrFail();

        $company = $this->makeCompany();
        $book = Book::factory()->create([
            'author_id'  => $author->getKey(),
            'company_id' => $company->getKey(),
        ]);

        // update
        $update = [
            'first_name'  => 'Пётр',
            'last_name'   => 'Петров',
            'father_name' => 'Петрович',
            'birth_date'  => now()->subYears(25)->toDateString(),
            'biography'   => 'Обновлённая биография',
            'gender'      => 0,
            'active'      => 0,
        ];
        $this->put(route('authors.update', $author->id), $update)->assertStatus(302);
        $this->assertDatabaseHas('authors', ['id' => $author->id, 'first_name' => 'Пётр']);

        // delete (soft) — вместе с книгами
        $this->delete(route('authors.destroy', $author->id))->assertStatus(302);
        $this->assertSoftDeleted('authors', ['id' => $author->id]);
        $this->assertSoftDeleted('books', ['id' => $book->getKey()]);
    }

    public function test_author_store_validation_errors(): void
    {
        // Пустой запрос
        $response = $this->post(route('authors.store'));
        $response->assertStatus(302);
        $response->assertSessionHasErrors(['first_name', 'last_name', 'father_name', 'gender']);
    }

    public function test_author_update_validation_errors(): void
    {
        $author = Author::factory()->create();

        $badData = [
            'first_name'  => '',
            'last_name'   => '',
            'father_name' => '',
            'birth_date'  => 'not-a-date',
            'gender'      => 3,
        ];

        $response = $this->put(route('authors.update', $author->getKey()), $badData);
        $response->assertStatus(302);
        $response->assertSessionHasErrors(['first_name', 'last_name', 'father_name', 'gender']);
    }

    public function test_author_update_not_found(): void
    {
        $response = $this->put(route('authors.update', 999999), [
            'first_name'  => 'Test',
            'last_name'   => 'Test',
            'father_name' => 'Test',
            'birth_date'  => now()->toDateString(),
            'gender'      => 1,
        ]);
        $response->assertStatus(404);
    }

    public function test_author_destroy_not_found(): void
    {
        $response = $this->delete(route('authors.destroy', 999999));
        $response->assertStatus(404);
    }

    public function test_author_restore_restores_books_too(): void
    {
        $author  = Author::factory()->create();
        $company = $this->makeCompany();

        $book1 = Book::factory()->create([
            'author_id'  => $author->getKey(),
            'company_id' => $company->getKey(),
        ]);

        $book2 = Book::factory()->create([
            'author_id'  => $author->getKey(),
            'company_id' => $company->getKey(),
        ]);

        // Удаляем автора -> его книги тоже должны удалиться (soft)
        $this->delete(route('authors.destroy', $author->getKey()))->assertStatus(302);
        $this->assertSoftDeleted('authors', ['id' => $author->getKey()]);
        $this->assertSoftDeleted('books', ['id' => $book1->getKey()]);
        $this->assertSoftDeleted('books', ['id' => $book2->getKey()]);

        // Восстанавливаем автора: восстанавливаются все его удалённые книги
        $this->post(route('trash.authors.restore', $author->getKey()))->assertStatus(302);

        $this->assertDatabaseHas('authors', ['id' => $author->getKey(), 'deleted_at' => null]);
        $this->assertDatabaseHas('books', ['id' => $book1->getKey(), 'deleted_at' => null]);
        $this->assertDatabaseHas('books', ['id' => $book2->getKey(), 'deleted_at' => null]);
    }

    public function test_author_unique_validation_prevents_duplicates(): void
    {
        Author::factory()->create([
            'first_name'  => 'Иван',
            'last_name'   => 'Иванов',
            'father_name' => 'Иванович',
            'birth_date'  => '1990-01-01',
        ]);

        $duplicateData = [
            'first_name'  => 'Иван',
            'last_name'   => 'Иванов',
            'father_name' => 'Иванович',
            'birth_date'  => '1990-01-01',
            'biography'   => 'Тестовая биография',
            'gender'      => 1,
            'active'      => 1,
        ];

        $response = $this->post(route('authors.store'), $duplicateData);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('first_name');
        $this->assertCount(1, Author::withTrashed()->get());
    }

    public function test_author_unique_validation_allows_different_birth_dates(): void
    {
        Author::factory()->create([
            'first_name'  => 'Иван',
            'last_name'   => 'Иванов',
            'father_name' => 'Иванович',
            'birth_date'  => '1990-01-01',
        ]);

        $newAuthorData = [
            'first_name'  => 'Иван',
            'last_name'   => 'Иванов',
            'father_name' => 'Иванович',
            'birth_date'  => '1991-01-01',
            'biography'   => 'Другая биография',
            'gender'      => 1,
            'active'      => 1,
        ];

        $response = $this->post(route('authors.store'), $newAuthorData);

        $response->assertStatus(302);
        $response->assertSessionMissing('errors');
        $this->assertCount(2, Author::withTrashed()->get());
    }
}
