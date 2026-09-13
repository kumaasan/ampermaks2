<?php

namespace App\Services;

use App\Models\Realization;
use App\Models\RealizationImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class RealizationImageStorage
{
    public const DISK = 'public';

    public const MAX_FILES_PER_UPLOAD = 10;

    public const MAX_UPLOAD_SIZE_MB = 8;

    /** @var list<string> */
    public const ALLOWED_MIME_TYPES = ['image/jpeg', 'image/png', 'image/webp'];

    /** @return array{disk: string, path: string} */
    public function store(UploadedFile $image, string $attribute): array
    {
        $realPath = $image->getRealPath();
        $dimensions = $realPath === false ? false : getimagesize($realPath);

        if ($dimensions === false || ($dimensions[0] * $dimensions[1]) > 24_000_000) {
            throw ValidationException::withMessages([
                $attribute => 'Zdjęcie może mieć maksymalnie 24 miliony pikseli.',
            ]);
        }

        $extension = match ($image->getMimeType()) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            default => throw ValidationException::withMessages([
                $attribute => 'Dozwolone są pliki JPG, PNG i WebP.',
            ]),
        };

        $filename = ((string) Str::ulid()).'.'.$extension;
        $directory = 'realizations/'.now()->format('Y/m');
        $path = Storage::disk(self::DISK)->putFileAs($directory, $image, $filename);

        if ($path === false) {
            throw ValidationException::withMessages([
                $attribute => 'Nie udało się zapisać zdjęcia. Spróbuj ponownie.',
            ]);
        }

        return [
            'disk' => self::DISK,
            'path' => $path,
        ];
    }

    public function deleteIfUnused(string $disk, string $path): void
    {
        $isUsedAsCover = Realization::query()
            ->where('image_disk', $disk)
            ->where('image_path', $path)
            ->exists();

        $isUsedInGallery = RealizationImage::query()
            ->where('disk', $disk)
            ->where('path', $path)
            ->exists();

        if (! $isUsedAsCover && ! $isUsedInGallery) {
            Storage::disk($disk)->delete($path);
        }
    }
}
