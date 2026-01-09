@extends('layouts.app')

@section('title', 'Список авторов')

@section('content')
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h2 class="mb-0"><i class="fas fa-search me-2"></i>Поиск авторов</h2>
        </div>
        <div class="card-body">
            <form action="{{ route('authors.index') }}" method="GET" class="row g-3">
                <div class="col-md-8">
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-magnifying-glass"></i></span>
                        <input type="text" name="search" class="form-control"
                               placeholder="Поиск по имени, фамилии или отчеству..."
                               value="{{ old('search', $search ?? '') }}"
                               aria-label="Поиск авторов">
                    </div>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search me-1"></i>Найти
                    </button>
                </div>
                <div class="col-md-2">
                    @if(request()->filled('search'))
                        <a href="{{ route('authors.index') }}" class="btn btn-outline-secondary w-100">
                            <i class="fas fa-rotate me-1"></i>Сбросить
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    @if($authors->count() === 0)
        <div class="alert alert-info text-center py-5">
            <i class="fas fa-info-circle fa-3x mb-3 text-info"></i>
            <h4>Авторов не найдено</h4>
            @if(request()->filled('search'))
                <p>По запросу "<strong>{{ request('search') }}</strong>" ничего не найдено. Попробуйте изменить критерии поиска.</p>
            @else
                <p>Добавьте первого автора, чтобы начать работу.</p>
            @endif
        </div>
    @else
        <div class="card">
            <div class="card-header bg-light">
                <div class="d-flex justify-content-between align-items-center">
                    <h2 class="mb-0"><i class="fas fa-users me-2"></i>Список авторов</h2>
                    <span class="badge bg-info text-dark fs-6">
                    Найдено: {{ $authors->total() }}
                </span>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                        <tr>
                            <th>Фамилия</th>
                            <th>Имя</th>
                            <th>Отчество</th>
                            <th>Дата рождения</th>
                            <th>Биография</th>
                            <th>Пол</th>
                            <th>Статус</th>
                            <th class="text-end"><i class="fas fa-cog"></i></th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($authors as $author)
                            <tr>
                                <td>
                                    <strong>{{ $author->last_name }}</strong>
                                </td>
                                <td>
                                    {{ $author->first_name }}
                                </td>
                                <td>
                                    {{ $author->father_name }}
                                </td>
                                <td>
                                    @if($author->birth_date)
                                        {{ $author->birth_date->format('d.m.Y') }}
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>
                                    @if($author->biography)
                                        <div title="{{ $author->biography }}">
                                            {{ Str::limit($author->biography, 50) }}
                                        </div>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>
                                    @if($author->gender)
                                        <span class="badge bg-primary"><i class="fas fa-mars me-1"></i>Мужской</span>
                                    @else
                                        <span class="badge bg-danger"><i class="fas fa-venus me-1"></i>Женский</span>
                                    @endif
                                </td>
                                <td>
                                    @if($author->active)
                                        <span class="badge bg-success"><i class="fas fa-toggle-on me-1"></i>Активен</span>
                                    @else
                                        <span class="badge bg-secondary"><i class="fas fa-toggle-off me-1"></i>Неактивен</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <div class="action-buttons">
                                        <a href="{{ route('authors.edit', $author) }}"
                                           class="btn btn-sm btn-outline-primary"
                                           title="Редактировать">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('authors.destroy', $author) }}"
                                              method="POST"
                                              class="d-inline"
                                              data-confirm="Переместить автора в корзину?">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Удалить">
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

                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div class="text-muted">
                        Показано {{ $authors->firstItem() }}-{{ $authors->lastItem() }} из {{ $authors->total() }}
                    </div>
                    <div>
                        {{ $authors->links() }}
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection
