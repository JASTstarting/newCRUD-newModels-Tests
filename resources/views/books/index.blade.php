@extends('layouts.app')

@section('title', 'Список книг')

@section('content')
    @php
        // Флаг для показа кнопки "Сбросить"
        $hasFilters =
            (request()->has('search') && request('search') !== '') ||
            (request()->has('author_id') && request('author_id') !== '') ||
            (request()->has('company_id') && request('company_id') !== '') ||
            (request()->has('created_date_from') && request('created_date_from') !== '') ||
            (request()->has('created_date_to') && request('created_date_to') !== '');
    @endphp

    <div class="card mb-4 border-0 shadow-sm">
        <div class="card-header bg-primary text-white py-3">
            <div class="d-flex align-items-center">
                <i class="fas fa-search me-2 fs-4"></i>
                <h2 class="mb-0 h5 fw-bold">Поиск и фильтрация книг</h2>
            </div>
        </div>
        <div class="card-body">
            <form id="books-filter-form" action="{{ route('books.index') }}" method="GET" class="row g-3 needs-validation" novalidate>
                <div class="col-md-6">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0">
                            <i class="fas fa-magnifying-glass text-muted"></i>
                        </span>
                        <input type="text" name="search" class="form-control border-start-0"
                               placeholder="Поиск по названию книги..."
                               value="{{ request('search', '') }}"
                               minlength="2"
                               aria-label="Поиск книг"
                               aria-describedby="search-help">
                    </div>
                    <div id="search-help" class="form-text text-muted small">
                        <small>Минимум 2 символа для поиска</small>
                    </div>
                </div>

                <div class="col-md-3">
                    <label class="form-label visually-hidden">Автор</label>
                    <select name="author_id" class="form-select" aria-label="Фильтр по автору">
                        <option value="">Все авторы</option>
                        @foreach($authorsSelect as $a)
                            <option value="{{ $a['id'] }}" {{ (string)request('author_id') === (string)$a['id'] ? 'selected' : '' }}>
                                {{ $a['full_name'] }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label visually-hidden">Издательство</label>
                    <select name="company_id" class="form-select" aria-label="Фильтр по издательству">
                        <option value="">Все издательства</option>
                        @foreach($companiesSelect as $c)
                            <option value="{{ $c['id'] }}" {{ (string)request('company_id') === (string)$c['id'] ? 'selected' : '' }}>
                                {{ $c['name'] }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Дата от</label>
                    <input type="date"
                           name="created_date_from"
                           class="form-control"
                           value="{{ request('created_date_from') }}"
                           max="{{ date('Y-m-d') }}"
                           aria-describedby="created-date-from-help">
                    <div id="created-date-from-help" class="form-text text-muted small">
                        Выберите начальную дату (не позднее сегодня)
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Дата до</label>
                    <input type="date"
                           name="created_date_to"
                           class="form-control"
                           value="{{ request('created_date_to') }}"
                           max="{{ date('Y-m-d') }}"
                           aria-describedby="created-date-to-help">
                    <div id="created-date-to-help" class="form-text text-muted small">
                        Выберите конечную дату (не позднее сегодня)
                    </div>
                </div>

                <div class="col-12 d-flex gap-2">
                    <button type="submit" class="btn btn-sm btn-primary flex-fill" aria-label="Найти книги">
                        <i class="fas fa-search me-1"></i>Применить фильтры
                    </button>

                    @if($hasFilters)
                        <a href="{{ route('books.index') }}" class="btn btn-sm btn-outline-secondary flex-fill" aria-label="Сбросить все фильтры">
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

    @if($books->count() === 0)
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-5">
                <div class="mb-4">
                    <i class="fas fa-book-slash text-muted fs-1 mb-3"></i>
                    <h3 class="h4 mb-3 fw-bold text-muted">Книг не найдено</h3>
                </div>

                @if($hasFilters)
                    <p class="text-muted">
                        По заданным критериям ничего не найдено.
                        Попробуйте изменить фильтры или сбросить их.
                    </p>
                @else
                    <p class="text-muted">
                        Добавьте первую книгу, чтобы начать работу.
                    </p>
                @endif

                <div class="mt-4">
                    <a href="{{ route('books.create') }}" class="btn btn-primary px-4">
                        <i class="fas fa-plus me-2"></i>Добавить книгу
                    </a>
                </div>
            </div>
        </div>
    @else
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-light py-3">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h2 class="mb-0 h5 fw-bold">
                        <i class="fas fa-book me-2 text-primary"></i>Список книг
                    </h2>
                    <span class="badge bg-info text-dark fs-6 px-3 py-2">
                        Найдено: {{ $books->total() }}
                    </span>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                        <tr>
                            <th scope="col" class="px-3 py-2 fw-bold">
                                <a href="{{ route('books.index', array_merge(request()->all(), ['sort' => 'title', 'direction' => request('direction') === 'asc' && request('sort') === 'title' ? 'desc' : 'asc'])) }}"
                                   class="text-decoration-none text-dark d-block">
                                    Название
                                    @if(request('sort') === 'title')
                                        <i class="fas fa-sort-{{ request('direction') === 'asc' ? 'up' : 'down' }} ms-1"></i>
                                    @endif
                                </a>
                            </th>
                            <th scope="col" class="px-3 py-2 fw-bold">
                                <a href="{{ route('books.index', array_merge(request()->all(), ['sort' => 'author', 'direction' => request('direction') === 'asc' && request('sort') === 'author' ? 'desc' : 'asc'])) }}"
                                   class="text-decoration-none text-dark d-block">
                                    Автор
                                    @if(request('sort') === 'author')
                                        <i class="fas fa-sort-{{ request('direction') === 'asc' ? 'up' : 'down' }} ms-1"></i>
                                    @endif
                                </a>
                            </th>
                            <th scope="col" class="px-3 py-2 fw-bold">
                                <a href="{{ route('books.index', array_merge(request()->all(), ['sort' => 'company', 'direction' => request('direction') === 'asc' && request('sort') === 'company' ? 'desc' : 'asc'])) }}"
                                   class="text-decoration-none text-dark d-block">
                                    Издательство
                                    @if(request('sort') === 'company')
                                        <i class="fas fa-sort-{{ request('direction') === 'asc' ? 'up' : 'down' }} ms-1"></i>
                                    @endif
                                </a>
                            </th>
                            <th scope="col" class="px-3 py-2 fw-bold d-none d-md-table-cell">Город</th>
                            <th scope="col" class="px-3 py-2 fw-bold text-center">
                                <a href="{{ route('books.index', array_merge(request()->all(), ['sort' => 'created_date', 'direction' => request('direction') === 'asc' && request('sort') === 'created_date' ? 'desc' : 'asc'])) }}"
                                   class="text-decoration-none text-dark d-block">
                                    Дата создания
                                    @if(request('sort') === 'created_date')
                                        <i class="fas fa-sort-{{ request('direction') === 'asc' ? 'up' : 'down' }} ms-1"></i>
                                    @endif
                                </a>
                            </th>
                            <th scope="col" class="px-3 py-2 fw-bold text-end">Действия</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($books as $book)
                            <tr>
                                <td class="px-3 py-2 fw-medium">
                                    {{ $book->title }}
                                </td>
                                <td class="px-3 py-2">
                                    @if($book->author)
                                        <span class="badge bg-light text-dark px-2 py-1" title="{{ $book->author->last_name }} {{ $book->author->first_name }}">
                                            <i class="fas fa-user text-muted me-1"></i>
                                            {{ $book->author->last_name }} {{ $book->author->first_name }}
                                        </span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="px-3 py-2">
                                    {{ $book->company?->name ?? '—' }}
                                </td>
                                <td class="px-3 py-2 d-none d-md-table-cell">
                                    {{ $book->company?->city?->name ?? '—' }}
                                </td>
                                <td class="px-3 py-2 text-center">
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
                                        <button type="button"
                                                class="btn btn-sm btn-outline-danger delete-book-btn"
                                                data-book-id="{{ $book->id }}"
                                                data-book-title="{{ $book->title }}"
                                                title="Удалить книгу"
                                                aria-label="Удалить книгу {{ $book->title }}">
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
                        Показано {{ $books->firstItem() }}-{{ $books->lastItem() }} из {{ $books->total() }}
                    </div>
                    <div>
                        {{ $books->appends(request()->query())->links() }}
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Тёмная тема (необязательно)
            if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
                document.documentElement.setAttribute('data-bs-theme', 'dark');
            }

            // Валидация формы фильтра (минимум 2 символа в поиске)
            const searchForm = document.getElementById('books-filter-form');
            if (searchForm) {
                searchForm.addEventListener('submit', function(e) {
                    const form = e.currentTarget instanceof HTMLFormElement ? e.currentTarget : null;
                    if (!form) return;

                    const input = form.querySelector('input[name="search"]');
                    if (input instanceof HTMLInputElement) {
                        const v = input.value.trim();
                        if (v.length > 0 && v.length < 2) {
                            e.preventDefault();
                            alert('Минимум 2 символа для поиска');
                            return;
                        }
                    }

                    // Проверка диапазона дат: "от" не позже "до"
                    const fromEl = form.querySelector('input[name="created_date_from"]');
                    const toEl   = form.querySelector('input[name="created_date_to"]');

                    const fromVal = (fromEl instanceof HTMLInputElement) ? fromEl.value : '';
                    const toVal   = (toEl   instanceof HTMLInputElement) ? toEl.value   : '';

                    if (fromVal && toVal) {
                        const fromDate = new Date(fromVal + 'T00:00:00');
                        const toDate   = new Date(toVal   + 'T00:00:00');

                        if (fromDate > toDate) {
                            e.preventDefault();
                            alert('Дата "от" не может быть позже даты "до".');
                        }
                    }
                });

                // Связка min/max атрибутов для диапазона дат
                const fromEl = searchForm.querySelector('input[name="created_date_from"]');
                const toEl   = searchForm.querySelector('input[name="created_date_to"]');

                if (fromEl instanceof HTMLInputElement && toEl instanceof HTMLInputElement) {
                    fromEl.addEventListener('change', () => {
                        toEl.min = fromEl.value || '';
                    });
                    toEl.addEventListener('change', () => {
                        fromEl.max = toEl.value || '{{ date('Y-m-d') }}';
                    });

                    // Инициализация при загрузке
                    if (fromEl.value) toEl.min = fromEl.value;
                    if (toEl.value) fromEl.max = toEl.value;
                }
            }

            // Удаление книги
            document.querySelectorAll('.delete-book-btn').forEach((button) => {
                button.addEventListener('click', function(e) {
                    const btn = e.currentTarget instanceof HTMLElement ? e.currentTarget : null;
                    if (!btn) return;

                    const bookId = btn.getAttribute('data-book-id');
                    const bookTitle = btn.getAttribute('data-book-title') || '';

                    if (!bookId) return;

                    if (!confirm(`Переместить книгу "${bookTitle}" в корзину?`)) {
                        return;
                    }

                    /** @type {HTMLFormElement} */
                    const deleteForm = document.createElement('form');
                    deleteForm.method = 'POST';
                    deleteForm.action = `/books/${encodeURIComponent(bookId)}`;

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
