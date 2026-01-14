<?php

namespace App\Http\Requests;

use App\Models\Book;
use Carbon\Carbon;
use Exception;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

abstract class BookBaseRequest extends FormRequest
{
    protected ?int $excludeBookId = null;

    public function excludeBook(int $bookId): self
    {
        $this->excludeBookId = $bookId;
        return $this;
    }

    protected function prepareForValidation(): void
    {
        $title       = $this->input('title');
        $description = $this->input('description');
        $created     = $this->input('created_date');
        $authorId    = $this->input('author_id');
        $companyId   = $this->input('company_id');

        $title       = is_string($title) ? trim($title) : $title;
        $description = is_string($description) ? trim($description) : $description;

        $createdNormalized = $created;
        if ($created !== null && $created !== '') {
            try {
                $createdNormalized = Carbon::parse($created)->toDateString();
            } catch (Exception) {
            }
        }

        $this->merge([
            'title'         => $title,
            'description'   => $description,
            'created_date'  => $createdNormalized,
            'author_id'     => is_numeric($authorId) ? (int) $authorId : $authorId,
            'company_id'    => is_numeric($companyId) ? (int) $companyId : $companyId,
        ]);
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }
            $this->checkBookDuplicate($validator);
        });
    }

    protected function checkBookDuplicate(Validator $validator): void
    {
        $title      = (string) $this->input('title');
        $authorId   = $this->input('author_id');
        $created    = $this->input('created_date');

        try {
            $year = Carbon::parse($created)->year;
        } catch (Exception) {
            return;
        }

        $query = Book::withTrashed()
            ->where('title', $title)
            ->where('author_id', $authorId)
            ->whereYear('created_date', $year);

        if ($this->excludeBookId) {
            $query->where('id', '!=', $this->excludeBookId);
        }

        $duplicate = $query->first();

        if ($duplicate) {
            $message = $duplicate->trashed()
                ? __('messages.book.duplicate_in_trash')
                : __('messages.book.duplicate');

            $validator->errors()->add('title', $message);
        }
    }

    public function rules(): array
    {
        return [
            'title'         => ['required', 'string', 'max:255'],
            'description'   => ['required', 'string', 'max:5000'],
            'created_date'  => ['required', 'date'],
            'author_id'     => [
                'required',
                'integer',
                Rule::exists('authors', 'id')->whereNull('deleted_at'),
            ],
            'company_id'    => ['required', 'integer', Rule::exists('companies', 'id')],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required'         => 'Название книги обязательно.',
            'description.required'   => 'Описание обязательно.',
            'created_date.required'  => 'Дата создания обязательна.',
            'created_date.date'      => 'Неверный формат даты.',
            'author_id.required'     => 'Не выбран автор.',
            'author_id.exists'       => 'Выбран несуществующий или удалённый автор.',
            'company_id.required'    => 'Не выбрано издательство.',
            'company_id.exists'      => 'Выбрано несуществующее издательство.',
        ];
    }
}
