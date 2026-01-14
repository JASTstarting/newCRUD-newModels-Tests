@extends('layouts.app')

@section('title', isset($author) ? 'Редактировать автора' : 'Создать автора')

@section('content')
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-gradient-primary text-white py-3">
                        <div class="d-flex align-items-center">
                            <i class="fas {{ isset($author) ? 'fa-user-edit' : 'fa-plus-circle' }} me-3 fs-4"></i>
                            <h1 class="h4 mb-0 fw-bold">
                                {{ isset($author) ? 'Редактировать автора' : 'Создать нового автора' }}
                            </h1>
                        </div>
                        @if(isset($author) && $author->trashed())
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

                        {{-- ОСНОВНАЯ ФОРМА (без дубля кнопок внизу) --}}
                        <form id="authorForm"
                              action="{{ isset($author) ? route('authors.update', $author) : route('authors.store') }}"
                              method="POST"
                              class="needs-validation"
                              novalidate>
                            @csrf
                            @if(isset($author))
                                @method('PUT')
                            @endif

                            @include('authors._form')

                            {{-- РАНЬШЕ ЗДЕСЬ БЫЛ ДУБЛЬ КНОПОК "СОЗДАТЬ/ОТМЕНА" — УДАЛЕНО --}}
                        </form>

                        {{-- КНОПКА УДАЛИТЬ — вынесена из основной формы, чтобы не было вложенных форм --}}
                        @if(isset($author) && !$author->trashed())
                            <div class="row mt-4">
                                <div class="col-md-8 offset-md-4">
                                    <form action="{{ route('authors.destroy', $author) }}"
                                          method="POST"
                                          onsubmit="return confirm('Переместить автора в корзину?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger px-4">
                                            <i class="fas fa-trash me-2"></i>Удалить
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endif

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
                        }
                    }

                    form.classList.add('was-validated');
                }, false);
            });

            // Тёмная тема (опционально)
            if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
                document.documentElement.setAttribute('data-bs-theme', 'dark');
            }

            // Счётчик символов для биографии — ИСПРАВЛЕНО (без this.value)
            const bioTextarea = document.getElementById('biography');
            const bioCounter = document.getElementById('biography_counter');

            if (bioTextarea instanceof HTMLTextAreaElement && bioCounter) {
                const updateBioCounter = () => {
                    bioCounter.textContent = `${bioTextarea.value.length}/5000`;
                };
                bioTextarea.addEventListener('input', updateBioCounter);
                updateBioCounter();
            }

            // Клиентская валидация обязательных полей — ИСПРАВЛЕНО (instanceof для .value)
            const authorForm = document.getElementById('authorForm');
            if (authorForm) {
                authorForm.addEventListener('submit', function(event) {
                    const requiredFields = ['last_name', 'first_name', 'father_name'];
                    let hasError = false;

                    requiredFields.forEach(function(fieldId) {
                        const field = document.getElementById(fieldId);

                        if (field instanceof HTMLInputElement || field instanceof HTMLTextAreaElement) {
                            const val = field.value.trim();
                            if (val.length < 2) {
                                hasError = true;
                                field.classList.add('is-invalid');

                                // Сообщение об ошибке (создаём, если нет)
                                let feedback = field.parentElement?.querySelector('.invalid-feedback');
                                if (!feedback) {
                                    feedback = document.createElement('div');
                                    feedback.className = 'invalid-feedback';
                                    feedback.textContent = 'Минимум 2 символа';
                                    field.parentElement?.appendChild(feedback);
                                }
                            } else {
                                field.classList.remove('is-invalid');
                            }
                        }
                    });

                    if (hasError) {
                        event.preventDefault();
                        event.stopPropagation();

                        // Прокрутка к первой ошибке
                        const firstInvalid = authorForm.querySelector('.is-invalid');
                        if (firstInvalid instanceof HTMLElement) {
                            firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
                            firstInvalid.focus();
                        }
                    }
                });
            }
        });
    </script>
@endsection
