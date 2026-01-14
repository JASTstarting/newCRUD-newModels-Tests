<?php

use App\Http\Controllers\AuthorController;
use App\Http\Controllers\BookController;
use Illuminate\Support\Facades\Route;


Route::get('/authors', [AuthorController::class, 'apiIndex'])->name('authors.api');
Route::get('/books', [BookController::class, 'apiIndex'])->name('books.api');
