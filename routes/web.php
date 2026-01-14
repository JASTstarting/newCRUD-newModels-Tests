<?php

use App\Models\Author;
use App\Http\Controllers\AuthorController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\TrashController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\CityController;
use Illuminate\Support\Facades\Route;

// Ограничиваем параметры без RouteServiceProvider
Route::pattern('author', '[0-9]+');
Route::pattern('book', '[0-9]+');

// Биндим автора с учетом удалённых записей — чтобы можно было показать понятное сообщение
Route::bind('author', function ($value) {
    return Author::withTrashed()->findOrFail((int) $value);
});

Route::get('/', function () {
    return redirect()->route('authors.index');
});


Route::post('/books/multiple-destroy', [BookController::class, 'multipleDestroy'])->name('books.multiple-destroy');

/**
 * Группа авторов
 */
Route::prefix('authors')->name('authors.')->group(function () {
    Route::get('/', [AuthorController::class, 'index'])->name('index');
    Route::get('/create', [AuthorController::class, 'create'])->name('create');
    Route::post('/', [AuthorController::class, 'store'])->name('store');

    Route::get('/{author}/edit', [AuthorController::class, 'edit'])->name('edit');
    Route::put('/{author}', [AuthorController::class, 'update'])->name('update');
    Route::delete('/{author}', [AuthorController::class, 'destroy'])->name('destroy');
});

Route::get('/companies/create', [CompanyController::class, 'create'])->name('companies.create');
Route::post('/companies', [CompanyController::class, 'store'])->name('companies.store');

Route::get('/cities/create', [CityController::class, 'create'])->name('cities.create');
Route::post('/cities', [CityController::class, 'store'])->name('cities.store');

/**
 * Ресурсные маршруты книг.
 */
Route::resource('books', BookController::class)->only([
    'index', 'create', 'store', 'edit', 'update', 'destroy',
]);

/**
 * Корзина
 */
Route::get('/trash', [TrashController::class, 'index'])->name('trash.index');
Route::post('/trash/authors/{id}/restore', [TrashController::class, 'restoreAuthor'])
    ->whereNumber('id')->name('trash.authors.restore');
Route::delete('/trash/authors/{id}/force-delete', [TrashController::class, 'forceDeleteAuthor'])
    ->whereNumber('id')->name('trash.authors.force-delete');
Route::post('/trash/books/{id}/restore', [TrashController::class, 'restoreBook'])
    ->whereNumber('id')->name('trash.books.restore');
Route::delete('/trash/books/{id}/force-delete', [TrashController::class, 'forceDeleteBook'])
    ->whereNumber('id')->name('trash.books.force-delete');
