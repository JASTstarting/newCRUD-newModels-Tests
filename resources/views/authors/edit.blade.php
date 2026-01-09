@php use App\Models\Book;use Illuminate\Support\Collection; @endphp
@extends('layouts.app')

@section('title', 'Редактировать автора')

@section('content')
    <div class="card">
        <div class="card-header bg-warning text-dark">
            <div class="d-flex justify-content-between align-items-center">
                <h2 class="mb-0">
                    <i class="fas fa-edit me-2"></i>Редактировать автора #{{ $author->id }}
                </h2>
                <div class="btn-group">
                    <a href="{{ route('authors.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i>Назад к списку
                    </a>

                    {{-- Удалить автора И ВСЕ его книги (soft-delete) --}}
                    <form action="{{ route('authors.destroy', $author) }}"
                          method="POST"
                          class="d-inline"
                          onsubmit="return confirm('Переместить в корзину автора и ВСЕ его книги?')">
                        {{ csrf_field() }}
                        {{ method_field('DELETE') }}
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-user-slash me-1"></i>Удалить автора и книги
                        </button>
                    </form>
                </div>
            </div>
        </div>

        @if(isset($author))
            <div class="card mt-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Книги автора</h5>
                </div>
                <div class="card-body">
                    @php
                        /** @var Collection<Book> $authorBooks */
                        $authorBooks = $author->books()->with('company.city')->get();
                    @endphp

                    @if($authorBooks->isEmpty())
                        <div class="text-muted">У автора пока нет книг.</div>
                    @else
                        <form action="{{ route('books.multiple-destroy') }}" method="POST"
                              onsubmit="return confirm('Переместить выбранные книги в корзину?')">
                            {{ csrf_field() }}
                            <div class="table-responsive">
                                <table class="table table-striped align-middle">
                                    <thead class="table-light">
                                    <tr>
                                        <th style="width: 36px;">
                                            <input type="checkbox" id="check_all">
                                        </th>
                                        <th>Название</th>
                                        <th>Издательство</th>
                                        <th>Город</th>
                                        <th>Дата создания</th>
                                        <th class="text-end"><i class="fas fa-cog"></i></th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($authorBooks as $book)
                                        <tr>
                                            <td>
                                                <input type="checkbox" name="book_ids[]" value="{{ $book->getKey() }}"
                                                       class="book-checkbox">
                                            </td>
                                            <td>{{ $book->getAttribute('name') }}</td>
                                            <td>{{ $book->company?->getAttribute('name') ?? '—' }}</td>
                                            <td>{{ $book->company?->city?->getAttribute('name') ?? '—' }}</td>
                                            <td>
                                                @if($book->created_date)
                                                    {{ $book->created_date->format('d.m.Y') }}
                                                @else
                                                    —
                                                @endif
                                            </td>
                                            <td class="text-end">
                                                <a class="btn btn-sm btn-outline-primary"
                                                   href="{{ route('books.edit', $book) }}">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form action="{{ route('books.destroy', $book) }}" method="POST"
                                                      class="d-inline"
                                                      onsubmit="return confirm('Удалить эту книгу в корзину?')">
                                                    {{ csrf_field() }}
                                                    {{ method_field('DELETE') }}
                                                    <button class="btn btn-sm btn-outline-danger">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <button class="btn btn-outline-danger mt-2">
                                <i class="fas fa-trash me-1"></i>Удалить выбранные книги
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        @endif

        <div class="card-body">
            <form action="{{ route('authors.update', $author) }}" method="POST">
                {{ csrf_field() }}
                {{ method_field('PUT') }}
                @include('authors.form')
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Обработчик для главного чекбокса
            const checkAllCheckbox = document.getElementById('check_all');
            if (checkAllCheckbox) {
                checkAllCheckbox.addEventListener('change', function () {
                    const checkboxes = document.querySelectorAll('.book-checkbox');
                    checkboxes.forEach(checkbox => {
                        checkbox.checked = this.checked;
                    });
                });
            }
        });
    </script>
@endpush
