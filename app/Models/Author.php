<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property string $first_name
 * @property string $last_name
 * @property string $father_name
 * @property string|null $birth_date
 * @property string|null $biography
 * @property bool $gender
 * @property int $active
 * @property string $created_at
 * @property string $updated_at
 * @property string|null $deleted_at
 */
class Author extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'first_name',
        'last_name',
        'father_name',
        'birth_date',
        'biography',
        'gender',
        'active',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'gender'     => 'boolean',
        'active'     => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function books(): HasMany
    {
        return $this->hasMany(Book::class);
    }

    protected static function boot(): void
    {
        parent::boot();

        static::deleting(function (Author $author): void {
            if (! $author->isForceDeleting()) {
                $author->books()->delete();
            }
        });
    }

}
