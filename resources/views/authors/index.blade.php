@extends('layouts.app')

@section('title', 'Список авторов')

@section('content')
    @php
        // Флаг для показа кнопки "Сбросить"
        $hasFilters =
            (request()->has('search') && request('search') !== '') ||
            (request()->has('gender') && request('gender') !== '') ||
            (request()->has('active') && request('active') !== '');
    @endphp

    <div class="card mb-4 border-0 shadow-sm">
        <div class="card-header bg-primary text-white py-3">
            <div class="d-flex align-items-center">
                <i class="fas fa-search me-2 fs-4"></i>
                <h2 class="mb-0 h5 fw-bold">Поиск и фильтрация авторов</h2>
            </div>
        </div>
        <div class="card-body">
            <form id="authors-filter-form" action="{{ route('authors.index') }}" method="GET" class="row g-3">
                <div class="col-md-6">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0">
                            <i class="fas fa-magnifying-glass text-muted"></i>
                        </span>
                        <input type="text" name="search" class="form-control border-start-0"
                               placeholder="Поиск по имени, фамилии или отчеству..."
                               value="{{ request('search', '') }}"
                               minlength="2"
                               aria-label="Поиск авторов"
                               aria-describedby="search-help">
                    </div>
                    <div id="search-help" class="form-text text-muted small">
                        <small>Минимум 2 символа для поиска</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label visually-hidden">Пол</label>
                    <select name="gender" class="form-select">
                        <option value="">Все полы</option>
                        <option value="1" {{ request('gender') === '1' ? 'selected' : '' }}>Мужской</option>
                        <option value="0" {{ request('gender') === '0' ? 'selected' : '' }}>Женский</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label visually-hidden">Статус</label>
                    <select name="active" class="form-select">
                        <option value="">Все статусы</option>
                        <option value="1" {{ request('active') === '1' ? 'selected' : '' }}>Активные</option>
                        <option value="0" {{ request('active') === '0' ? 'selected' : '' }}>Неактивные</option>
                    </select>
                </div>

                <div class="col-md-6 d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-fill" aria-label="Найти авторов">
                        <i class="fas fa-search me-1"></i>Применить фильтры
                    </button>

                    @if($hasFilters)
                        <a href="{{ route('authors.index') }}" class="btn btn-outline-secondary flex-fill" aria-label="Сбросить все фильтры">
                            <i class="fas fa-filter-circle-xmark me-1"></i>Сбросить
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Закрыть"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Закрыть"></button>
        </div>
    @endif

    @if($authors->count() === 0)
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-5">
                <div class="mb-4">
                    <i class="fas fa-users-slash text-muted fs-1 mb-3"></i>
                    <h3 class="h4 mb-3 fw-bold text-muted">Авторов не найдено</h3>
                </div>

                @if($hasFilters)
                    <p class="text-muted">
                        По заданным критериям ничего не найдено.
                        Попробуйте изменить фильтры или сбросить их.
                    </p>
                @else
                    <p class="text-muted">
                        Добавьте первого автора, чтобы начать работу.
                    </p>
                @endif

                <div class="mt-4">
                    <a href="{{ route('authors.create') }}" class="btn btn-primary px-4">
                        <i class="fas fa-plus me-2"></i>Добавить автора
                    </a>
                </div>
            </div>
        </div>
    @else
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-light py-3">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h2 class="mb-0 h5 fw-bold">
                        <i class="fas fa-users me-2 text-primary"></i>Список авторов
                    </h2>
                    <span class="badge bg-info text-dark fs-6 px-3 py-2">
                        Найдено: {{ $authors->total() }}
                    </span>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                        <tr>
                            <th scope="col" class="px-3 py-2 fw-bold">
                                <a href="{{ route('authors.index', array_merge(request()->all(), ['sort' => 'last_name', 'direction' => request('direction') === 'asc' && request('sort') === 'last_name' ? 'desc' : 'asc'])) }}"
                                   class="text-decoration-none text-dark d-block">
                                    Фамилия
                                    @if(request('sort') === 'last_name')
                                        <i class="fas fa-sort-{{ request('direction') === 'asc' ? 'up' : 'down' }} ms-1"></i>
                                    @endif
                                </a>
                            </th>
                            <th scope="col" class="px-3 py-2 fw-bold">
                                <a href="{{ route('authors.index', array_merge(request()->all(), ['sort' => 'first_name', 'direction' => request('direction') === 'asc' && request('sort') === 'first_name' ? 'desc' : 'asc'])) }}"
                                   class="text-decoration-none text-dark d-block">
                                    Имя
                                    @if(request('sort') === 'first_name')
                                        <i class="fas fa-sort-{{ request('direction') === 'asc' ? 'up' : 'down' }} ms-1"></i>
                                    @endif
                                </a>
                            </th>
                            <th scope="col" class="px-3 py-2 fw-bold">
                                <a href="{{ route('authors.index', array_merge(request()->all(), ['sort' => 'father_name', 'direction' => request('direction') === 'asc' && request('sort') === 'father_name' ? 'desc' : 'asc'])) }}"
                                   class="text-decoration-none text-dark d-block">
                                    Отчество
                                    @if(request('sort') === 'father_name')
                                        <i class="fas fa-sort-{{ request('direction') === 'asc' ? 'up' : 'down' }} ms-1"></i>
                                    @endif
                                </a>
                            </th>
                            <th scope="col" class="px-3 py-2 fw-bold">
                                <a href="{{ route('authors.index', array_merge(request()->all(), ['sort' => 'birth_date', 'direction' => request('direction') === 'asc' && request('sort') === 'birth_date' ? 'desc' : 'asc'])) }}"
                                   class="text-decoration-none text-dark d-block">
                                    Дата рождения
                                    @if(request('sort') === 'birth_date')
                                        <i class="fas fa-sort-{{ request('direction') === 'asc' ? 'up' : 'down' }} ms-1"></i>
                                    @endif
                                </a>
                            </th>
                            <th scope="col" class="px-3 py-2 fw-bold">Биография</th>
                            <th scope="col" class="px-3 py-2 fw-bold text-center">
                                <a href="{{ route('authors.index', array_merge(request()->all(), ['sort' => 'gender', 'direction' => request('direction') === 'asc' && request('sort') === 'gender' ? 'desc' : 'asc'])) }}"
                                   class="text-decoration-none text-dark d-block">
                                    Пол
                                    @if(request('sort') === 'gender')
                                        <i class="fas fa-sort-{{ request('direction') === 'asc' ? 'up' : 'down' }} ms-1"></i>
                                    @endif
                                </a>
                            </th>
                            <th scope="col" class="px-3 py-2 fw-bold text-center">
                                <a href="{{ route('authors.index', array_merge(request()->all(), ['sort' => 'active', 'direction' => request('direction') === 'asc' && request('sort') === 'active' ? 'desc' : 'asc'])) }}"
                                   class="text-decoration-none text-dark d-block">
                                    Статус
                                    @if(request('sort') === 'active')
                                        <i class="fas fa-sort-{{ request('direction') === 'asc' ? 'up' : 'down' }} ms-1"></i>
                                    @endif
                                </a>
                            </th>
                            <th scope="col" class="px-3 py-2 fw-bold text-end">Действия</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($authors as $author)
                            <tr>
                                <td class="px-3 py-2 fw-medium">
                                    {{ $author->last_name }}
                                </td>
                                <td class="px-3 py-2">
                                    {{ $author->first_name }}
                                </td>
                                <td class="px-3 py-2">
                                    {{ $author->father_name }}
                                </td>
                                <td class="px-3 py-2">
                                    @if($author->birth_date)
                                        <span class="badge bg-light text-dark px-2 py-1">
                                            {{ $author->birth_date->format('d.m.Y') }}
                                        </span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="px-3 py-2">
                                    @if($author->biography)
                                        <div class="text-truncate" style="max-width: 200px;"
                                             title="{{ $author->biography }}">
                                            {{ \Illuminate\Support\Str::limit($author->biography, 50) }}
                                        </div>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="px-3 py-2 text-center">
                                    @if($author->gender)
                                        <span class="badge bg-primary-subtle text-primary px-2 py-1"
                                              aria-label="Мужской">
                                            <i class="fas fa-mars me-1"></i>Мужской
                                        </span>
                                    @else
                                        <span class="badge bg-danger-subtle text-danger px-2 py-1"
                                              aria-label="Женский">
                                            <i class="fas fa-venus me-1"></i>Женский
                                        </span>
                                    @endif
                                </td>
                                <td class="px-3 py-2 text-center">
                                    @if($author->active)
                                        <span class="badge bg-success-subtle text-success px-3 py-1"
                                              aria-label="Активный автор">
                                            <i class="fas fa-toggle-on me-1"></i>Активен
                                        </span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary px-3 py-1"
                                              aria-label="Неактивный автор">
                                            <i class="fas fa-toggle-off me-1"></i>Неактивен
                                        </span>
                                    @endif
                                </td>
                                <td class="px-3 py-2 text-end">
                                    <div class="d-flex gap-2 justify-content-end">
                                        <a href="{{ route('authors.edit', $author) }}"
                                           class="btn btn-sm btn-outline-primary"
                                           title="Редактировать автора"
                                           aria-label="Редактировать автора {{ $author->last_name }} {{ $author->first_name }}">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button"
                                                class="btn btn-sm btn-outline-danger delete-author-btn"
                                                data-author-id="{{ $author->id }}"
                                                data-author-name="{{ $author->last_name }} {{ $author->first_name }}"
                                                title="Удалить автора"
                                                aria-label="Удалить автора {{ $author->last_name }} {{ $author->first_name }}">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mt-3 gap-2">
                    <div class="text-muted small">
                        Показано {{ $authors->firstItem() }}-{{ $authors->lastItem() }} из {{ $authors->total() }}
                    </div>
                    <div>
                        {{ $authors->appends(request()->query())->links() }}
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Темная тема (необязательно)
            if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
                document.documentElement.setAttribute('data-bs-theme', 'dark');
            }

            // Валидация формы (минимум 2 символа, если поле непустое)
            const searchForm = document.getElementById('authors-filter-form');
            if (searchForm) {
                searchForm.addEventListener('submit', function(e) {
                    const searchInput = this.querySelector('input[name="search"]');
                    if (searchInput) {
                        const v = searchInput.value.trim();
                        if (v.length > 0 && v.length < 2) {
                            e.preventDefault();
                            alert('Минимум 2 символа для поиска');
                        }
                    }
                });
            }

            // Удаление автора
            document.querySelectorAll('.delete-author-btn').forEach((button) => {
                button.addEventListener('click', function() {
                    const authorId = this.getAttribute('data-author-id');
                    const authorName = this.getAttribute('data-author-name') || '';

                    if (!authorId) return;

                    if (!confirm(`Переместить автора "${authorName}" в корзину?`)) {
                        return;
                    }

                    /** Явно укажем тип формы для линтера/IDE */
                    /** @type {HTMLFormElement} */
                    const deleteForm = document.createElement('form');
                    deleteForm.method = 'POST';
                    deleteForm.action = `/authors/${encodeURIComponent(authorId)}`;

                    // Получаем CSRF-токен из мета-тега. Не обращаемся к Element.content — используем getAttribute.
                    const csrfMeta = document.querySelector('meta[name="csrf-token"]');
                    const csrfValue = (csrfMeta && typeof csrfMeta.getAttribute === 'function')
                        ? (csrfMeta.getAttribute('content') || '')
                        : '';

                    const csrfInput = document.createElement('input');
                    csrfInput.type = 'hidden';
                    csrfInput.name = '_token';
                    csrfInput.value = csrfValue;
                    deleteForm.appendChild(csrfInput);

                    const methodInput = document.createElement('input');
                    methodInput.type = 'hidden';
                    methodInput.name = '_method';
                    methodInput.value = 'DELETE';
                    deleteForm.appendChild(methodInput);

                    deleteForm.style.display = 'none';
                    document.body.appendChild(deleteForm);

                    // Корректный вызов отправки формы. Если IDE не знает submit(),
                    // она "узнает" его благодаря JSDoc типу HTMLFormElement. Фолбэк оставлен.
                    if (typeof deleteForm.requestSubmit === 'function') {
                        deleteForm.requestSubmit();
                    } else {
                        deleteForm.submit();
                    }
                });
            });
        });
    </script>
@endsection
