<div class="row g-3">
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0">Основная информация</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="last_name" class="form-label required">Фамилия</label>
                            <input type="text"
                                   id="last_name"
                                   name="last_name"
                                   class="form-control @error('last_name') is-invalid @enderror"
                                   value="{{ old('last_name', $author->last_name ?? '') }}"
                                   required>
                            @error('last_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="first_name" class="form-label required">Имя</label>
                            <input type="text"
                                   id="first_name"
                                   name="first_name"
                                   class="form-control @error('first_name') is-invalid @enderror"
                                   value="{{ old('first_name', $author->first_name ?? '') }}"
                                   required>
                            @error('first_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="father_name" class="form-label required">Отчество</label>
                            <input type="text"
                                   id="father_name"
                                   name="father_name"
                                   class="form-control @error('father_name') is-invalid @enderror"
                                   value="{{ old('father_name', $author->father_name ?? '') }}"
                                   required>
                            @error('father_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="birth_date" class="form-label">Дата рождения</label>
                            <input type="date"
                                   id="birth_date"
                                   name="birth_date"
                                   class="form-control @error('birth_date') is-invalid @enderror"
                                   value="{{ old('birth_date', isset($author) && $author->birth_date ? $author->birth_date->format('Y-m-d') : '') }}"
                                   max="{{ date('Y-m-d') }}">
                            @error('birth_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label d-block required">Пол</label>
                            <div class="mt-2">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input"
                                           type="radio"
                                           name="gender"
                                           id="gender_male"
                                           value="1"
                                           {{ old('gender', $author->gender ?? 1) ? 'checked' : '' }} required>
                                    <label class="form-check-label" for="gender_male">
                                        <i class="fas fa-mars me-1"></i>Мужской
                                    </label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input"
                                           type="radio"
                                           name="gender"
                                           id="gender_female"
                                           value="0"
                                           {{ old('gender', $author->gender ?? 1) ? '' : 'checked' }} required>
                                    <label class="form-check-label" for="gender_female">
                                        <i class="fas fa-venus me-1"></i>Женский
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
                    <label for="biography" class="form-label">Биография</label>
                    <textarea id="biography"
                              name="biography"
                              class="form-control @error('biography') is-invalid @enderror"
                              rows="4"
                              maxlength="5000"
                              placeholder="Расскажите о достижениях автора...">{{ old('biography', $author->biography ?? '') }}</textarea>
                    <small class="text-muted">Максимум 5000 символов</small>
                    @error('biography')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0">Дополнительно</h5>
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
                            {{ old('active', $author->active ?? true) ? 'checked' : '' }}>
                        <label class="form-check-label fw-medium" for="active">
                            <i class="fas fa-toggle-on me-1"></i>Активен
                        </label>
                    </div>
                    <small class="text-muted d-block mt-1">
                        Неактивные авторы не отображаются в публичных списках
                    </small>
                </div>
            </div>
        </div>
        <div class="mt-3">
            <div class="d-grid gap-2">
                <button type="submit" class="btn {{ isset($author) ? 'btn-warning' : 'btn-primary' }} btn-lg">
                    <i class="fas {{ isset($author) ? 'fa-save' : 'fa-plus' }} me-1"></i>
                    {{ isset($author) ? 'Сохранить изменения' : 'Создать автора' }}
                </button>
                <a href="{{ route('authors.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-times me-1"></i>Отмена
                </a>
            </div>
        </div>
    </div>
</div>
