<?php

namespace App\Http\Controllers;

use App\Http\Requests\BookStoreRequest;
use App\Http\Requests\BookUpdateRequest;
use App\Models\Book;
use App\Repository\BookRepository;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class BookController extends Controller
{
    public function __construct(
        private readonly BookRepository $bookRepository
    ) {}

    public function index(Request $request): View
    {
        $validated = $request->validate([
            'search'            => 'nullable|string|max:255',
            'author_id'         => 'nullable|integer|exists:authors,id',
            'company_id'        => 'nullable|integer|exists:companies,id',
            'created_date_from' => 'nullable|date',
            'created_date_to'   => 'nullable|date|after_or_equal:created_date_from',
            'sort'              => 'nullable|in:title,created_date,author,company',
            'direction'         => 'nullable|in:asc,desc',
            'per_page'          => 'nullable|integer|min:1|max:100',
        ]);

        $perPage = (int)($validated['per_page'] ?? 10);

        $books = $this->bookRepository->filterPaginate($validated, $perPage);

        $authorsSelect   = $this->getAuthorsForForm();
        $companiesSelect = $this->getCompaniesForForm();
        $citiesSelect    = $this->getCitiesForForm();

        $search = (string)($validated['search'] ?? '');

        return view('books.index', compact('books', 'search', 'authorsSelect', 'companiesSelect', 'citiesSelect'));
    }

    public function create(): View
    {
        $authors   = $this->getAuthorsForForm();
        $companies = $this->getCompaniesForForm();

        if (empty($authors)) {
            Log::warning('No active authors found when creating book');
        }

        if (empty($companies)) {
            Log::warning('No companies found when creating book');
        }

        return view('books.create', compact('authors', 'companies'));
    }

    public function store(BookStoreRequest $request): RedirectResponse
    {
        try {
            $this->bookRepository->store($request->validated());

            return redirect()
                ->route('books.index')
                ->with('success', __('messages.book.created'));
        } catch (Exception $e) {
            Log::error('Book store error: ' . $e->getMessage(), [
                'data' => $request->validated(),
            ]);

            return back()
                ->withInput()
                ->with('error', __('messages.book.create_failed'));
        }
    }

    public function edit(Book $book): View|RedirectResponse
    {
        $book->load([
            'author:id,first_name,last_name',
            'company:id,name,city_id',
            'company.city:id,name',
        ]);

        $authors   = $this->getAuthorsForForm();
        $companies = $this->getCompaniesForForm();

        if (empty($authors)) {
            return back()
                ->with('error', __('messages.book.no_authors_available'));
        }

        if ($book->author_id && !collect($authors)->contains('id', $book->author_id)) {
            Log::warning("Book author (ID: $book->author_id) not in active authors list", [
                'book_id'           => $book->id,
                'available_authors' => $authors,
            ]);
        }

        return view('books.edit', compact('book', 'authors', 'companies'));
    }

    public function update(BookUpdateRequest $request, Book $book): RedirectResponse
    {
        try {
            $this->bookRepository->update($book, $request->validated());

            return redirect()
                ->route('books.index')
                ->with('success', __('messages.book.updated'));
        } catch (Exception $e) {
            Log::error('Book update error: ' . $e->getMessage(), [
                'book_id' => $book->id,
                'data'    => $request->validated(),
            ]);

            return back()
                ->withInput()
                ->with('error', __('messages.book.update_failed'));
        }
    }

    public function destroy(Book $book): RedirectResponse
    {
        try {
            $this->bookRepository->delete($book);

            return redirect()
                ->route('books.index')
                ->with('success', __('messages.book.moved_to_trash'));
        } catch (Exception $e) {
            Log::error('Book delete error: ' . $e->getMessage(), [
                'book_id' => $book->id,
            ]);

            return back()
                ->with('error', __('messages.book.delete_failed'));
        }
    }

    public function multipleDestroy(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'book_ids'   => 'required|array|min:1',
            'book_ids.*' => 'integer|exists:books,id,deleted_at,NULL',
            'return_author_id' => 'nullable|integer|exists:authors,id',
        ]);

        $bookIds  = $validated['book_ids'];
        $authorId = $validated['return_author_id'] ?? null;

        try {
            $deletedCount = $this->bookRepository->deleteMultiple($bookIds);

            $successMessage = __('messages.book.multiple_moved_to_trash_count', [
                'count' => $deletedCount,
            ]);

            if ($authorId) {
                return redirect()
                    ->route('authors.edit', ['author' => $authorId])
                    ->with('success', $successMessage);
            }

            return back()->with('success', $successMessage);
        } catch (Exception $e) {
            Log::error('Books multiple delete error: ' . $e->getMessage(), [
                'book_ids'  => $bookIds,
                'author_id' => $authorId,
            ]);

            $errorMessage = __('messages.book.delete_failed');

            if ($authorId) {
                return redirect()
                    ->route('authors.edit', ['author' => $authorId])
                    ->with('error', $errorMessage);
            }

            return back()->with('error', $errorMessage);
        }
    }

    /**
     * Получить авторов для формы (create/edit) с кэшированием
     */
    private function getAuthorsForForm(): array
    {
        return cache()->remember('form_authors', 3600, function () {
            return $this->bookRepository->getActiveAuthors()
                ->map(fn($author) => [
                    'id'         => $author->id,
                    'first_name' => $author->first_name,
                    'last_name'  => $author->last_name,
                    'full_name'  => "$author->last_name $author->first_name",
                ])
                ->toArray();
        });
    }

    private function getCitiesForForm(): array
    {
        return cache()->remember('form_cities', 3600, function () {
            return $this->bookRepository->getCities()
                ->map(fn($city) => [
                    'id'   => $city->id,
                    'name' => $city->name,
                ])
                ->toArray();
        });
    }

    /**
     * Получить компании для формы (create/edit) с кэшированием
     */
    private function getCompaniesForForm(): array
    {
        return cache()->remember('form_companies', 3600, function () {
            return $this->bookRepository->getCompanies()
                ->map(fn($company) => [
                    'id'   => $company->id,
                    'name' => $company->name,
                ])
                ->toArray();
        });
    }

    public function apiIndex(Request $request): JsonResponse
    {
        if (!config('features.api.books.enabled', false)) {
            return response()->json([
                'error' => __('messages.api.disabled'),
            ], 503);
        }

        try {
            $validated = $request->validate([
                'search'   => 'nullable|string|max:255',
                'per_page' => 'nullable|integer|min:1|max:100',
            ]);

            $search  = $validated['search'] ?? '';
            $perPage = (int) ($validated['per_page'] ?? 10);

            $books = $this->bookRepository->getForApi($search, $perPage);

            return response()->json([
                'data'  => $books->items(),
                'meta'  => [
                    'current_page' => $books->currentPage(),
                    'last_page'    => $books->lastPage(),
                    'per_page'     => $books->perPage(),
                    'total'        => $books->total(),
                ],
                'links' => [
                    'first' => $books->url(1),
                    'last'  => $books->url($books->lastPage()),
                    'prev'  => $books->previousPageUrl(),
                    'next'  => $books->nextPageUrl(),
                ],
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'error'   => __('messages.common.validation_error'),
                'message' => 'Validation failed',
                'errors'  => $e->errors(),
            ], 422);
        } catch (Exception $e) {
            Log::error('Book API index error: ' . $e->getMessage());

            return response()->json([
                'error' => config('app.debug')
                    ? $e->getMessage()
                    : __('messages.common.error'),
            ], 500);
        }
    }
}
