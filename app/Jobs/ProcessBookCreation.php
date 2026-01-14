<?php

namespace App\Jobs;

use App\Models\Book;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessBookCreation implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;             // Кол-во попыток
    public int $timeout = 10;            // Таймаут выполнения (сек)
    public array $backoff = [10, 30, 60]; // Паузы между ретраями (сек)

    public function __construct(
        public readonly int $bookId
    ) {}

    /**
     * Middleware очереди:
     * - WithoutOverlapping: чтобы не обрабатывать одновременно одну и ту же книгу.
     */
    public function middleware(): array
    {
        return [
            new WithoutOverlapping($this->bookId),
        ];
    }

    public function handle(): void
    {
        Log::info('ProcessBookCreation started', [
            'book_id' => $this->bookId,
        ]);

        $book = Book::query()->find($this->bookId);

        if (!$book) {
            Log::warning('ProcessBookCreation: book not found', [
                'book_id' => $this->bookId,
            ]);
            return;
        }


        Log::info('ProcessBookCreation finished', [
            'book_id'   => $book->id,
            'author_id' => $book->author_id,
            'company_id'=> $book->company_id,
        ]);
    }

    /**
     * Обработчик фатальных ошибок — будет вызван, когда job исчерпает tries.
     */
    public function failed(Exception $e): void
    {
        Log::error('ProcessBookCreation failed', [
            'book_id' => $this->bookId,
            'error'   => $e->getMessage(),
        ]);
    }
}
