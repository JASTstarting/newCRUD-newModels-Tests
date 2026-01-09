<?php

namespace App\Http\Controllers;

use App\Http\Requests\AuthorStoreRequest;
use App\Http\Requests\AuthorUpdateRequest;
use App\Models\Author;
use App\Repository\AuthorRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuthorController extends Controller
{
    public function __construct(
        private readonly AuthorRepository $authorRepository
    ) {}

    public function index(Request $request): View
    {
        $search = $request->input('search', '');
        $authors = $this->authorRepository->getAllAuthors($search);

        return view('authors.index', compact('authors', 'search'));
    }

    public function edit(Author $author): View|RedirectResponse
    {
        if ($author->trashed()) {
            return redirect()->route('trash.index')
                ->with('error', 'Этот автор был удален. Восстановите его для редактирования.');
        }

        return view('authors.edit', compact('author'));
    }

    public function store(AuthorStoreRequest $request): RedirectResponse
    {
        $activeExists = Author::where('first_name', $request->first_name)
            ->where('last_name', $request->last_name)
            ->where('father_name', $request->father_name)
            ->where(function($q) use ($request) {
                if ($request->filled('birth_date')) {
                    $q->whereDate('birth_date', $request->birth_date);
                } else {
                    $q->whereNull('birth_date');
                }
            })
            ->exists();

        $trashedExists = Author::onlyTrashed()
            ->where('first_name', $request->first_name)
            ->where('last_name', $request->last_name)
            ->where('father_name', $request->father_name)
            ->where(function($q) use ($request) {
                if ($request->filled('birth_date')) {
                    $q->whereDate('birth_date', $request->birth_date);
                } else {
                    $q->whereNull('birth_date');
                }
            })
            ->exists();

        if ($activeExists) {
            return back()->withErrors([
                'first_name' => 'Автор с такими ФИО и датой рождения уже существует'
            ])->withInput();
        }
        elseif ($trashedExists) {
            return back()->withErrors([
                'first_name' => 'Автор с такими ФИО и датой рождения уже существует, но находится в корзине. Сначала восстановите или удалите его.'
            ])->withInput();
        }

        $this->authorRepository->store($request->validated());
        return redirect()->route('authors.index')
            ->with('success', 'Автор успешно создан');
    }

    public function create(): View
    {
        return view('authors.create');
    }

    public function update(AuthorUpdateRequest $request, Author $author): RedirectResponse
    {
        $this->authorRepository->update($author, $request); // Передаем Form Request
        return redirect()->route('authors.edit', $author)
            ->with('success', 'Данные автора успешно обновлены');
    }

    public function destroy(Author $author): RedirectResponse
    {
        $author->books()->delete();

        $this->authorRepository->delete($author);

        return back()->with('success', 'Автор и все его книги перемещены в корзину');
    }

    public function ajaxIndex(Request $request): JsonResponse
    {
        $search = (string)$request->input('search', '');

        $q = Author::query()->with('books');

        if ($search) {
            $q->where(function ($qq) use ($search) {
                $qq->where('first_name', 'like', "%$search%")
                    ->orWhere('last_name', 'like', "%$search%")
                    ->orWhere('father_name', 'like', "%$search%");
            });
        }

        return response()->json($q->paginate(10));
    }
}
