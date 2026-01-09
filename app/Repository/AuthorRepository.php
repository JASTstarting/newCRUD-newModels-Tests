<?php

namespace App\Repository;

use App\Http\Requests\AuthorUpdateRequest;
use App\Models\Author;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;

class AuthorRepository
{
    public function store(array $data): Author
    {
        $author = Author::create($data);
        $this->clearAllAuthorsCache();
        return $author;
    }

    public function update(Author $author, AuthorUpdateRequest $request): Author
    {
        $author->update($request->validated());
        $this->clearAllAuthorsCache();
        return $author;
    }

    public function delete(Author $author): void
    {
        $author->delete();
        $this->clearAllAuthorsCache();
    }

    public function getAllAuthors(?string $search = null): LengthAwarePaginator
    {
        $search = $search ?? '';
        $page = (int)request()->input('page', 1);
        $perPage = 10;

        $cacheKey = 'authors:index:' . md5($search . '|' . $page . '|' . $perPage);
        $ttl = config('cache.authors_ttl', 30);

        return Cache::remember($cacheKey, $ttl, function () use ($search, $perPage) {
            return $this->applySearch(Author::query(), $search)
                ->withCount('books')
                ->orderBy('last_name')
                ->orderBy('first_name')
                ->paginate($perPage)
                ->appends(['search' => $search]);
        });
    }

    private function applySearch(Builder $query, string $search): Builder
    {
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%$search%")
                    ->orWhere('last_name', 'like', "%$search%")
                    ->orWhere('father_name', 'like', "%$search%");
            });
        }
        return $query;
    }

    private function clearAllAuthorsCache(): void
    {
        Cache::flush();
    }
}
