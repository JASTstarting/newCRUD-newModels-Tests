<?php

namespace App\Http\Controllers;

use App\Http\Requests\AuthorStoreRequest;
use App\Http\Requests\AuthorUpdateRequest;
use App\Models\Author;
use App\Repository\AuthorRepository;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthorController extends Controller
{
    public function __construct(
        private readonly AuthorRepository $authorRepository
    ) {}

    public function index(Request $request): View
    {
        $validated = $request->validate([
            'search'    => 'nullable|string|max:255',
            'gender'    => 'nullable|in:0,1',
            'active'    => 'nullable|in:0,1',
            'sort'      => 'nullable|in:last_name,first_name,father_name,birth_date,gender,active',
            'direction' => 'nullable|in:asc,desc',
            'per_page'  => 'nullable|integer|min:1|max:100',
        ]);

        $perPage = (int)($validated['per_page'] ?? 10);

        $authors = $this->authorRepository->filterPaginate($validated, $perPage);

        $search = (string)($validated['search'] ?? '');

        return view('authors.index', compact('authors', 'search'));
    }

    public function create(): View
    {
        return view('authors.create');
    }

    public function store(AuthorStoreRequest $request): RedirectResponse
    {
        try {
            $this->authorRepository->store($request->validated());

            return redirect()
                ->route('authors.index')
                ->with('success', __('messages.author.created'));
        } catch (Exception $e) {
            Log::error('Author store error: ' . $e->getMessage());

            return back()
                ->withInput()
                ->with('error', __('messages.author.create_failed'));
        }
    }

    public function edit(Author $author): View|RedirectResponse
    {
        if ($author->trashed()) {
            return redirect()->route('trash.index')
                ->with('error', __('messages.author.already_deleted'));
        }

        $author->load(['books' => function ($q) {
            $q->select('id', 'title', 'author_id')->orderBy('title');
        }]);

        return view('authors.edit', compact('author'));
    }

    public function update(AuthorUpdateRequest $request, Author $author): RedirectResponse
    {
        if ($author->trashed()) {
            return redirect()->route('trash.index')
                ->with('error', __('messages.author.already_deleted'));
        }

        try {
            $this->authorRepository->update($author, $request->validated());

            return redirect()
                ->route('authors.index')
                ->with('success', __('messages.author.updated'));
        } catch (Exception $e) {
            Log::error('Author update error: ' . $e->getMessage(), [
                'author_id' => $author->id,
                'data'      => $request->validated(),
            ]);

            return back()
                ->withInput()
                ->with('error', __('messages.author.update_failed'));
        }
    }

    public function destroy(Author $author): RedirectResponse
    {
        if ($author->trashed()) {
            return redirect()->route('trash.index')
                ->with('error', __('messages.author.already_deleted'));
        }

        try {
            $this->authorRepository->delete($author);

            return redirect()->route('authors.index')
                ->with('success', __('messages.author.moved_to_trash'));
        } catch (Exception $e) {
            Log::error('Author delete error: ' . $e->getMessage(), [
                'author_id' => $author->id,
            ]);

            return back()->with('error', __('messages.author.delete_failed'));
        }
    }

    public function apiIndex(Request $request): JsonResponse
    {
        if (!config('features.api.authors.enabled', false)) {
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

            $authors = $this->authorRepository->getAuthorsForApi($search, $perPage);

            return response()->json([
                'data'  => $authors->items(),
                'meta'  => [
                    'current_page' => $authors->currentPage(),
                    'last_page'    => $authors->lastPage(),
                    'per_page'     => $authors->perPage(),
                    'total'        => $authors->total(),
                ],
                'links' => [
                    'first' => $authors->url(1),
                    'last'  => $authors->url($authors->lastPage()),
                    'prev'  => $authors->previousPageUrl(),
                    'next'  => $authors->nextPageUrl(),
                ],
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'error'   => __('messages.common.validation_error'),
                'message' => 'Validation failed',
                'errors'  => $e->errors(),
            ], 422);
        } catch (Exception $e) {
            Log::error('Author API index error: ' . $e->getMessage());

            return response()->json([
                'error' => config('app.debug') ? $e->getMessage() : __('messages.common.error'),
            ], 500);
        }
    }
}
