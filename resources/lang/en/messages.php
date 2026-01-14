<?php

return [
    'author' => [
        'created'            => 'Author successfully created',
        'updated'            => 'Author data successfully updated',
        'moved_to_trash'     => 'Author and all their books moved to trash',
        'already_deleted'    => 'This author has been deleted. Restore it to edit.',
        'create_failed'      => 'Failed to create author',
        'update_failed'      => 'Failed to update author data',
        'delete_failed'      => 'Failed to delete author',

        'restored'             => 'Author and all their deleted books have been restored',
        'force_delete_blocked' => 'Cannot delete author permanently. They have restored books.',
        'force_deleted'        => 'Author and all their deleted books have been permanently deleted',

        'duplicate'           => 'An author with the same full name and birth date already exists',
        'duplicate_in_trash'  => 'An author with the same full name and birth date already exists in trash. Restore or permanently delete it first.',
    ],

    'book' => [
        'created'                      => 'Book successfully created',
        'updated'                      => 'Book successfully updated',
        'moved_to_trash'               => 'Book moved to trash',
        'multiple_moved_to_trash'      => 'Selected books moved to trash',
        'multiple_moved_to_trash_count'=> 'Successfully deleted :count book(s)',
        'not_selected'                 => 'No books selected',
        'create_failed'                => 'Failed to create book',
        'update_failed'                => 'Failed to update book',
        'delete_failed'                => 'Failed to delete book',
        'already_deleted'              => 'This book has been deleted',

        'restored'                  => 'Book has been restored',
        'force_deleted'             => 'Book has been permanently deleted',
        'author_restored_with_book' => 'The author was automatically restored together with the book',
        'author_not_found'          => 'Cannot restore the book: related author not found',

        'duplicate'           => 'A book with the same title, author, and release year already exists',
        'duplicate_in_trash'  => 'A book with the same title, author, and release year already exists in trash. Restore or permanently delete it first.',
    ],

    'common' => [
        'not_found'        => 'Record not found',
        'error'            => 'An error occurred',
        'success'          => 'Operation completed successfully',
        'validation_error' => 'Validation error',
    ],

    'api' => [
        'disabled' => 'API is disabled',
    ],
];
