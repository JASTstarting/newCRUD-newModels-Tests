<?php

namespace App\Repository;

use App\Http\Requests\BookUpdateRequest;
use App\Jobs\ProcessBookCreation;
use App\Models\Book;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;

class BookRepository
{
    public function paginateWithFilters(?string $search = null, int $perPage = 10): LengthAwarePaginator
    {
        $search = $search ?? '';
        $page = (int)request()->input('page', 1);
        $cacheKey = 'books:index:' . md5($search . '|' . $page . '|' . $perPage);
        $ttl = config('cache.books_ttl', 60);

        return Cache::remember($cacheKey, $ttl, function () use ($search, $perPage) {
            $q = Book::query()
                ->with([
                    'author:id,first_name,last_name',
                    'company:id,name,city_id',
                    'company.city:id,name',
                ])
                ->select(['id','name','description','created_date','author_id','company_id'])
                ->orderByDesc('id');

            if ($search) {
                $q->where('name', 'like', "%$search%");
            }

            return $q->paginate($perPage)->appends(['search' => $search]);
        });
    }

    public function store(array $data): Book
    {
        $book = new Book($data);
        $book->save();

        ProcessBookCreation::dispatch($book->id);
        $this->clearAllBooksCache();
        return $book;
    }

    public function update(Book $book, BookUpdateRequest $request): Book
    {
        $book->update($request->validated());
        $this->clearAllBooksCache();
        return $book->refresh();
    }

    public function delete(Book $book): void
    {
        $book->delete();
        $this->clearAllBooksCache();
    }

    private function clearAllBooksCache(): void
    {
        Cache::flush();
    }
}
