<?php

use App\Models\Author;
use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/authors', function (Request $request) {
    $search = (string)$request->input('search', '');
    $q = Author::query()->with('books');

    if ($search) {
        $q->where(function($qq) use ($search) {
            $qq->where('first_name', 'like', "%$search%")
                ->orWhere('last_name', 'like', "%$search%")
                ->orWhere('father_name', 'like', "%$search%");
        });
    }

    return $q->paginate(10);
});

Route::get('/books', function (Request $request) {
    $search = (string)$request->input('search', '');
    $q = Book::query()->with(['author', 'company.city']);

    if ($search) {
        $q->where('name', 'like', "%$search%");
    }

    return $q->paginate(10);
});
