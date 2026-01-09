@extends('layouts.app')

@section('title', 'Список книг')

@section('content')
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <div class="d-flex align-items-center">
                <h2 class="mb-0"><i class="fas fa-book-open me-2"></i>Книги</h2>

            </div>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('books.index') }}" class="row g-3 mb-3">
                <div class="col-md-10">
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-magnifying-glass"></i></span>
                        <input type="text" name="search" class="form-control" placeholder="Поиск по названию..."
                               value="{{ old('search', $search ?? '') }}">
                    </div>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-primary w-100"><i class="fas fa-search me-1"></i>Найти</button>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                    <tr>
                        <th>Название</th>
                        <th>Автор</th>
                        <th>Издательство</th>
                        <th>Город</th>
                        <th>Дата создания</th>
                        <th class="text-end"><i class="fas fa-cog"></i></th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($books as $book)
                        <tr>
                            <td>{{ $book->name }}</td>
                            <td>{{ $book->author ? ($book->author->last_name . ' ' . $book->author->first_name) : '—' }}</td>
                            <td>{{ $book->company->name ?? '—' }}</td>
                            <td>{{ $book->company->city->name ?? '—' }}</td>
                            <td>{{ $book->created_date?->format('d.m.Y') }}</td>
                            <td class="text-end">
                                <a href="{{ route('books.edit', ['book' => $book->id]) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('books.destroy', ['book' => $book->id]) }}"
                                      method="POST"
                                      class="d-inline"
                                      onsubmit="return confirm('Удалить книгу?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">Нет данных</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-between align-items-center mt-3">
                <div class="text-muted">
                    Показано {{ $books->firstItem() }}-{{ $books->lastItem() }} из {{ $books->total() }}
                </div>
                <div>{{ $books->links() }}</div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script>
        // Переменные URL инициализируются на уровне PHP и передаются в JavaScript
        const BOOKS_AJAX_URL = "{{ route('books.ajax') }}";
        const AUTHORS_AJAX_URL = "{{ route('authors.ajax') }}";

        /**
         * Загрузка книг с обработкой ошибок
         * @param {number|string} page - Номер страницы
         * @param {string} search - Строка поиска
         * @returns {Promise<Object|null>}
         */
        async function loadBooks(page = 1, search = '') {
            try {
                // Гарантируем инициализацию URL
                const booksUrl = BOOKS_AJAX_URL || '/api/books';

                // Преобразуем page в строку для параметров запроса
                const pageParam = String(parseInt(page, 10) || 1);
                const searchParam = String(search || '').trim();

                const response = await axios.get(booksUrl, {
                    params: {
                        page: pageParam,
                        search: searchParam
                    },
                    timeout: 5000
                });

                // Проверяем структуру ответа
                if (response && response.data) {
                    console.log('Books (AJAX):', response.data);
                    return response.data;
                }

                console.warn('Пустой ответ от сервера при загрузке книг');
                return null;
            } catch (error) {
                console.error('Ошибка при загрузке книг:', error);
                return null;
            }
        }

        /**
         * Загрузка авторов с обработкой ошибок
         * @param {number|string} page - Номер страницы
         * @param {string} search - Строка поиска
         * @returns {Promise<Object|null>}
         */
        async function loadAuthors(page = 1, search = '') {
            try {
                // Гарантируем инициализацию URL
                const authorsUrl = AUTHORS_AJAX_URL || '/api/authors';

                // Преобразуем page в строку для параметров запроса
                const pageParam = String(parseInt(page, 10) || 1);
                const searchParam = String(search || '').trim();

                const response = await axios.get(authorsUrl, {
                    params: {
                        page: pageParam,
                        search: searchParam
                    },
                    timeout: 5000
                });

                // Проверяем структуру ответа
                if (response && response.data) {
                    console.log('Authors (AJAX):', response.data);
                    return response.data;
                }

                console.warn('Пустой ответ от сервера при загрузке авторов');
                return null;
            } catch (error) {
                console.error('Ошибка при загрузке авторов:', error);
                return null;
            }
        }

        document.addEventListener('DOMContentLoaded', async () => {
            try {
                // Загружаем книги с параметрами поиска
                const searchValue = '{{ $search ?? "" }}'.trim() || '';
                const booksData = await loadBooks(1, searchValue);

                // Безопасная проверка данных
                if (booksData && booksData.data && Array.isArray(booksData.data)) {
                    console.log('Загружено книг:', booksData.data.length);
                } else {
                    console.log('Нет данных для отображения или неверная структура ответа');
                }
            } catch (error) {
                console.error('Ошибка инициализации:', error);
            }
        });
    </script>
@endpush
