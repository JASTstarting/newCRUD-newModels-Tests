<?php

namespace App\Repository;

use App\Jobs\ProcessBookCreation;
use App\Models\Author;
use App\Models\Book;
use App\Models\Company;
use App\Models\City;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;


class BookRepository
{
    /**
     * Получить книги с фильтрацией и пагинацией (для UI)
     */
    public function paginateWithFilters(?string $search = null, int $perPage = 10): LengthAwarePaginator
    {
        $perPage = max(1, min(100, $perPage));

        $query = Book::query()
            ->with([
                'author:id,first_name,last_name',
                'company:id,name,city_id',
                'company.city:id,name',
            ])
            ->select(['id', 'title', 'description', 'created_date', 'author_id', 'company_id'])
            ->orderByDesc('id');

        $query = $this->applySearch($query, $search);

        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * Создать новую книгу
     * @throws Exception
     */
    public function store(array $data): Book
    {
        try {
            $book = Book::query()->create($data);
            ProcessBookCreation::dispatch($book->id)
                ->delay(now()->addSeconds(2));
            return $book;
        } catch (Exception $e) {
            Log::error('BookRepository store error: ' . $e->getMessage(), [
                'data' => $data,
            ]);
            throw $e;
        }
    }

    /**
     * Обновить данные книги
     * @throws Exception
     */
    public function update(Book $book, array $data): Book
    {
        try {
            $book->update($data);
            return $book->fresh();
        } catch (Exception $e) {
            Log::error('BookRepository update error: ' . $e->getMessage(), [
                'book_id' => $book->id,
                'data'    => $data,
            ]);
            throw $e;
        }
    }

    /**
     * Удалить книгу (мягкое удаление)
     * @throws Exception
     */
    public function delete(Book $book): bool
    {
        try {
            return (bool) $book->delete();
        } catch (Exception $e) {
            Log::error('BookRepository delete error: ' . $e->getMessage(), [
                'book_id' => $book->id,
            ]);
            throw $e;
        }
    }

    /**
     * Массовое удаление книг (soft delete)
     * @throws Exception
     */
    public function deleteMultiple(array $ids): int
    {
        try {
            return Book::query()
                ->whereIn('id', $ids)
                ->delete();
        } catch (Exception $e) {
            Log::error('BookRepository deleteMultiple error: ' . $e->getMessage(), [
                'ids' => $ids,
            ]);
            throw $e;
        }
    }

    /**
     * Получить книги для API
     */
    public function getForApi(?string $search = null, int $perPage = 10): LengthAwarePaginator
    {
        $perPage = max(1, min(100, $perPage));

        $query = Book::query()
            ->with([
                'author:id,first_name,last_name',
                'company:id,name,city_id',
                'company.city:id,name',
            ])
            ->select(['id', 'title', 'description', 'created_date', 'author_id', 'company_id']);

        $query = $this->applySearch($query, $search);

        return $query->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Найти книгу с отношениями
     * @throws ModelNotFoundException
     */
    public function findWithRelations(int $id): Book
    {
        return Book::with([
            'author:id,first_name,last_name',
            'company:id,name,city_id',
            'company.city:id,name',
        ])->findOrFail($id);
    }

    /**
     * Получить активных авторов
     */
    public function getActiveAuthors(): EloquentCollection
    {
        return Author::query()
            ->withoutTrashed()
            ->where('active', true)
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get(['id', 'first_name', 'last_name']);
    }

    public function getCompanies(): EloquentCollection
    {
        return Company::query()
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    public function getCities(): EloquentCollection
    {
        return City::query()
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    /**
     * Получить книги по автору
     */
    public function getBooksByAuthor(int $authorId, bool $onlyActive = true): array
    {
        $query = Book::query()->where('author_id', $authorId);

        if (!$onlyActive) {
            $query->withTrashed();
        }

        return $query->orderBy('title')
            ->get(['id', 'title', 'author_id'])
            ->toArray();
    }

    /**
     * Применить поиск к запросу
     */
    private function applySearch(Builder $query, ?string $search): Builder
    {
        if (empty($search)) {
            return $query;
        }

        $search = trim($search);

        if (mb_strlen($search) < 2) {
            return $query;
        }

        $search = str_replace(['%', '_'], ['\%', '\_'], $search);

        return $query->where('title', 'like', "%$search%");
    }

    /**
     * Пагинация книг с фильтрами/поиском/сортировкой (для UI).
     * filters:
     * - search: string|null
     * - author_id: int|null
     * - company_id: int|null
     * - created_date_from: Y-m-d|null
     * - created_date_to: Y-m-d|null
     * - sort: title|created_date|author|company|null
     * - direction: asc|desc|null
     */
    public function filterPaginate(array $filters, int $perPage = 10): LengthAwarePaginator
    {
        $perPage   = max(1, min(100, $perPage));
        $search    = isset($filters['search']) ? trim((string)$filters['search']) : '';
        $authorId  = $filters['author_id'] ?? null;
        $companyId = $filters['company_id'] ?? null;
        $dateFrom  = $filters['created_date_from'] ?? null;
        $dateTo    = $filters['created_date_to'] ?? null;
        $sort      = $filters['sort'] ?? null;
        $direction = ($filters['direction'] ?? 'asc') === 'desc' ? 'desc' : 'asc';

        $query = Book::query()
            ->with([
                'author:id,first_name,last_name',
                'company:id,name,city_id',
                'company.city:id,name',
            ])
            ->select('books.*');

        if ($search !== '' && mb_strlen($search) >= 2) {
            $query->where('title', 'like', "%$search%");
        }

        if (!empty($authorId)) {
            $query->where('author_id', (int)$authorId);
        }
        if (!empty($companyId)) {
            $query->where('company_id', (int)$companyId);
        }

        if (!empty($dateFrom)) {
            $query->whereDate('created_date', '>=', $dateFrom);
        }
        if (!empty($dateTo)) {
            $query->whereDate('created_date', '<=', $dateTo);
        }

        $allowedSorts = ['title', 'created_date', 'author', 'company'];
        if ($sort && in_array($sort, $allowedSorts, true)) {
            if ($sort === 'author') {
                $query->leftJoin('authors', 'authors.id', '=', 'books.author_id')
                    ->orderBy('authors.last_name', $direction)
                    ->orderBy('authors.first_name', $direction)
                    ->orderBy('books.title')
                    ->orderBy('books.id');
            } elseif ($sort === 'company') {
                $query->leftJoin('companies', 'companies.id', '=', 'books.company_id')
                    ->orderBy('companies.name', $direction)
                    ->orderBy('books.title')
                    ->orderBy('books.id');
            } else {
                $query->orderBy($sort, $direction)
                    ->orderBy('title')
                    ->orderBy('id');
            }
        } else {
            $query->orderBy('title')
                ->orderBy('id');
        }

        return $query->paginate($perPage)->withQueryString();
    }
}
