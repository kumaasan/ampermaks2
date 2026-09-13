<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
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
        static::deleted(function (Realization $realization): void {
            $imageIsUsedElsewhere = static::query()
                ->where('image_disk', $realization->image_disk)
                ->where('image_path', $realization->image_path)
                ->exists();

            if ($imageIsUsedElsewhere) {
                return;
            }

            Storage::disk($realization->image_disk)->delete($realization->image_path);
        });
    }

    public function imageUrl(): string
    {
        return Storage::disk($this->image_disk)->url($this->image_path);
    }
}
