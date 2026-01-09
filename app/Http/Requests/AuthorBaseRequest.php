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

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $this->checkAuthorDuplicate($validator);
        });
    }

    protected function checkAuthorDuplicate(Validator $validator): void
    {
        $firstName = $this->input('first_name');
        $lastName = $this->input('last_name');
        $fatherName = $this->input('father_name');
        $birthDate = $this->input('birth_date');

        // Проверяем сначала активных авторов (без корзины)
        $activeQuery = Author::where([
            'first_name' => $firstName,
            'last_name' => $lastName,
            'father_name' => $fatherName,
        ]);

        if ($this->excludeAuthorId) {
            $activeQuery->where('id', '!=', $this->excludeAuthorId);
        }

        if ($birthDate && trim($birthDate) !== '') {
            try {
                $formattedDate = Carbon::parse($birthDate)->format('Y-m-d');
                $activeQuery->whereDate('birth_date', $formattedDate);
            } catch (Exception) {
            }
        } else {
            $activeQuery->whereNull('birth_date');
        }

        // Проверяем авторов в корзине
        $trashedQuery = Author::onlyTrashed()->where([
            'first_name' => $firstName,
            'last_name' => $lastName,
            'father_name' => $fatherName,
        ]);

        if ($this->excludeAuthorId) {
            $trashedQuery->where('id', '!=', $this->excludeAuthorId);
        }

        if ($birthDate && trim($birthDate) !== '') {
            try {
                $formattedDate = Carbon::parse($birthDate)->format('Y-m-d');
                $trashedQuery->whereDate('birth_date', $formattedDate);
            } catch (Exception) {
            }
        } else {
            $trashedQuery->whereNull('birth_date');
        }

        // Сначала проверяем активных авторов
        if ($activeQuery->exists()) {
            $validator->errors()->add('first_name', 'Автор с такими ФИО и датой рождения уже существует');
        }
        // Если активных нет, но есть в корзине
        elseif ($trashedQuery->exists()) {
            $validator->errors()->add('first_name', 'Автор с такими ФИО и датой рождения уже существует, но находится в корзине. Сначала восстановите или удалите его.');
        }
    }

    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name'  => ['required', 'string', 'max:255'],
            'father_name'=> ['required', 'string', 'max:255'],
            'birth_date' => ['nullable', 'date', 'before:today'],
            'biography'  => ['nullable', 'string', 'max:5000'],
            'gender'     => ['required', 'boolean'],
            'active'     => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'first_name.required' => 'Имя обязательно для заполнения.',
            'last_name.required'  => 'Фамилия обязательна для заполнения.',
            'father_name.required'=> 'Отчество обязательно для заполнения.',
            'birth_date.date'     => 'Дата рождения должна быть корректной датой.',
            'birth_date.before'   => 'Дата рождения не может быть в будущем.',
            'gender.required'     => 'Пол обязателен для выбора.',
            'gender.boolean'      => 'Пол указан некорректно.',
        ];
    }
}
