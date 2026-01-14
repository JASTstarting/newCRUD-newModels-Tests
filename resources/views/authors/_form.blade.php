<div class="row g-3">
    <div class="col-md-8">
        <div class="card mb-4 border-0 shadow-sm">
            <div class="card-header bg-light py-3">
                <h5 class="mb-0 fw-bold">
                    <i class="fas fa-user me-2 text-primary"></i>Основная информация
                </h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="last_name" class="form-label required fw-medium">Фамилия</label>
                            <input type="text"
                                   id="last_name"
                                   name="last_name"
                                   class="form-control form-control-lg @error('last_name') is-invalid @enderror"
                                   value="{{ old('last_name', $author->last_name ?? '') }}"
                                   required
                                   minlength="2"
                                   aria-required="true"
                                   aria-describedby="last_name_help">
                            <div id="last_name_help" class="form-text text-muted small">
                                Обязательное поле, минимум 2 символа
                            </div>
                            @error('last_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="first_name" class="form-label required fw-medium">Имя</label>
                            <input type="text"
                                   id="first_name"
                                   name="first_name"
                                   class="form-control form-control-lg @error('first_name') is-invalid @enderror"
                                   value="{{ old('first_name', $author->first_name ?? '') }}"
                                   required
                                   minlength="2"
                                   aria-required="true"
                                   aria-describedby="first_name_help">
                            <div id="first_name_help" class="form-text text-muted small">
                                Обязательное поле, минимум 2 символа
                            </div>
                            @error('first_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="father_name" class="form-label required fw-medium">Отчество</label>
                            <input type="text"
                                   id="father_name"
                                   name="father_name"
                                   class="form-control form-control-lg @error('father_name') is-invalid @enderror"
                                   value="{{ old('father_name', $author->father_name ?? '') }}"
                                   required
                                   minlength="2"
                                   aria-required="true"
                                   aria-describedby="father_name_help">
                            <div id="father_name_help" class="form-text text-muted small">
                                Обязательное поле, минимум 2 символа
                            </div>
                            @error('father_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="birth_date" class="form-label fw-medium">Дата рождения</label>
                            <input type="date"
                                   id="birth_date"
                                   name="birth_date"
                                   class="form-control form-control-lg @error('birth_date') is-invalid @enderror"
                                   value="{{ old('birth_date', isset($author) && $author->birth_date ? $author->birth_date->format('Y-m-d') : '') }}"
                                   max="{{ date('Y-m-d') }}"
                                   aria-describedby="birth_date_help">
                            <div id="birth_date_help" class="form-text text-muted small">
                                Не обязательно для заполнения
                            </div>
                            @error('birth_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-medium d-block required">Пол</label>
                            <div class="mt-2">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input"
                                           type="radio"
                                           name="gender"
                                           id="gender_male"
                                           value="1"
                                           {{ old('gender', $author->gender ?? 1) == 1 ? 'checked' : '' }}
                                           required
                                           aria-required="true">
                                    <label class="form-check-label" for="gender_male">
                                        <i class="fas fa-mars me-1 text-primary"></i>Мужской
                                    </label>
                                </div>
                                <div class="form-check form-check-inline ms-3">
                                    <input class="form-check-input"
                                           type="radio"
                                           name="gender"
                                           id="gender_female"
                                           value="0"
                                           {{ old('gender', $author->gender ?? 1) == 0 ? 'checked' : '' }}
                                           required
                                           aria-required="true">
                                    <label class="form-check-label" for="gender_female">
                                        <i class="fas fa-venus me-1 text-danger"></i>Женский
                                    </label>
                                </div>
                            </div>
                            @error('gender')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="biography" class="form-label fw-medium">Биография</label>
                    <textarea id="biography"
                              name="biography"
                              class="form-control form-control-lg @error('biography') is-invalid @enderror"
                              rows="4"
                              maxlength="5000"
                              placeholder="Расскажите о достижениях автора..."
                              aria-describedby="biography_help">{{ old('biography', $author->biography ?? '') }}</textarea>
                    <div class="d-flex justify-content-between">
                        <small class="text-muted" id="biography_help">Максимум 5000 символов</small>
                        <small class="text-muted" id="biography_counter">{{ strlen(old('biography', $author->biography ?? '')) }}/5000</small>
                    </div>
                    @error('biography')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card mb-4 border-0 shadow-sm">
            <div class="card-header bg-light py-3">
                <h5 class="mb-0 fw-bold">
                    <i class="fas fa-cogs me-2 text-primary"></i>Дополнительно
                </h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="form-check form-switch">
                        <input type="hidden" name="active" value="0">
                        <input class="form-check-input"
                               type="checkbox"
                               role="switch"
                               id="active"
                               name="active"
                               value="1"
                               {{ old('active', $author->active ?? true) ? 'checked' : '' }}
                               aria-label="Статус автора">
                        <label class="form-check-label fw-medium" for="active">
                            <i class="fas fa-toggle-on me-1"></i>Активен
                        </label>
                    </div>
                    <small class="text-muted d-block mt-2">
                        <i class="fas fa-info-circle me-1"></i>Неактивные авторы не отображаются в публичных списках и в поиске
                    </small>
                </div>
            </div>
        </div>
        <div class="sticky-md-top" style="top: 20px;">
            <div class="d-grid gap-3">
                <button type="submit" id="submitBtn" class="btn {{ isset($author) ? 'btn-warning' : 'btn-primary' }} btn-lg px-4 shadow-sm">
                    <i class="fas {{ isset($author) ? 'fa-save' : 'fa-plus' }} me-2"></i>
                    {{ isset($author) ? 'Сохранить изменения' : 'Создать автора' }}
                </button>
                <a href="{{ route('authors.index') }}" class="btn btn-outline-secondary btn-lg px-4 shadow-sm">
                    <i class="fas fa-arrow-left me-2"></i>Отмена
                </a>
            </div>
            @if(isset($author) && $author->trashed())
                <div class="alert alert-warning mt-3">
                    <i class="fas fa-exclamation-triangle me-1"></i>
                    Этот автор находится в корзине. Изменения восстановят его.
                </div>
            @endif
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        // Счётчик символов для биографии
        const bioTextarea = document.getElementById('biography');
        const bioCounter  = document.getElementById('biography_counter');

        if (bioCounter && bioTextarea instanceof HTMLTextAreaElement) {
            const updateCounter = () => {
                bioCounter.textContent = `${bioTextarea.value.length}/5000`;
            };
            bioTextarea.addEventListener('input', updateCounter);
            updateCounter();
        }

        // Валидация формы перед отправкой
        const form = document.querySelector('form[id^="author"]') || document.querySelector('form');
        const submitBtn = document.getElementById('submitBtn');

        if (form && submitBtn) {
            form.addEventListener('submit', (e) => {
                const lastNameEl   = document.getElementById('last_name');
                const firstNameEl  = document.getElementById('first_name');
                const fatherNameEl = document.getElementById('father_name');

                let isValid = true;
                let errorMessage = '';

                const validateField = (el, label) => {
                    if (!el) return;
                    const val = el.value.trim();
                    if (val.length < 2) {
                        isValid = false;
                        errorMessage += `${label} должна содержать минимум 2 символа\n`;
                        el.classList.add('is-invalid');
                    } else {
                        el.classList.remove('is-invalid');
                    }
                };

                validateField(lastNameEl,  'Фамилия');
                validateField(firstNameEl, 'Имя');
                validateField(fatherNameEl,'Отчество');

                if (!isValid) {
                    e.preventDefault();

                    const errorDiv = document.createElement('div');
                    errorDiv.className = 'alert alert-danger alert-dismissible fade show mt-3';
                    errorDiv.innerHTML = `
            <i class="fas fa-exclamation-triangle me-2"></i>
            <strong>Ошибка валидации:</strong>
            <div class="mt-2">${errorMessage.replace(/\n/g, '<br>')}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          `;

                    if (submitBtn.parentNode) {
                        submitBtn.parentNode.insertBefore(errorDiv, submitBtn);
                    }

                    const firstInvalid = form.querySelector('.is-invalid');
                    if (firstInvalid instanceof HTMLElement) {
                        firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        firstInvalid.focus();
                    }

                    // Автоматическое закрытие через 5 секунд — без использования Bootstrap API
                    setTimeout(() => {
                        const alertEl = document.querySelector('.alert-danger');
                        if (alertEl) {
                            // Попытаемся "нажать" на кнопку закрытия (если подключён Bootstrap — закроется с анимацией)
                            const closeBtn = alertEl.querySelector('[data-bs-dismiss="alert"]');
                            if (closeBtn instanceof HTMLElement) closeBtn.click();

                            // Фолбэк: если Bootstrap не подключён и элемент всё ещё на странице — удалим его вручную
                            setTimeout(() => {
                                if (alertEl.isConnected) {
                                    alertEl.remove();
                                }
                            }, 50);
                        }
                    }, 5000);
                }
            });
        }

        // Поддержка тёмной темы
        if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
            document.documentElement.setAttribute('data-bs-theme', 'dark');
        }

        // Минимальная длина для HTML5-валидации
        const lastNameField = document.getElementById('last_name');
        const firstNameField = document.getElementById('first_name');
        const fatherNameField = document.getElementById('father_name');

        if (lastNameField) lastNameField.minLength = 2;
        if (firstNameField) firstNameField.minLength = 2;
        if (fatherNameField) fatherNameField.minLength = 2;
    });
</script>
