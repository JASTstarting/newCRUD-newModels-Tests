@php
    use App\Models\{Author, Book, Company, City};
@endphp
    <!doctype html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>@yield('title', 'Система управления авторами')</title>

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Bootstrap и иконки -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    @stack('styles')

    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
        }

        .table th {
            border-top: none;
            font-weight: 600;
            color: #495057;
        }

        .table td {
            vertical-align: middle;
        }

        .table tbody tr:hover {
            background-color: rgba(13, 110, 253, 0.05);
        }

        .search-highlight {
            background-color: #ffecb3;
            padding: 0 2px;
            border-radius: 2px;
            font-weight: 600;
        }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
    <div class="container">
        <a class="navbar-brand" href="{{ route('authors.index') }}">
            <i class="fas fa-book me-2"></i>Управление авторами
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false"
                aria-label="Переключить навигацию">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('authors.*') ? 'active' : '' }}"
                       href="{{ route('authors.index') }}">
                        <i class="fas fa-users me-1"></i>Авторы
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('books.*') ? 'active' : '' }}"
                       href="{{ route('books.index') }}">
                        <i class="fas fa-book-open me-1"></i>Книги
                    </a>
                </li>
                @php
                    $trashAuthorsCount = Author::onlyTrashed()->count();
                    $trashBooksCount   = Book::onlyTrashed()->count();
                @endphp
                <a class="nav-link {{ request()->routeIs('trash.index') ? 'active' : '' }}"
                   href="{{ route('trash.index') }}">
                    <i class="fas fa-trash me-1"></i>Корзина
                    <span class="badge bg-warning text-dark ms-1">{{ $trashAuthorsCount + $trashBooksCount }}</span>
                </a>

            </ul>

            <div class="d-flex gap-2">
                <a href="{{ route('authors.create') }}" class="btn btn-success">
                    <i class="fas fa-plus me-1"></i>Добавить автора
                </a>
                <a href="{{ route('books.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i>Добавить книгу
                </a>
            </div>
        </div>
    </div>
</nav>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"
                            aria-label="Закрыть"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"
                            aria-label="Закрыть"></button>
                </div>
            @endif

            @yield('content')
        </div>
    </div>
</div>

<!-- Bootstrap bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Авто-скрытие алертов
        setTimeout(() => {
            document.querySelectorAll('.alert').forEach(alert => {
                if (alert.classList.contains('show')) {
                    alert.classList.remove('show');
                    alert.classList.add('fade');
                    setTimeout(() => {
                        alert.style.display = 'none';
                    }, 150);
                }
            });
        }, 5000);

        // Подсветка совпадений в колонке Имя (страница авторов)
        const searchQuery = '{{ request('search', '') }}'.toLowerCase().trim();
        if (searchQuery) {
            document.querySelectorAll('td:nth-child(2)').forEach(cell => {
                const text = cell.textContent.toLowerCase();
                if (text.includes(searchQuery)) {
                    const regex = new RegExp('(' + searchQuery.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + ')', 'gi');
                    cell.innerHTML = cell.innerHTML.replace(regex, match =>
                        `<span class="search-highlight">${match}</span>`
                    );
                }
            });
        }

        // Подтверждение действий для форм с data-confirm
        document.querySelectorAll('form[data-confirm]').forEach(form => {
            form.addEventListener('submit', (e) => {
                const message = form.getAttribute('data-confirm') || 'Подтвердите действие';
                if (!window.confirm(message)) {
                    e.preventDefault();
                }
            });
        });
    });
</script>

@stack('scripts')
</body>
</html>
