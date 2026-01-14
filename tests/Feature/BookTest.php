<?php

namespace Tests\Feature;

use App\Jobs\ProcessBookCreation;
use App\Models\Author;
use App\Models\Book;
use App\Models\City;
use App\Models\Company;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class BookTest extends TestCase
{
    use RefreshDatabase;

    private function makeAuthorAndCompany(): array
    {
        $author  = Author::factory()->create(['active' => true]);
        $city    = City::factory()->create();
        $company = Company::factory()->create(['city_id' => $city->getKey()]);
        return [$author, $company];
    }

    public function test_books_index(): void
    {
        [$author, $company] = $this->makeAuthorAndCompany();

        Book::factory()->count(3)->create([
            'author_id'  => $author->getKey(),
            'company_id' => $company->getKey(),
        ]);

        $response = $this->get(route('books.index'));
        $response->assertStatus(200);
        $response->assertSee('Книги');
    }

    public function test_book_store_update_delete_soft(): void
    {
        [$author, $company] = $this->makeAuthorAndCompany();

        // store
        $payload = [
            'title'        => 'Test Book',
            'description'  => 'Description',
            'created_date' => now()->toDateString(),
            'author_id'    => $author->getKey(),
            'company_id'   => $company->getKey(),
        ];
        $this->post(route('books.store'), $payload)->assertStatus(302);

        $this->assertDatabaseHas('books', ['title' => 'Test Book']);

        $book = Book::query()->where('title', 'Test Book')->firstOrFail();

        // update
        $updatePayload = [
            'title'        => 'Updated Book',
            'description'  => 'New Description',
            'created_date' => now()->subDay()->toDateString(),
            'author_id'    => $author->getKey(),
            'company_id'   => $company->getKey(),
        ];
        $this->put(route('books.update', ['book' => $book->getKey()]), $updatePayload)->assertStatus(302);
        $this->assertDatabaseHas('books', ['id' => $book->getKey(), 'title' => 'Updated Book']);

        // delete (soft)
        $this->delete(route('books.destroy', ['book' => $book->getKey()]))->assertStatus(302);
        $this->assertSoftDeleted('books', ['id' => $book->getKey()]);
    }

    public function test_book_store_validation_errors(): void
    {
        Queue::fake();

        // Пустой запрос
        $this->post(route('books.store'))->assertStatus(302)->assertSessionHasErrors([
            'title', 'description', 'created_date', 'author_id', 'company_id'
        ]);

        Queue::assertNotPushed(ProcessBookCreation::class);
    }

    public function test_book_update_validation_errors(): void
    {
        [$author, $company] = $this->makeAuthorAndCompany();
        $book = Book::factory()->create([
            'author_id'  => $author->id,
            'company_id' => $company->id,
        ]);

        // Плохие значения
        $bad = [
            'title'        => '',
            'description'  => '',
            'created_date' => 'not-a-date',
            'author_id'    => 999999,
            'company_id'   => 999999,
        ];

        $response = $this->put(route('books.update', ['book' => $book->getKey()]), $bad);

        $response->assertStatus(302);
        $response->assertSessionHasErrors([
            'title',
            'description',
            'created_date',
            'author_id',
            'company_id'
        ]);

        $this->assertDatabaseHas('books', [
            'id'    => $book->getKey(),
            'title' => $book->getAttribute('title'),
        ]);
    }

    public function test_edit_not_found_404(): void
    {
        $this->get(route('books.edit', ['book' => 999999]))->assertStatus(404);
    }

    public function test_api_index(): void
    {
        // Включаем API-флаг, иначе контроллер вернёт 503
        config(['features.api.books.enabled' => true]);

        [$author, $company] = $this->makeAuthorAndCompany();

        Book::factory()->count(2)->create([
            'author_id'  => $author->getKey(),
            'company_id' => $company->getKey(),
        ]);

        $payload = ['page' => 1, 'search' => ''];
        $res = $this->get(route('books.api', $payload));
        $res->assertStatus(200);

        $json = $res->json();
        $this->assertArrayHasKey('data', $json);
        $this->assertIsArray($json['data']);
        $this->assertArrayHasKey('meta', $json);
        $this->assertArrayHasKey('links', $json);
    }

    public function test_dispatches_process_book_creation_job(): void
    {
        Queue::fake();

        [$author, $company] = $this->makeAuthorAndCompany();

        $payload = [
            'title'        => 'Test Book',
            'description'  => 'Description',
            'created_date' => now()->toDateString(),
            'author_id'    => $author->getKey(),
            'company_id'   => $company->getKey(),
        ];

        $this->post(route('books.store'), $payload)->assertStatus(302);

        Queue::assertPushed(ProcessBookCreation::class, function ($job) {
            return isset($job->bookId) && is_int($job->bookId);
        });
    }

    public function test_book_edit_page(): void
    {
        [$author, $company] = $this->makeAuthorAndCompany();
        $book = Book::factory()->create([
            'author_id'  => $author->getKey(),
            'company_id' => $company->getKey(),
        ]);

        $response = $this->get(route('books.edit', $book));
        $response->assertStatus(200);
        $response->assertSee($book->getAttribute('title'));
    }

    public function test_book_restore_from_trash_restores_only_that_book(): void
    {
        // Проверяем логику: восстановление одной книги поднимает автора, но НЕ остальные книги
        [$author, $company] = $this->makeAuthorAndCompany();

        $book1 = Book::factory()->create([
            'author_id'  => $author->getKey(),
            'company_id' => $company->getKey(),
        ]);
        $book2 = Book::factory()->create([
            'author_id'  => $author->getKey(),
            'company_id' => $company->getKey(),
        ]);

        // Удаляем автора — обе книги в корзину
        $this->delete(route('authors.destroy', $author->getKey()))->assertStatus(302);
        $this->assertSoftDeleted('authors', ['id' => $author->getKey()]);
        $this->assertSoftDeleted('books', ['id' => $book1->getKey()]);
        $this->assertSoftDeleted('books', ['id' => $book2->getKey()]);

        // Восстанавливаем ТОЛЬКО книгу book1
        $this->post(route('trash.books.restore', $book1->getKey()))->assertStatus(302);

        // Автор должен быть активен
        $this->assertDatabaseHas('authors', ['id' => $author->getKey(), 'deleted_at' => null]);

        // Восстановилась только book1
        $this->assertDatabaseHas('books', ['id' => $book1->getKey(), 'deleted_at' => null]);
        $this->assertSoftDeleted('books', ['id' => $book2->getKey()]);
    }
}
