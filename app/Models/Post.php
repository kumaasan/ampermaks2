<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property string $title
 * @property string $slug
 * @property string|null $excerpt
 * @property array<string, mixed> $content_json
 * @property string $content_html
 * @property int $content_schema_version
 * @property string $status
 * @property Carbon|null $published_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User $author
 */
#[Fillable([
    'user_id',
    'title',
    'slug',
    'excerpt',
    'content_json',
    'content_html',
    'content_schema_version',
    'status',
    'published_at',
])]
class Post extends Model
{
    protected static function booted(): void
    {
        static::deleting(function (Post $post): void {
            $post->images()->get()->each->delete();
        });
    }

    /** @return BelongsTo<User, $this> */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /** @return HasMany<PostImage, $this> */
    public function images(): HasMany
    {
        return $this->hasMany(PostImage::class);
    }

    /** @param Builder<Post> $query */
    public function scopePublished(Builder $query): Builder
    {
        return $query
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'content_json' => 'array',
            'published_at' => 'datetime',
        ];
    }
}
