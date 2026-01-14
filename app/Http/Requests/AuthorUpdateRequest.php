<?php

namespace App\Http\Requests;

use App\Models\Author;

class AuthorUpdateRequest extends AuthorBaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        parent::prepareForValidation();

        $author = $this->route('author');

        if ($author instanceof Author) {
            $this->excludeAuthor($author->id);
        } elseif (is_numeric($author)) {
            $this->excludeAuthor((int) $author);
        }
    }
}
