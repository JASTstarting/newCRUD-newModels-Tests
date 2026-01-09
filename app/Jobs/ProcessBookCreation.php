<?php

namespace App\Jobs;

use App\Models\Book;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessBookCreation implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $bookId;

    public function __construct(int $bookId)
    {
        $this->bookId = $bookId;
    }

    public function handle(): void
    {
        sleep(2);

        $book = Book::query()->find($this->bookId);
        if ($book) {
            Log::info("ProcessBookCreation завершён для книги #$book->id");
        }
    }
}
