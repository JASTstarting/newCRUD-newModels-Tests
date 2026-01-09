<?php

use App\Http\Controllers\AuthorController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\TrashController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('authors.index');
});

Route::prefix('authors')->name('authors.')->group(function () {
    Route::get('/', [AuthorController::class, 'index'])->name('index');
    Route::get('/create', [AuthorController::class, 'create'])->name('create');
    Route::post('/', [AuthorController::class, 'store'])->name('store');
    Route::get('/{author}/edit', [AuthorController::class, 'edit'])->name('edit');
    Route::put('/{author}', [AuthorController::class, 'update'])->name('update');
    Route::delete('/{author}', [AuthorController::class, 'destroy'])->name('destroy');
});

Route::resource('books', BookController::class)->only([
    'index', 'create', 'store', 'edit', 'update', 'destroy'
]);

Route::post('/books/multiple-destroy', [BookController::class, 'multipleDestroy'])->name('books.multiple-destroy');

Route::get('/trash', [TrashController::class, 'index'])->name('trash.index');
Route::post('/trash/authors/{id}/restore', [TrashController::class, 'restoreAuthor'])->name('trash.authors.restore');
Route::delete('/trash/authors/{id}/force-delete', [TrashController::class, 'forceDeleteAuthor'])->name('trash.authors.force-delete');
Route::post('/trash/books/{id}/restore', [TrashController::class, 'restoreBook'])->name('trash.books.restore');
Route::delete('/trash/books/{id}/force-delete', [TrashController::class, 'forceDeleteBook'])->name('trash.books.force-delete');

Route::get('/books/ajax/list', [BookController::class, 'ajaxIndex'])->name('books.ajax');
Route::get('/authors/ajax/list', [AuthorController::class, 'ajaxIndex'])->name('authors.ajax');
