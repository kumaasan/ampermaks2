<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

/**
 * @property int $id
 * @property int $realization_id
 * @property string $disk
 * @property string $path
 * @property int $sort_order
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'realization_id',
    'disk',
    'path',
    'sort_order',
])]
class RealizationImage extends Model
{
    protected static function booted(): void
    {
        static::deleted(function (RealizationImage $image): void {
            $isUsedByGallery = static::query()
                ->where('disk', $image->disk)
                ->where('path', $image->path)
                ->exists();

            $isUsedAsCover = Realization::query()
                ->where('image_disk', $image->disk)
                ->where('image_path', $image->path)
                ->exists();

            if (! $isUsedByGallery && ! $isUsedAsCover) {
                Storage::disk($image->disk)->delete($image->path);
            }
        });
    }

    /** @return BelongsTo<Realization, $this> */
    public function realization(): BelongsTo
    {
        return $this->belongsTo(Realization::class);
    }

    public function url(): string
    {
        return Storage::disk($this->disk)->url($this->path);
    }
}
