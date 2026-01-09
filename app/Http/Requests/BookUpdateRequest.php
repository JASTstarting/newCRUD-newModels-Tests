<?php

namespace App\Http\Requests;

use App\Models\Book;

class BookUpdateRequest extends BookBaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $book = $this->route('book');

        if ($book instanceof Book) {
            $this->excludeBook($book->getKey());
        } elseif (is_numeric($book)) {
            $this->excludeBook((int) $book);
        }
    }
}
