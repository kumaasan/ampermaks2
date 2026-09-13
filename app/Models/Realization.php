<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

/**
 * @property int $id
 * @property string $title
 * @property string $description
 * @property string $image_disk
 * @property string $image_path
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property int $images_count
 */
#[Fillable([
    'title',
    'description',
    'image_disk',
    'image_path',
])]
class Realization extends Model
{
    protected static function booted(): void
    {
        static::deleting(function (Realization $realization): void {
            $realization->images()->get()->each->delete();
        });

        static::deleted(function (Realization $realization): void {
            $imageIsUsedAsAnotherCover = static::query()
                ->where('image_disk', $realization->image_disk)
                ->where('image_path', $realization->image_path)
                ->exists();

            $imageIsUsedInGallery = RealizationImage::query()
                ->where('disk', $realization->image_disk)
                ->where('path', $realization->image_path)
                ->exists();

            if ($imageIsUsedAsAnotherCover || $imageIsUsedInGallery) {
                return;
            }

            Storage::disk($realization->image_disk)->delete($realization->image_path);
        });
    }

    public function imageUrl(): string
    {
        return Storage::disk($this->image_disk)->url($this->image_path);
    }

    /** @return HasMany<RealizationImage, $this> */
    public function images(): HasMany
    {
        return $this->hasMany(RealizationImage::class)
            ->orderBy('sort_order')
            ->orderBy('id');
    }
}
