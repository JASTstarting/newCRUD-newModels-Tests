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
        $author = Author::factory()->create(['active' => true]);
        $city   = City::factory()->create();
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
            'name'         => 'Test Book',
            'description'  => 'Description',
            'created_date' => now()->toDateString(),
            'author_id'    => $author->getKey(),
            'company_id'   => $company->getKey(),
        ];
        $this->post(route('books.store'), $payload)->assertStatus(302);

        $this->assertDatabaseHas('books', ['name' => 'Test Book']);

        $book = Book::query()->where('name', 'Test Book')->firstOrFail();

        // update
        $updatePayload = [
            'name'         => 'Updated Book',
            'description'  => 'New Description',
            'created_date' => now()->subDay()->toDateString(),
            'author_id'    => $author->getKey(),
            'company_id'   => $company->getKey(),
        ];
        $this->put(route('books.update', ['book' => $book->getKey()]), $updatePayload)->assertStatus(302);
        $this->assertDatabaseHas('books', ['id' => $book->getKey(), 'name' => 'Updated Book']);

        // delete (soft)
        $this->delete(route('books.destroy', ['book' => $book->getKey()]))->assertStatus(302);
        $this->assertSoftDeleted('books', ['id' => $book->getKey()]);
    }

    public function test_book_store_validation_errors(): void
    {
        Queue::fake();

        // Пустой запрос
        $this->post(route('books.store'))->assertStatus(302)->assertSessionHasErrors([
            'name', 'description', 'created_date', 'author_id', 'company_id'
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
            'name'         => '',
            'description'  => '',
            'created_date' => 'not-a-date',
            'author_id'    => 999999,
            'company_id'   => 999999,
        ];

        $response = $this->put(route('books.update', ['book' => $book->getKey()]), $bad);

        $response->assertStatus(302);
        $response->assertSessionHasErrors([
            'name',
            'description',
            'created_date',
            'author_id',
            'company_id'
        ]);

        $this->assertDatabaseHas('books', [
            'id' => $book->getKey(),
            'name' => $book->getAttribute('name'),
        ]);
    }

    public function test_edit_not_found_404(): void
    {
        $this->get(route('books.edit', ['book' => 999999]))->assertStatus(404);
    }

    public function test_ajax_index(): void
    {
        [$author, $company] = $this->makeAuthorAndCompany();

        Book::factory()->count(2)->create([
            'author_id'  => $author->getKey(),
            'company_id' => $company->getKey(),
        ]);

        $payload = ['page' => 1, 'search' => ''];
        $res = $this->get(route('books.ajax', $payload));
        $res->assertStatus(200);
        $json = $res->json();
        $this->assertArrayHasKey('data', $json);
        $this->assertIsArray($json['data']);
    }

    public function test_dispatches_process_book_creation_job(): void
    {
        Queue::fake();

        [$author, $company] = $this->makeAuthorAndCompany();

        $payload = [
            'name' => 'Test Book',
            'description' => 'Description',
            'created_date' => now()->toDateString(),
            'author_id' => $author->getKey(),
            'company_id' => $company->getKey(),
        ];

        $this->post(route('books.store'), $payload)->assertStatus(302);

        Queue::assertPushed(ProcessBookCreation::class, function () {
            return true;
        });
    }

    public function test_book_edit_page(): void
    {
        [$author, $company] = $this->makeAuthorAndCompany();
        $book = Book::factory()->create([
            'author_id' => $author->getKey(),
            'company_id' => $company->getKey(),
        ]);

        $response = $this->get(route('books.edit', $book));
        $response->assertStatus(200);
        $response->assertSee($book->getAttribute('name'));
    }

    public function test_book_restore_from_trash(): void
    {
        [$author, $company] = $this->makeAuthorAndCompany();
        $book = Book::factory()->create([
            'author_id' => $author->getKey(),
            'company_id' => $company->getKey(),
        ]);

        $this->delete(route('books.destroy', $book))->assertStatus(302);
        $this->assertSoftDeleted('books', ['id' => $book->getKey()]);

        $this->post(route('trash.books.restore', $book->getKey()))->assertStatus(302);
        $this->assertDatabaseHas('books', ['id' => $book->getKey(), 'deleted_at' => null]);
    }
}
