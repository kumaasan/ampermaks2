<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

/**
 * @property string $id
 * @property int $user_id
 * @property int|null $post_id
 * @property string $disk
 * @property string $path
 * @property string $original_name
 * @property string $mime_type
 * @property int $size
 * @property int $width
 * @property int $height
 * @property Carbon|null $attached_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'id',
    'user_id',
    'post_id',
    'disk',
    'path',
    'original_name',
    'mime_type',
    'size',
    'width',
    'height',
    'attached_at',
])]
class PostImage extends Model
{
    public $incrementing = false;

    protected $keyType = 'string';

    protected static function booted(): void
    {
        static::deleted(function (PostImage $image): void {
            Storage::disk($image->disk)->delete($image->path);
        });
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<Post, $this> */
    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    public function url(): string
    {
        return Storage::disk($this->disk)->url($this->path);
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'attached_at' => 'datetime',
        ];
    }
}
