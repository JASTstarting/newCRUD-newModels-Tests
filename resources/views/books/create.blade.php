@extends('layouts.app')

@section('title', 'Создать книгу')

@section('content')
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h2 class="mb-0"><i class="fas fa-plus me-2"></i>Создать новую книгу</h2>
        </div>
        <div class="card-body">
            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach($errors->all() as $e)
                            <li>{{ $e }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('books.store') }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label class="form-label required">Название</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                </div>

                <div class="mb-3">
                    <label class="form-label required">Описание</label>
                    <textarea name="description" class="form-control" rows="4" required>{{ old('description') }}</textarea>
                </div>

                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label required">Дата создания</label>
                        <input type="date" name="created_date" class="form-control" value="{{ old('created_date') }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label required">Автор</label>
                        <select name="author_id" class="form-select" required>
                            <option value="">— Выберите автора —</option>
                            @foreach($authors as $author)
                                <option value="{{ $author->getKey() }}"
                                    @selected(old('author_id') == $author->getKey())>
                                    {{ $author->getAttribute('last_name') }} {{ $author->getAttribute('first_name') }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label required">Издательство</label>
                        <select name="company_id" class="form-select" required>
                            <option value="">— Выберите издательство —</option>
                            @foreach($companies as $company)
                                <option value="{{ $company->getKey() }}"
                                    @selected(old('company_id') == $company->getKey())>
                                    {{ $company->getAttribute('name') }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="mt-4 d-flex gap-2">
                    <button class="btn btn-success"><i class="fas fa-save me-1"></i>Создать</button>
                    <a href="{{ route('books.index') }}" class="btn btn-outline-secondary"><i class="fas fa-times me-1"></i>Отмена</a>
                </div>
            </form>
        </div>
    </div>
@endsection
