<?php

namespace App\Http\Controllers;

use App\Http\Requests\BookStoreRequest;
use App\Http\Requests\BookUpdateRequest;
use App\Models\Author;
use App\Models\Book;
use App\Models\Company;
use App\Repository\BookRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Exception;
use Illuminate\View\View;

class BookController extends Controller
{
    public function __construct(
        private readonly BookRepository $bookRepository
    ) {}

    public function index(Request $request): View
    {
        $search = $request->input('search', '');
        $books  = $this->bookRepository->paginateWithFilters($search);

        return view('books.index', compact('books', 'search'));
    }

    public function create(): View
    {
        $authors = Author::query()->where('active', true)->orderBy('last_name')->get(['id','first_name','last_name']);
        $companies = Company::query()->orderBy('name')->get(['id','name']);
        return view('books.create', compact('authors', 'companies'));
    }

    public function store(BookStoreRequest $request): RedirectResponse
    {
        $name = $request->input('name');
        $authorId = $request->input('author_id');
        $createdDate = $request->input('created_date');

        try {
            $year = Carbon::parse($createdDate)->year;

            $activeExists = Book::query()->where('name', $name)
                ->where('author_id', $authorId)
                ->whereYear('created_date', $year)
                ->exists();

            $trashedExists = Book::onlyTrashed()
                ->where('name', $name)
                ->where('author_id', $authorId)
                ->whereYear('created_date', $year)
                ->exists();

            if ($activeExists) {
                return back()->withErrors([
                    'name' => 'Книга с таким названием, автором и годом выпуска уже существует'
                ])->withInput();
            }
            elseif ($trashedExists) {
                return back()->withErrors([
                    'name' => 'Книга с таким названием, автором и годом выпуска уже существует, но находится в корзине. Сначала восстановите или удалите её.'
                ])->withInput();
            }
        } catch (Exception) {
        }

        $this->bookRepository->store($request->validated());
        return redirect()->route('books.index')
            ->with('success', 'Книга успешно создана');
    }

    public function edit(Book $book): View
    {
        $authors = Author::query()->where('active', true)->orderBy('last_name')->get(['id','first_name','last_name']);
        $companies = Company::query()->orderBy('name')->get(['id','name']);
        $book->load(['author', 'company.city']);

        return view('books.edit', compact('book', 'authors', 'companies'));
    }

    public function update(BookUpdateRequest $request, Book $book): RedirectResponse
    {
        $this->bookRepository->update($book, $request);

        return redirect()->route('books.index')->with('success', 'Книга успешно обновлена');
    }

    public function destroy(Book $book): RedirectResponse
    {
        $this->bookRepository->delete($book);
        return back()->with('success', 'Книга перемещена в корзину');
    }

    public function multipleDestroy(Request $request): RedirectResponse
    {
        $ids = (array)$request->input('book_ids', []);
        if (empty($ids)) {
            return back()->with('error', 'Не выбрано ни одной книги');
        }

        $books = Book::query()->whereIn('id', $ids)->get();
        foreach ($books as $book) {
            $book->delete();
        }

        return back()->with('success', 'Выбранные книги перемещены в корзину');
    }

    public function ajaxIndex(Request $request): JsonResponse
    {
        $search = (string)$request->input('search', '');

        $q = Book::query()
            ->with(['author:id,first_name,last_name', 'company:id,name,city_id', 'company.city:id,name']);

        if ($search) {
            $q->where('name', 'like', "%$search%");
        }

        return response()->json($q->paginate(10));
    }
}
