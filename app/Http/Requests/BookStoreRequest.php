<?php

namespace App\Http\Requests;

class BookStoreRequest extends BookBaseRequest
{
    public function authorize(): bool
    {
        return true;
    }
}
