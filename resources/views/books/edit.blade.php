@extends('layouts.app')

@section('title', 'Редактировать книгу')

@section('content')
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-gradient-warning text-dark py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-edit me-3 fs-4"></i>
                                <h1 class="h4 mb-0 fw-bold">
                                    Редактировать книгу #{{ $book->getKey() }}
                                </h1>
                            </div>
                            <div class="btn-group">
                                <a href="{{ route('books.index') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-arrow-left me-1"></i>Назад к списку
                                </a>

                                {{-- Кнопка удаления вынесена отдельной формой, чтобы избежать вложенных форм --}}
                                <form action="{{ route('books.destroy', $book) }}"
                                      method="POST"
                                      class="d-inline"
                                      onsubmit="return confirm('Переместить книгу в корзину?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger">
                                        <i class="fas fa-book-slash me-1"></i>Удалить книгу
                                    </button>
                                </form>
                            </div>
                        </div>

                        {{-- Если используете SoftDeletes, можно отобразить бейдж: --}}
                        {{-- @if(method_exists($book, 'trashed') && $book->trashed()) --}}
                        {{--     <div class="mt-2"> --}}
                        {{--         <span class="badge bg-warning text-dark"> --}}
                        {{--             <i class="fas fa-trash me-1"></i>Книга в корзине --}}
                        {{--         </span> --}}
                        {{--     </div> --}}
                        {{-- @endif --}}
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

                        <form id="bookForm"
                              action="{{ route('books.update', ['book' => $book->getKey()]) }}"
                              method="POST"
                              class="needs-validation"
                              novalidate>
                            @csrf
                            @method('PUT')

                            @include('books._form', [
                                'book' => $book,
                                'authors' => $authors,
                                'companies' => $companies,
                                'mode' => 'edit'
                            ])
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
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

                        const invalidElements = form.querySelectorAll(':invalid');
                        if (invalidElements.length > 0) {
                            invalidElements[0].scrollIntoView({
                                behavior: 'smooth',
                                block: 'center'
                            });
                            invalidElements[0].focus();
                        }
                    }
                    form.classList.add('was-validated');
                }, false);
            });

            // Тёмная тема (опционально)
            if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
                document.documentElement.setAttribute('data-bs-theme', 'dark');
            }
        });
    </script>
@endsection
