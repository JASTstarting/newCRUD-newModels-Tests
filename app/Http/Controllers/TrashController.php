<?php

namespace App\Http\Controllers;

use App\Models\Author;
use App\Models\Book;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class TrashController extends Controller
{
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'authors_search' => 'nullable|string|max:255',
            'books_search'   => 'nullable|string|max:255',
            'include_books'  => 'nullable|boolean',
        ]);

        $searchAuthors = (string) ($validated['authors_search'] ?? '');
        $searchBooks   = (string) ($validated['books_search'] ?? '');
        $includeBooks  = (bool) ($validated['include_books'] ?? true);

        if ($searchAuthors !== '') {
            Log::info('Trash: search authors', [
                'term'    => $searchAuthors,
                'user_id' => auth()->id(),
            ]);
        }
        if ($searchBooks !== '') {
            Log::info('Trash: search books', [
                'term'    => $searchBooks,
                'user_id' => auth()->id(),
            ]);
        }
        if (!$includeBooks) {
            Log::debug('Trash: listing authors without loading books (withCount mode)', [
                'user_id' => auth()->id(),
            ]);
        }

        $authorsQuery = Author::onlyTrashed()->orderByDesc('deleted_at');

        if ($searchAuthors !== '') {
            $term = trim(str_replace(['%', '_'], ['\%', '\_'], $searchAuthors));
            if (mb_strlen($term) >= 2) {
                $authorsQuery->where(function ($qq) use ($term) {
                    $qq->where('first_name', 'like', "%$term%")
                        ->orWhere('last_name', 'like', "%$term%")
                        ->orWhere('father_name', 'like', "%$term%");
                });
            }
        }

        if ($includeBooks) {
            $authorsQuery->with([
                'books' => function ($q) {
                    $q->onlyTrashed()->with([
                        'company.city',
                        'author' => function ($aq) {
                            $aq->withTrashed()->select('id', 'first_name', 'last_name', 'deleted_at');
                        },
                    ])->select('id', 'title', 'author_id', 'company_id', 'deleted_at');
                },
            ]);
        } else {
            $authorsQuery->withCount([
                'books as trashed_books_count' => function ($q) {
                    $q->onlyTrashed();
                },
            ]);
        }

        $trashedAuthors = $authorsQuery
            ->paginate(5, ['*'], 'authors_page')
            ->withQueryString();

        $booksQuery = Book::onlyTrashed()
            ->whereHas('author', fn($q) => $q->withoutTrashed())
            ->with([
                'author:id,first_name,last_name',
                'company:id,name,city_id',
                'company.city:id,name',
            ])
            ->orderByDesc('deleted_at');

        if ($searchBooks !== '') {
            $term = trim(str_replace(['%', '_'], ['\%', '\_'], $searchBooks));
            if (mb_strlen($term) >= 2) {
                $booksQuery->where('title', 'like', "%$term%");
            }
        }

        $trashedBooks = $booksQuery
            ->paginate(10, ['*'], 'books_page')
            ->withQueryString();

        Log::debug('Trash: page loaded', [
            'user_id'         => auth()->id(),
            'authors_total'   => $trashedAuthors->total(),
            'authors_page'    => $trashedAuthors->currentPage(),
            'books_total'     => $trashedBooks->total(),
            'books_page'      => $trashedBooks->currentPage(),
            'include_books'   => $includeBooks,
        ]);

        return view('trash.index', compact('trashedAuthors', 'trashedBooks', 'searchAuthors', 'searchBooks', 'includeBooks'));
    }

    public function restoreAuthor(int $id): RedirectResponse
    {
        $author = Author::onlyTrashed()->findOrFail($id);

        $author->restore();

        $restoredBooks = $author->books()->onlyTrashed()->restore();

        Log::info('Trash: author restored with all books', [
            'author_id'      => $author->id,
            'restored_books' => $restoredBooks,
            'user_id'        => auth()->id(),
        ]);

        return back()->with('success', __('messages.author.restored'));
    }

    public function forceDeleteAuthor(int $id): RedirectResponse
    {
        $author = Author::withTrashed()->findOrFail($id);

        $activeBooksCount = $author->books()->withoutTrashed()->count();
        if ($activeBooksCount > 0) {
            Log::warning('Trash: force delete author blocked - has active books', [
                'author_id'          => $author->id,
                'active_books_count' => $activeBooksCount,
                'user_id'            => auth()->id(),
            ]);

            return back()->with('error', __('messages.author.force_delete_blocked'));
        }

        $author->forceDelete();

        Log::info('Trash: author permanently deleted', [
            'author_id' => $id,
            'user_id'   => auth()->id(),
        ]);

        return back()->with('success', __('messages.author.force_deleted'));
    }

    public function restoreBook(int $id): RedirectResponse
    {
        $book = Book::withTrashed()->findOrFail($id);

        $authorId = (int) $book->getAttribute('author_id');
        $author   = Author::withTrashed()->find($authorId);

        if (!$author) {
            Log::warning('Trash: cannot restore book - related author not found', [
                'book_id'   => $book->getKey(),
                'author_id' => $authorId,
                'user_id'   => auth()->id(),
            ]);

            return back()->with('error', __('messages.book.author_not_found'));
        }

        if ($author->trashed()) {
            $author->restore();

            Log::info('Trash: author restored for single book restore', [
                'author_id' => $author->id,
                'book_id'   => $book->getKey(),
                'user_id'   => auth()->id(),
            ]);
        }

        $book->restore();

        Log::info('Trash: book restored', [
            'book_id'   => $book->getKey(),
            'author_id' => $author->id,
            'user_id'   => auth()->id(),
        ]);

        return back()
            ->with('info', __('messages.book.author_restored_with_book'))
            ->with('success', __('messages.book.restored'));
    }

    public function forceDeleteBook(int $id): RedirectResponse
    {
        $book = Book::withTrashed()->findOrFail($id);
        $book->forceDelete();

        Log::info('Trash: book permanently deleted', [
            'book_id' => $id,
            'user_id' => auth()->id(),
        ]);

        return back()->with('success', __('messages.book.force_deleted'));
    }
}
