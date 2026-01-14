<?php

namespace App\Http\Requests;

use App\Models\Author;
use Carbon\Carbon;
use Exception;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

abstract class AuthorBaseRequest extends FormRequest
{
    protected ?int $excludeAuthorId = null;

    public function excludeAuthor(int $authorId): self
    {
        $this->excludeAuthorId = $authorId;
        return $this;
    }

    protected function prepareForValidation(): void
    {
        $first  = $this->input('first_name');
        $last   = $this->input('last_name');
        $father = $this->input('father_name');
        $birth  = $this->input('birth_date');

        $first  = is_string($first)  ? trim($first)  : $first;
        $last   = is_string($last)   ? trim($last)   : $last;
        $father = is_string($father) ? trim($father) : $father;

        $birthNormalized = null;
        if ($birth !== null && $birth !== '') {
            try {
                $birthNormalized = Carbon::parse($birth)->toDateString();
            } catch (Exception) {
                $birthNormalized = $birth;
            }
        }

        $this->merge([
            'first_name'  => $first,
            'last_name'   => $last,
            'father_name' => $father,
            'birth_date'  => $birthNormalized,
        ]);
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }
            $this->checkAuthorDuplicate($validator);
        });
    }

    protected function checkAuthorDuplicate(Validator $validator): void
    {
        $firstName  = (string) $this->input('first_name');
        $lastName   = (string) $this->input('last_name');
        $fatherName = (string) $this->input('father_name');
        $birthDate  = $this->input('birth_date');

        $query = Author::withTrashed()
            ->where('first_name', $firstName)
            ->where('last_name', $lastName)
            ->where('father_name', $fatherName);

        if (!empty($birthDate)) {
            try {
                $normalized = Carbon::parse($birthDate)->toDateString();
                $query->whereDate('birth_date', $normalized);
            } catch (Exception) {
            }
        } else {
            $query->whereNull('birth_date');
        }

        if ($this->excludeAuthorId) {
            $query->where('id', '!=', $this->excludeAuthorId);
        }

        $duplicate = $query->first();

        if ($duplicate) {
            $message = $duplicate->trashed()
                ? __('messages.author.duplicate_in_trash')
                : __('messages.author.duplicate');

            $validator->errors()->add('first_name', $message);
        }
    }

    public function rules(): array
    {
        return [
            'first_name'  => ['required', 'string', 'max:255'],
            'last_name'   => ['required', 'string', 'max:255'],
            'father_name' => ['required', 'string', 'max:255'],
            'birth_date'  => ['nullable', 'date', 'before:today'],
            'biography'   => ['nullable', 'string', 'max:5000'],
            'gender'      => ['required', 'boolean'],
            'active'      => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'first_name.required'  => 'Имя обязательно для заполнения.',
            'last_name.required'   => 'Фамилия обязательна для заполнения.',
            'father_name.required' => 'Отчество обязательно для заполнения.',
            'birth_date.date'      => 'Дата рождения должна быть корректной датой.',
            'birth_date.before'    => 'Дата рождения не может быть в будущем.',
            'gender.required'      => 'Пол обязателен для выбора.',
            'gender.boolean'       => 'Пол указан некорректно.',
        ];
    }
}
