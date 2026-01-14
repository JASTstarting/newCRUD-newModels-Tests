@extends('layouts.app')

@section('title', 'Корзина')

@section('content')
    {{-- Обработка сообщений из сессии - ТОЛЬКО ОДНО сообщение приоритета --}}
    @if(session('success') || session('error') || session('info'))
        <div class="mb-4">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @elseif(session('info'))
                <div class="alert alert-info alert-dismissible fade show mb-3" role="alert">
                    {{ session('info') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
        </div>
    @endif

    <div class="card mb-4">
        <div class="card-header bg-dark text-white d-flex align-items-center">
            <i class="fas fa-trash-alt me-2" aria-hidden="true"></i>
            <h2 class="mb-0">Корзина</h2>
        </div>
        <div class="card-body">
            <p class="mb-0 text-muted">
                Здесь отображаются удалённые авторы и книги. Можно восстановить или удалить навсегда.
            </p>
        </div>
    </div>

    {{-- Блок: Удалённые авторы --}}
    <div class="card mb-4">
        <div class="card-header bg-warning text-dark d-flex align-items-center">
            <i class="fas fa-user-slash me-2" aria-hidden="true"></i>
            <h5 class="mb-0">Удалённые авторы</h5>
            <span class="badge bg-dark text-white ms-auto">{{ $trashedAuthors->total() }}</span>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('trash.index') }}" class="row g-3 mb-3">
                <input type="hidden" name="include_books" value="{{ $includeBooks ? 1 : 0 }}">
                <div class="col-md-9">
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-magnifying-glass" aria-hidden="true"></i></span>
                        <input type="text" name="authors_search" value="{{ $searchAuthors }}" class="form-control" placeholder="Поиск по авторам...">
                    </div>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button class="btn btn-warning flex-fill"><i class="fas fa-search me-1" aria-hidden="true"></i>Найти</button>
                    @if($searchAuthors)
                        <a href="{{ route('trash.index', ['include_books' => $includeBooks ? 1 : 0]) }}" class="btn btn-outline-secondary flex-fill">Сбросить</a>
                    @endif
                </div>
            </form>

            @if($trashedAuthors->count() === 0)
                <div class="alert alert-info mb-0">Удалённых авторов нет</div>
            @else
                <div class="list-group">
                    @foreach($trashedAuthors as $author)
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>{{ $author->last_name }} {{ $author->first_name }}</strong>
                                    <span class="text-muted">(#{{ $author->id }})</span>
                                    <div class="small text-muted">
                                        Удалён: {{ optional($author->deleted_at)->format('d.m.Y H:i') }}
                                    </div>
                                </div>
                                <div class="d-flex gap-2">
                                    {{-- Восстановить только автора --}}
                                    <form method="POST" action="{{ route('trash.authors.restore', ['id' => $author->id]) }}">
                                        @csrf
                                        <button class="btn btn-sm btn-success" title="Восстановить автора" aria-label="Восстановить автора">
                                            <i class="fas fa-undo" aria-hidden="true"></i> Восстановить автора
                                        </button>
                                    </form>

                                    {{-- Удалить автора и все его книги навсегда --}}
                                    <form method="POST" action="{{ route('trash.authors.force-delete', ['id' => $author->id]) }}"
                                          onsubmit="return confirm('Удалить автора и все его книги навсегда?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-danger" title="Удалить навсегда автора и все его книги" aria-label="Удалить навсегда">
                                            <i class="fas fa-trash-alt" aria-hidden="true"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>

                            {{-- Удалённые книги этого автора --}}
                            @if($author->relationLoaded('books') && $author->books->count())
                                <div class="mt-3">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <div class="fw-semibold">Книги автора (в корзине):</div>
                                        <small class="text-muted">{{ $author->books->count() }} шт.</small>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table table-sm table-bordered align-middle mb-0">
                                            <thead class="table-light">
                                            <tr>
                                                <th>Название</th>
                                                <th>Издательство</th>
                                                <th>Город</th>
                                                <th>Удалена</th>
                                                <th class="text-end"><i class="fas fa-cog" aria-hidden="true"></i></th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @foreach($author->books as $book)
                                                <tr>
                                                    <td>{{ $book->title }}</td>
                                                    <td>{{ $book->company?->name ?? '—' }}</td>
                                                    <td>{{ $book->company?->city?->name ?? '—' }}</td>
                                                    <td>{{ $book->deleted_at?->format('d.m.Y H:i') ?? '—' }}</td>
                                                    <td class="text-end">
                                                        @include('trash._book-actions', ['book' => $book])
                                                    </td>
                                                </tr>
                                            @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @else
                                @if(isset($author->trashed_books_count))
                                    <div class="small text-muted mt-2">
                                        У этого автора удалённых книг: {{ $author->trashed_books_count }}
                                    </div>
                                @else
                                    <div class="small text-muted mt-2">У этого автора нет удалённых книг.</div>
                                @endif
                            @endif
                        </div>
                    @endforeach
                </div>

                <div class="mt-3">
                    {{ $trashedAuthors->links() }}
                </div>
            @endif
        </div>
    </div>

    {{-- Блок: Удалённые книги (авторы активны) --}}
    <div class="card">
        <div class="card-header bg-secondary text-white d-flex align-items-center">
            <i class="fas fa-book-dead me-2" aria-hidden="true"></i>
            <h5 class="mb-0">Удалённые книги (авторы активны)</h5>
            <span class="badge bg-light text-dark ms-auto">{{ $trashedBooks->total() }}</span>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('trash.index') }}" class="row g-3 mb-3">
                <input type="hidden" name="include_books" value="{{ $includeBooks ? 1 : 0 }}">
                <div class="col-md-9">
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-magnifying-glass" aria-hidden="true"></i></span>
                        <input type="text" name="books_search" value="{{ $searchBooks }}" class="form-control" placeholder="Поиск по книгам...">
                    </div>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button class="btn btn-secondary flex-fill"><i class="fas fa-search me-1" aria-hidden="true"></i>Найти</button>
                    @if($searchBooks)
                        <a href="{{ route('trash.index', ['include_books' => $includeBooks ? 1 : 0]) }}" class="btn btn-outline-secondary flex-fill">Сбросить</a>
                    @endif
                </div>
            </form>

            @if($trashedBooks->count() === 0)
                <div class="alert alert-info mb-0">Удалённых книг нет</div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                        <tr>
                            <th>Название</th>
                            <th>Автор</th>
                            <th>Издательство</th>
                            <th>Город</th>
                            <th>Удалена</th>
                            <th class="text-end"><i class="fas fa-cog" aria-hidden="true"></i></th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($trashedBooks as $book)
                            <tr>
                                <td>{{ $book->title }}</td>
                                <td>{{ $book->author ? ($book->author->last_name . ' ' . $book->author->first_name) : '—' }}</td>
                                <td>{{ $book->company?->name ?? '—' }}</td>
                                <td>{{ $book->company?->city?->name ?? '—' }}</td>
                                <td>{{ $book->deleted_at?->format('d.m.Y H:i') ?? '—' }}</td>
                                <td class="text-end">
                                    @include('trash._book-actions', ['book' => $book])
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $trashedBooks->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection

{{-- Partial для действий с книгами --}}
@includeWhen(false, 'trash._book-actions') {{-- Заглушка для IDE --}}

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const alertElement = document.querySelector('.alert');
            if (alertElement && window.location.hash === '') {
                alertElement.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }

            // Защита от двойной отправки формы
            document.querySelectorAll('form').forEach(form => {
                let isSubmitting = false;
                form.addEventListener('submit', function(e) {
                    if (isSubmitting) {
                        e.preventDefault();
                        return;
                    }
                    isSubmitting = true;
                    const submitButtons = form.querySelectorAll('button[type="submit"], input[type="submit"]');
                    submitButtons.forEach(button => {
                        button.disabled = true;
                        button.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Подождите...';
                    });
                });
            });
        });
    </script>
@endpush
