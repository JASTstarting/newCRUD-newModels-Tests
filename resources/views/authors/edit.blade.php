@php use App\Models\Book;use Illuminate\Support\Collection; @endphp
@extends('layouts.app')

@section('title', 'Редактировать автора')

@section('content')
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-gradient-warning text-dark py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-user-edit me-3 fs-4"></i>
                                <h1 class="h4 mb-0 fw-bold">
                                    Редактировать автора #{{ $author->id }}
                                </h1>
                            </div>
                            <div class="btn-group">
                                <a href="{{ route('authors.index') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-arrow-left me-1"></i>Назад к списку
                                </a>

                                <form action="{{ route('authors.destroy', $author) }}"
                                      method="POST"
                                      class="d-inline"
                                      onsubmit="return confirm('Переместить в корзину автора и ВСЕ его книги?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger">
                                        <i class="fas fa-user-slash me-1"></i>Удалить автора и книги
                                    </button>
                                </form>
                            </div>
                        </div>
                        @if($author->trashed())
                            <div class="mt-2">
                                <span class="badge bg-warning text-dark">
                                    <i class="fas fa-trash me-1"></i>Автор в корзине
                                </span>
                            </div>
                        @endif
                    </div>

                    <div class="card-body">
                        @if($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <strong>Пожалуйста, исправьте следующие ошибки:</strong>
                                <ul class="mb-0 mt-2">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Закрыть"></button>
                            </div>
                        @endif

                        <form id="authorForm"
                              action="{{ route('authors.update', $author) }}"
                              method="POST"
                              class="needs-validation"
                              novalidate>
                            @csrf
                            @method('PUT')

                            @include('authors._form')

                        </form>
                    </div>
                </div>

                @if(isset($author))
                    <div class="card border-0 shadow-sm mt-4">
                        <div class="card-header bg-light py-3">
                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                                <h5 class="mb-0 fw-bold">
                                    <i class="fas fa-book me-2 text-primary"></i>Книги автора
                                </h5>
                                @if($author->books()->count() > 0)
                                    <span class="badge bg-info text-dark px-3 py-2">
                                        Всего: {{ $author->books()->count() }}
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="card-body">
                            @php
                                /** @var Collection<Book> $authorBooks */
                                $authorBooks = $author->books()->with('company.city')->get();
                            @endphp

                            @if($authorBooks->isEmpty())
                                <div class="text-center py-5">
                                    <i class="fas fa-book-open text-muted fs-1 mb-3"></i>
                                    <h4 class="text-muted mb-2">У автора пока нет книг</h4>
                                    <a href="{{ route('books.create') }}" class="btn btn-primary">
                                        <i class="fas fa-plus me-2"></i>Добавить книгу
                                    </a>
                                </div>
                            @else
                                <div class="mb-4">
                                    <form id="bulkDeleteForm"
                                          action="{{ route('books.multiple-destroy') }}"
                                          method="POST"
                                          onsubmit="return confirm('Удалить выбранные книги в корзину?')">
                                        @csrf
                                        <input type="hidden" name="return_author_id" value="{{ $author->getKey() }}">

                                        <button type="submit" class="btn btn-outline-danger">
                                            <i class="fas fa-trash me-1"></i>Удалить выбранные книги
                                        </button>
                                    </form>
                                </div>

                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead class="table-light">
                                        <tr>
                                            <th scope="col" class="px-3 py-2 fw-bold" style="width: 50px;"></th>
                                            <th scope="col" class="px-3 py-2 fw-bold">Название книги</th>
                                            <th scope="col" class="px-3 py-2 fw-bold">Издательство</th>
                                            <th scope="col" class="px-3 py-2 fw-bold d-none d-md-table-cell">Город</th>
                                            <th scope="col" class="px-3 py-2 fw-bold">Дата создания</th>
                                            <th scope="col" class="px-3 py-2 fw-bold text-end">Действия</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach($authorBooks as $book)
                                            <tr class="book-row">
                                                <td class="px-3 py-2">
                                                    <input type="checkbox"
                                                           name="book_ids[]"
                                                           value="{{ $book->getKey() }}"
                                                           class="form-check-input book-checkbox">
                                                </td>
                                                <td class="px-3 py-2 fw-medium">
                                                    {{ $book->title }}
                                                </td>
                                                <td class="px-3 py-2">
                                                    {{ $book->company?->name ?? '—' }}
                                                </td>
                                                <td class="px-3 py-2 d-none d-md-table-cell">
                                                    {{ $book->company?->city?->name ?? '—' }}
                                                </td>
                                                <td class="px-3 py-2">
                                                    @if($book->created_date)
                                                        <span class="badge bg-light text-dark px-2 py-1">
                                                                {{ $book->created_date->format('d.m.Y') }}
                                                            </span>
                                                    @else
                                                        <span class="text-muted">—</span>
                                                    @endif
                                                </td>
                                                <td class="px-3 py-2 text-end">
                                                    <div class="d-flex gap-2 justify-content-end">
                                                        <a href="{{ route('books.edit', $book) }}"
                                                           class="btn btn-sm btn-outline-primary"
                                                           title="Редактировать книгу"
                                                           aria-label="Редактировать книгу {{ $book->title }}">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <form action="{{ route('books.destroy', $book) }}"
                                                              method="POST"
                                                              class="d-inline"
                                                              onsubmit="return confirm('Удалить эту книгу в корзину?')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit"
                                                                    class="btn btn-sm btn-outline-danger"
                                                                    title="Удалить книгу"
                                                                    aria-label="Удалить книгу {{ $book->title }}">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>

                                <div class="mt-3 text-muted small">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Вы можете выбрать несколько книг и удалить их одновременно
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            'use strict';

            // Bootstrap HTML5 validation
            const forms = document.querySelectorAll('.needs-validation');
            forms.forEach(function(form) {
                form.addEventListener('submit', function(event) {
                    if (!form.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    form.classList.add('was-validated');
                }, false);
            });

            // Тёмная тема
            if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
                document.documentElement.setAttribute('data-bs-theme', 'dark');
            }

            // Обработчик отправки формы массового удаления
            const bulkDeleteForm = document.getElementById('bulkDeleteForm');

            if (bulkDeleteForm) {
                bulkDeleteForm.addEventListener('submit', function(e) {
                    e.preventDefault();

                    const checkboxes = document.querySelectorAll('.book-checkbox:checked');
                    const selectedCount = checkboxes.length;

                    if (selectedCount === 0) {
                        alert('Пожалуйста, выберите хотя бы одну книгу для удаления');
                        return;
                    }

                    if (!confirm(`Вы уверены, что хотите удалить ${selectedCount} выбранных книг?`)) {
                        return;
                    }

                    // Удаляем старые скрытые инпуты
                    const existingInputs = this.querySelectorAll('input[name="book_ids[]"]');
                    existingInputs.forEach(input => input.remove());

                    // Добавляем новые скрытые инпуты для выбранных книг
                    checkboxes.forEach(function(checkbox) {
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = 'book_ids[]';
                        input.value = checkbox.value;
                        bulkDeleteForm.appendChild(input);
                    });

                    // Отправляем форму
                    this.submit();
                });
            }

            // Прокрутка к первой ошибке при отправке формы
            const authorForm = document.getElementById('authorForm');
            if (authorForm) {
                authorForm.addEventListener('submit', function() {
                    if (!this.checkValidity()) {
                        const invalidElements = this.querySelectorAll(':invalid');
                        if (invalidElements.length > 0) {
                            invalidElements[0].scrollIntoView({
                                behavior: 'smooth',
                                block: 'center'
                            });
                            invalidElements[0].focus();
                        }
                    }
                });
            }
        });
    </script>
@endpush
