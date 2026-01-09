<?php

namespace App\Http\Controllers;

use App\Models\Author;
use App\Models\Book;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TrashController extends Controller
{
    public function index(Request $request): View
    {
        $searchAuthors = (string)$request->input('authors_search', '');
        $searchBooks   = (string)$request->input('books_search', '');

        $trashedAuthors = Author::onlyTrashed()
            ->when($searchAuthors, function ($q) use ($searchAuthors) {
                $q->where(function ($qq) use ($searchAuthors) {
                    $qq->where('first_name', 'like', "%$searchAuthors%")
                        ->orWhere('last_name', 'like', "%$searchAuthors%")
                        ->orWhere('father_name', 'like', "%$searchAuthors%");
                });
            })
            ->with(['books' => function ($q) {
                $q->onlyTrashed()->with(['company.city', 'author']);
            }])
            ->orderByDesc('deleted_at')
            ->paginate(5, ['*'], 'authors_page')
            ->appends(['authors_search' => $searchAuthors]);

        $trashedBooks = Book::onlyTrashed()
            ->whereHas('author', function ($q) {
                $q->whereNull('deleted_at');
            })
            ->when($searchBooks, function ($q) use ($searchBooks) {
                $q->where('name', 'like', "%$searchBooks%");
            })
            ->with(['author', 'company.city'])
            ->orderByDesc('deleted_at')
            ->paginate(10, ['*'], 'books_page')
            ->appends(['books_search' => $searchBooks]);

        return view('trash.index', compact('trashedAuthors', 'trashedBooks', 'searchAuthors', 'searchBooks'));
    }

    public function restoreAuthor(Request $request, int $id): RedirectResponse
    {
        $author = Author::withTrashed()->findOrFail($id);
        $author->restore();

        return back()->with('success', 'Автор восстановлен. Книги можно восстановить по отдельности ниже.');
    }

    public function forceDeleteAuthor(int $id): RedirectResponse
    {
        $author = Author::withTrashed()->findOrFail($id);

        $activeBooksCount = $author->books()->whereNull('deleted_at')->count();

        if ($activeBooksCount > 0) {
            return back()->with('error', 'Нельзя удалить автора. У него есть восстановленные книги.');
        }

        $author->books()->onlyTrashed()->forceDelete();

        $author->forceDelete();
        return back()->with('success', 'Автор и все его удалённые книги удалены навсегда');
    }

    public function restoreBook(int $id): RedirectResponse
    {
        $book = Book::withTrashed()->findOrFail($id);

        $authorId = $book->getAttribute('author_id');

        if ($authorId && is_numeric($authorId)) {

            $author = Author::withTrashed()->find((int)$authorId);

            if ($author instanceof Author) {
                if (method_exists($author, 'trashed') && $author->trashed()) {
                    $author->restore();
                    session()->flash('info', 'Автор автоматически восстановлен вместе с книгой');
                }
            }
        }

        $book->restore();
        return back()->with('success', 'Книга восстановлена');
    }

    public function forceDeleteBook(int $id): RedirectResponse
    {
        $book = Book::withTrashed()->findOrFail($id);
        $book->forceDelete();

        return back()->with('success', 'Книга удалена навсегда');
    }
}
