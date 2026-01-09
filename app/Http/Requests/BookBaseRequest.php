<?php

namespace App\Http\Requests;

use App\Models\Book;
use Carbon\Carbon;
use Exception;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

abstract class BookBaseRequest extends FormRequest
{
    protected ?int $excludeBookId = null;

    public function excludeBook(int $bookId): self
    {
        $this->excludeBookId = $bookId;
        return $this;
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $this->checkBookDuplicate($validator);
        });
    }

    protected function checkBookDuplicate(Validator $validator): void
    {
        $name = $this->input('name');
        $authorId = $this->input('author_id');
        $createdDate = $this->input('created_date');

        try {
            $year = Carbon::parse($createdDate)->year;
        } catch (Exception) {
            return;
        }

        $activeQuery = Book::query()->where([
            'name' => $name,
            'author_id' => $authorId,
        ])->whereYear('created_date', $year);

        if ($this->excludeBookId) {
            $activeQuery->where('id', '!=', $this->excludeBookId);
        }

        $trashedQuery = Book::onlyTrashed()->where([
            'name' => $name,
            'author_id' => $authorId,
        ])->whereYear('created_date', $year);

        if ($this->excludeBookId) {
            $trashedQuery->where('id', '!=', $this->excludeBookId);
        }

        if ($activeQuery->exists()) {
            $validator->errors()->add('name', 'Книга с таким названием, автором и годом выпуска уже существует');
        }
        elseif ($trashedQuery->exists()) {
            $validator->errors()->add('name', 'Книга с таким названием, автором и годом выпуска уже существует, но находится в корзине. Сначала восстановите или удалите её.');
        }
    }

    public function rules(): array
    {
        return [
            'name'         => ['required', 'string', 'max:255'],
            'description'  => ['required', 'string', 'max:5000'],
            'created_date' => ['required', 'date'],
            'author_id'    => ['required', 'integer', 'exists:authors,id'],
            'company_id'   => ['required', 'integer', 'exists:companies,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'         => 'Название книги обязательно.',
            'description.required'  => 'Описание обязательно.',
            'created_date.required' => 'Дата создания обязательна.',
            'created_date.date'     => 'Неверный формат даты.',
            'author_id.required'    => 'Не выбран автор.',
            'author_id.exists'      => 'Выбран несуществующий автор.',
            'company_id.required'   => 'Не выбрано издательство.',
            'company_id.exists'     => 'Выбрано несуществующее издательство.',
        ];
    }
}
