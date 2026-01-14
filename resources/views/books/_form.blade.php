@props(['book' => null, 'authors', 'companies', 'mode' => 'create'])

<div class="row g-3">
    <div class="col-md-8">
        <div class="card mb-4 border-0 shadow-sm">
            <div class="card-header bg-light py-3">
                <h5 class="mb-0 fw-bold">
                    <i class="fas fa-book me-2 text-primary"></i>Основная информация о книге
                </h5>
            </div>
            <div class="card-body">
                <div class="mb-4">
                    <label for="title" class="form-label fw-medium required">
                        <i class="fas fa-heading me-2 text-muted"></i>Название книги
                    </label>
                    <div class="input-group input-group-lg">
                        <span class="input-group-text bg-light border-end-0">
                            <i class="fas fa-book-open text-primary"></i>
                        </span>
                        <input type="text"
                               id="title"
                               name="title"
                               class="form-control form-control-lg @error('title') is-invalid @enderror"
                               value="{{ old('title', $book?->title ?? '') }}"
                               required
                               minlength="2"
                               autocomplete="off"
                               placeholder="Введите название книги..."
                               aria-describedby="titleHelp">
                    </div>
                    <div id="titleHelp" class="form-text text-muted small mt-1">
                        Название должно быть уникальным и описательным
                    </div>
                    @error('title')
                    <div class="invalid-feedback d-block mt-1">
                        <i class="fas fa-exclamation-triangle me-1"></i>{{ $message }}
                    </div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="description" class="form-label fw-medium required">
                        <i class="fas fa-align-left me-2 text-muted"></i>Описание
                    </label>
                    <textarea id="description"
                              name="description"
                              class="form-control form-control-lg @error('description') is-invalid @enderror"
                              rows="4"
                              required
                              minlength="50"
                              placeholder="Введите описание книги..."
                              aria-describedby="descriptionHelp">{{ old('description', $book?->description ?? '') }}</textarea>
                    <div id="descriptionHelp" class="form-text text-muted small mt-1">
                        Минимум 50 символов. Опишите содержание, особенности и целевую аудиторию
                    </div>
                    @error('description')
                    <div class="invalid-feedback d-block mt-1">
                        <i class="fas fa-exclamation-triangle me-1"></i>{{ $message }}
                    </div>
                    @enderror
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-light py-3">
                <h5 class="mb-0 fw-bold">
                    <i class="fas fa-calendar-alt me-2 text-primary"></i>Дополнительные параметры
                </h5>
            </div>
            <div class="card-body">
                <div class="row g-4">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="created_date" class="form-label fw-medium required">
                                <i class="fas fa-calendar me-2 text-muted"></i>Дата создания
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light">
                                    <i class="fas fa-calendar-day text-primary"></i>
                                </span>
                                <input type="date"
                                       id="created_date"
                                       name="created_date"
                                       class="form-control form-control-lg @error('created_date') is-invalid @enderror"
                                       value="{{ old('created_date', $book?->created_date?->format('Y-m-d') ?? now()->format('Y-m-d')) }}"
                                       required
                                       max="{{ now()->format('Y-m-d') }}"
                                       aria-describedby="createdDateHelp">
                            </div>
                            <div id="createdDateHelp" class="form-text text-muted small mt-1">
                                Выберите дату публикации или создания книги
                            </div>
                            @error('created_date')
                            <div class="invalid-feedback d-block mt-1">
                                <i class="fas fa-exclamation-triangle me-1"></i>{{ $message }}
                            </div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="author_id" class="form-label fw-medium required">
                                <i class="fas fa-user me-2 text-muted"></i>Автор
                            </label>
                            <select id="author_id"
                                    name="author_id"
                                    class="form-select form-select-lg @error('author_id') is-invalid @enderror"
                                    required
                                    aria-describedby="authorHelp">
                                @if($mode === 'create' || empty($book?->author_id))
                                    <option value="">— Выберите автора —</option>
                                @endif

                                @forelse($authors as $author)
                                    <option value="{{ $author['id'] }}"
                                        @selected(old('author_id', $book?->author_id ?? null) == $author['id'])>
                                        {{ $author['full_name'] }}
                                    </option>
                                @empty
                                    <option value="" disabled>Нет доступных авторов</option>
                                @endforelse
                            </select>
                            <div id="authorHelp" class="form-text text-muted small mt-1">
                                Выберите автора из списка или <a href="{{ route('authors.create') }}" class="text-primary text-decoration-none">добавьте нового</a>
                            </div>
                            @error('author_id')
                            <div class="invalid-feedback d-block mt-1">
                                <i class="fas fa-exclamation-triangle me-1"></i>{{ $message }}
                            </div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="company_id" class="form-label fw-medium required">
                                <i class="fas fa-building me-2 text-muted"></i>Издательство
                            </label>
                            <select id="company_id"
                                    name="company_id"
                                    class="form-select form-select-lg @error('company_id') is-invalid @enderror"
                                    required
                                    aria-describedby="companyHelp">
                                @if($mode === 'create' || empty($book?->company_id))
                                    <option value="">— Выберите издательство —</option>
                                @endif

                                @forelse($companies as $company)
                                    <option value="{{ $company['id'] }}"
                                        @selected(old('company_id', $book?->company_id ?? null) == $company['id'])>
                                        {{ $company['name'] }}
                                    </option>
                                @empty
                                    <option value="" disabled>Нет доступных издательств</option>
                                @endforelse
                            </select>
                            <div id="companyHelp" class="form-text text-muted small mt-1">
                                Выберите издательство или
                                <a href="{{ route('companies.create', ['return' => url()->current()]) }}"
                                   class="text-primary text-decoration-none">
                                    создайте новое
                                </a>
                            </div>
                            @error('company_id')
                            <div class="invalid-feedback d-block mt-1">
                                <i class="fas fa-exclamation-triangle me-1"></i>{{ $message }}
                            </div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Правая колонка с "липкими" кнопками действий -->
    <div class="col-md-4">
        <div class="sticky-md-top" style="top: 20px;">
            <div class="card mb-4 border-0 shadow-sm">
                <div class="card-header bg-light py-3">
                    <h5 class="mb-0 fw-bold">
                        <i class="fas fa-cogs me-2 text-primary"></i>Действия
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-3">
                        <button type="submit" id="submitBtn" class="btn {{ $mode === 'edit' ? 'btn-warning' : 'btn-primary' }} btn-lg px-4 shadow-sm">
                            <i class="fas {{ $mode === 'edit' ? 'fa-save' : 'fa-plus' }} me-2"></i>
                            {{ $mode === 'edit' ? 'Сохранить изменения' : 'Создать книгу' }}
                        </button>
                        <a href="{{ route('books.index') }}" class="btn btn-outline-secondary btn-lg px-4 shadow-sm">
                            <i class="fas fa-arrow-left me-2"></i>Отмена
                        </a>
                    </div>
                    <small class="text-muted d-block mt-3">
                        <i class="fas fa-info-circle me-1"></i>
                        Проверьте корректность данных перед сохранением
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .form-control:focus, .form-select:focus {
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
        border-color: #86b7fe;
    }

    .input-group-text {
        background-color: #f8f9fa;
        border-right: none;
    }

    .card {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .card:hover {
        transform: translateY(-2px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    }

    .required::after {
        content: " *";
        color: #dc3545;
        font-weight: bold;
    }

    @media (prefers-reduced-motion: reduce) {
        .card {
            transition: none;
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Подсветка обязательных полей при фокусе
        const requiredFields = document.querySelectorAll('input[required], select[required], textarea[required]');
        requiredFields.forEach((field) => {
            field.addEventListener('focus', (e) => {
                const el = e.currentTarget instanceof HTMLElement ? e.currentTarget : null;
                if (!el) return;
                const group = el.closest('.mb-3, .mb-4, .card-body');
                if (group instanceof HTMLElement) {
                    group.classList.add('bg-light-subtle');
                }
            });
            field.addEventListener('blur', (e) => {
                const el = e.currentTarget instanceof HTMLElement ? e.currentTarget : null;
                if (!el) return;
                const group = el.closest('.mb-3, .mb-4, .card-body');
                if (group instanceof HTMLElement) {
                    group.classList.remove('bg-light-subtle');
                }
            });
        });

        // Автофокус на первое поле при загрузке
        const firstField = document.querySelector('input[name="title"]');
        if (firstField instanceof HTMLInputElement) {
            firstField.focus();
        }

        // Проверка длины описания
        const descriptionField = document.getElementById('description');
        const descriptionHelp = document.getElementById('descriptionHelp');

        const updateDescriptionValidity = () => {
            if (!(descriptionField instanceof HTMLTextAreaElement)) return;
            const len = descriptionField.value.trim().length;
            const invalid = (len > 0 && len < 50);
            descriptionField.classList.toggle('is-invalid', invalid);
            if (descriptionHelp) {
                descriptionHelp.classList.toggle('text-danger', invalid);
            }
        };

        if (descriptionField instanceof HTMLTextAreaElement) {
            descriptionField.addEventListener('input', updateDescriptionValidity);
            updateDescriptionValidity();
        }

        // Тёмная тема
        if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
            document.documentElement.setAttribute('data-bs-theme', 'dark');
        }
    });
</script>
