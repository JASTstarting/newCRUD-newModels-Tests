@extends('layouts.app')

@section('title', 'Создать издательство')

@section('content')
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12 col-lg-8 mx-auto">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-gradient-primary text-white py-3">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-building me-3 fs-4"></i>
                            <h1 class="h4 mb-0 fw-bold">Создать новое издательство</h1>
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

                        <form method="POST" action="{{ route('companies.store') }}" class="needs-validation" novalidate>
                            @csrf
                            <input type="hidden" name="return" value="{{ $returnUrl }}">

                            <div class="mb-3">
                                <label for="name" class="form-label fw-medium required">
                                    <i class="fas fa-heading me-2 text-muted"></i>Название издательства
                                </label>
                                <input type="text"
                                       id="name"
                                       name="name"
                                       class="form-control @error('name') is-invalid @enderror"
                                       value="{{ old('name') }}"
                                       required
                                       maxlength="255"
                                       placeholder="Например: Питер">
                                @error('name')
                                <div class="invalid-feedback d-block mt-1">
                                    <i class="fas fa-exclamation-triangle me-1"></i>{{ $message }}
                                </div>
                                @enderror
                            </div>

                            <div class="mb-4">
                                <label for="city_id" class="form-label fw-medium required">
                                    <i class="fas fa-city me-2 text-muted"></i>Город
                                </label>
                                <select id="city_id"
                                        name="city_id"
                                        class="form-select @error('city_id') is-invalid @enderror"
                                        required>
                                    <option value="" selected disabled>— Выберите город —</option>
                                    @foreach($cities as $city)
                                        <option value="{{ $city->getKey() }}" @selected(old('city_id') == $city->getKey())>
                                            {{ $city->getAttribute('name') }}
                                        </option>
                                    @endforeach
                                </select>
                                <div id="cityHelp" class="form-text text-muted small mt-1">
                                    Выберите город из списка или
                                    <a href="{{ route('cities.create', ['return' => url()->current()]) }}"
                                       class="text-primary text-decoration-none">
                                        создайте новый
                                    </a>
                                </div>
                                @error('city_id')
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
