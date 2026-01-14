<div class="d-flex gap-2">
    <form method="POST" action="{{ route('trash.books.restore', ['id' => $book->id]) }}" class="d-inline">
        @csrf
        <button class="btn btn-sm btn-outline-success" title="Восстановить книгу" aria-label="Восстановить книгу">
            <i class="fas fa-undo" aria-hidden="true"></i>
        </button>
    </form>
    <form method="POST" action="{{ route('trash.books.force-delete', ['id' => $book->id]) }}"
          class="d-inline"
          onsubmit="return confirm('Удалить книгу навсегда?')">
        @csrf
        @method('DELETE')
        <button class="btn btn-sm btn-outline-danger" title="Удалить книгу навсегда" aria-label="Удалить навсегда">
            <i class="fas fa-trash-alt" aria-hidden="true"></i>
        </button>
    </form>
</div>
