@extends('layouts.app')

@section('title', 'Создать книгу')

@section('content')
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-gradient-primary text-white py-3">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-plus-circle me-3 fs-4"></i>
                            <h1 class="h4 mb-0 fw-bold">Создать новую книгу</h1>
                        </div>
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
                              action="{{ route('books.store') }}"
                              method="POST"
                              class="needs-validation"
                              novalidate>
                            @csrf

                            @include('books._form', [
                                'authors' => $authors,
                                'companies' => $companies,
                                'mode' => 'create'
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

                        // Прокрутка к первой ошибке
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
