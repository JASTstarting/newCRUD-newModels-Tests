<?php

namespace App\Repository;

use App\Models\Author;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;

class AuthorRepository
{
    /**
     * Создать нового автора
     * @throws Exception
     */
    public function store(array $data): Author
    {
        try {
            return Author::create($data);
        } catch (Exception $e) {
            Log::error('AuthorRepository store error: ' . $e->getMessage(), [
                'data' => $data,
            ]);
            throw $e;
        }
    }

    /**
     * Обновить данные автора
     * @throws Exception
     */
    public function update(Author $author, array $data): Author
    {
        try {
            $author->update($data);
            return $author->fresh();
        } catch (Exception $e) {
            Log::error('AuthorRepository update error: ' . $e->getMessage(), [
                'author_id' => $author->id,
                'data'      => $data,
            ]);
            throw $e;
        }
    }

    /**
     * Удалить автора (мягкое удаление)
     * @throws Exception
     */
    public function delete(Author $author): bool
    {
        try {
            return (bool) $author->delete();
        } catch (Exception $e) {
            Log::error('AuthorRepository delete error: ' . $e->getMessage(), [
                'author_id' => $author->id,
            ]);
            throw $e;
        }
    }

    /**
     * Пагинация авторов с фильтрами/поиском/сортировкой (для веб-интерфейса).
     * filters:
     * - search: string|null
     * - gender: '0'|'1'|null
     * - active: '0'|'1'|null
     * - sort: last_name|first_name|father_name|birth_date|gender|active|null
     * - direction: asc|desc|null
     */
    public function filterPaginate(array $filters, int $perPage = 10): LengthAwarePaginator
    {
        $perPage = max(1, min(100, $perPage));

        $search    = isset($filters['search']) ? trim((string) $filters['search']) : '';
        $gender    = array_key_exists('gender', $filters) ? $filters['gender'] : null;
        $active    = array_key_exists('active', $filters) ? $filters['active'] : null;
        $sort      = $filters['sort']      ?? null;
        $direction = ($filters['direction'] ?? 'asc') === 'desc' ? 'desc' : 'asc';

        $query = Author::query()->withoutTrashed();

        if ($search !== '' && mb_strlen($search) >= 2) {
            $term = $search;
            $query->where(function (Builder $q) use ($term) {
                $q->where('last_name', 'like', "%$term%")
                    ->orWhere('first_name', 'like', "%$term%")
                    ->orWhere('father_name', 'like', "%$term%");
            });
        }

        if ($gender !== null && $gender !== '') {
            $query->where('gender', (int) $gender);
        }
        if ($active !== null && $active !== '') {
            $query->where('active', (int) $active);
        }

        $allowedSorts = ['last_name','first_name','father_name','birth_date','gender','active'];
        if ($sort && in_array($sort, $allowedSorts, true)) {
            $query->orderBy($sort, $direction);

            foreach (['last_name', 'first_name'] as $fallback) {
                if ($fallback !== $sort) {
                    $query->orderBy($fallback);
                }
            }
            $query->orderBy('id');
        } else {
            $query->orderBy('last_name')
                ->orderBy('first_name')
                ->orderBy('id');
        }

        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * Применить поиск к запросу.
     * Сохранено для совместимости с API-методами, где это зовется.
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

        return $query->where(function ($q) use ($search) {
            $q->where('first_name', 'like', "%$search%")
                ->orWhere('last_name', 'like', "%$search%")
                ->orWhere('father_name', 'like', "%$search%");
        });
    }

    /**
     * Получить всех авторов.
     */
    public function getAllAuthors(?string $search = null, int $perPage = 10): LengthAwarePaginator
    {
        $perPage = max(1, min(100, $perPage));

        $query = Author::withoutTrashed();
        $query = $this->applySearch($query, $search);

        return $query->orderBy('last_name')
            ->orderBy('first_name')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Получить авторов для API (с книгами)
     */
    public function getAuthorsForApi(?string $search = null, int $perPage = 10): LengthAwarePaginator
    {
        $perPage = max(1, min(100, $perPage));

        $query = Author::withoutTrashed()
            ->with([
                'books' => function ($q) {
                    $q->select('id', 'title', 'author_id')->orderBy('title');
                },
            ])
            ->withCount('books')
            ->select('id', 'first_name', 'last_name', 'father_name');

        $query = $this->applySearch($query, $search);

        return $query->orderBy('last_name')
            ->orderBy('first_name')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Найти активного автора по ID
     */
    public function find(int $id): ?Author
    {
        return Author::withoutTrashed()->find($id);
    }

    /**
     * Найти автора (включая удаленных) - для корзины
     */
    public function findWithTrashed(int $id): ?Author
    {
        return Author::withTrashed()->find($id);
    }

    public function findWithBooks(int $id): ?Author
    {
        return Author::withoutTrashed()
            ->with([
                'books' => function ($q) {
                    $q->select('id', 'title', 'author_id')->orderBy('title');
                },
            ])
            ->find($id);
    }

    /**
     * Получить удаленных авторов - для корзины
     */
    public function getTrashed(?string $search = null, int $perPage = 10): LengthAwarePaginator
    {
        $perPage = max(1, min(100, $perPage));

        $query = Author::onlyTrashed();
        $query = $this->applySearch($query, $search);

        return $query->orderBy('deleted_at', 'desc')
            ->paginate($perPage)
            ->withQueryString();
    }
}
