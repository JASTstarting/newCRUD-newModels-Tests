<?php

namespace App\Http\Requests;

class AuthorStoreRequest extends AuthorBaseRequest
{
    public function authorize(): bool
    {
        return true;
    }
}
