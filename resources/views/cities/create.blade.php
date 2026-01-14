@extends('layouts.app')

@section('title', 'Создать город')

@section('content')
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12 col-lg-6 mx-auto">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-gradient-primary text-white py-3">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-city me-3 fs-4"></i>
                            <h1 class="h4 mb-0 fw-bold">Создать новый город</h1>
                        </div>
                    </div>

                    <div class="card-body">
                        @if(session('error'))
                            <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                                <i class="fas fa-exclamation-triangle me-2"></i>{{ session('error') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Закрыть"></button>
                            </div>
                        @endif

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

                        <form method="POST" action="{{ route('cities.store') }}" class="needs-validation" novalidate>
                            @csrf
                            <input type="hidden" name="return" value="{{ $returnUrl }}">

                            <div class="mb-3">
                                <label for="name" class="form-label fw-medium required">
                                    <i class="fas fa-heading me-2 text-muted"></i>Название города
                                </label>
                                <input type="text"
                                       id="name"
                                       name="name"
                                       class="form-control @error('name') is-invalid @enderror"
                                       value="{{ old('name') }}"
                                       required
                                       maxlength="255"
                                       placeholder="Например: Санкт-Петербург">
                                @error('name')
                                <div class="invalid-feedback d-block mt-1">
                                    <i class="fas fa-exclamation-triangle me-1"></i>{{ $message }}
                                </div>
                                @enderror
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i>Создать
                                </button>
                                <a href="{{ $returnUrl }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-arrow-left me-1"></i>Отмена
                                </a>
                            </div>
                        </form>
                    </div>
                </div>

                <small class="text-muted d-block mt-3">
                    <i class="fas fa-info-circle me-1"></i>
                    После создания вы вернётесь к предыдущей форме.
                </small>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const forms = document.querySelectorAll('.needs-validation');
            forms.forEach(function (form) {
                form.addEventListener('submit', function (event) {
                    if (!form.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    form.classList.add('was-validated');
                }, false);
            });
        });
    </script>

    <style>
        .required::after {
            content: " *";
            color: #dc3545;
            font-weight: bold;
        }
    </style>
@endsection
