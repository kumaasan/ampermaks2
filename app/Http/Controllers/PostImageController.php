<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePostImageRequest;
use App\Models\PostImage;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Throwable;

class PostImageController extends Controller
{
    public function store(StorePostImageRequest $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user instanceof User, 403);

        $file = $request->file('image');

        if (! $file instanceof UploadedFile) {
            throw ValidationException::withMessages([
                'image' => 'Wybierz obraz do przesłania.',
            ]);
        }

        $dimensions = getimagesize($file->getRealPath());

        if ($dimensions === false || ($dimensions[0] * $dimensions[1]) > 24_000_000) {
            throw ValidationException::withMessages([
                'image' => 'Obraz może mieć maksymalnie 24 miliony pikseli.',
            ]);
        }

        $mimeType = $file->getMimeType();
        $extension = match ($mimeType) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            default => throw ValidationException::withMessages([
                'image' => 'Dozwolone są pliki JPG, PNG i WebP.',
            ]),
        };

        $id = (string) Str::ulid();
        $directory = 'blog/'.now()->format('Y/m');
        $path = Storage::disk('public')->putFileAs($directory, $file, "{$id}.{$extension}");

        if ($path === false) {
            throw new RuntimeException('Nie udało się zapisać obrazu.');
        }

        try {
            $image = PostImage::query()->create([
                'id' => $id,
                'user_id' => $user->getKey(),
                'disk' => 'public',
                'path' => $path,
                'original_name' => Str::limit($file->getClientOriginalName(), 255, ''),
                'mime_type' => $mimeType,
                'size' => $file->getSize(),
                'width' => $dimensions[0],
                'height' => $dimensions[1],
            ]);
        } catch (Throwable $exception) {
            Storage::disk('public')->delete($path);

            throw $exception;
        }

        return response()->json([
            'id' => $image->id,
            'url' => $image->url(),
            'width' => $image->width,
            'height' => $image->height,
        ], 201);
    }
}
