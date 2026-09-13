<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRealizationImagesRequest;
use App\Http\Requests\StoreRealizationRequest;
use App\Models\Realization;
use App\Models\RealizationImage;
use App\Services\RealizationImageStorage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class RealizationController extends Controller
{
    public function index(): Response
    {
        $realizations = Realization::query()
            ->select(['id', 'title', 'description', 'image_disk', 'image_path', 'created_at'])
            ->withCount('images')
            ->latest()
            ->latest('id')
            ->paginate(12)
            ->through(fn (Realization $realization): array => [
                'id' => $realization->id,
                'title' => $realization->title,
                'description' => $realization->description,
                'image_url' => $realization->imageUrl(),
                'images_count' => 1 + $realization->images_count,
                'created_at' => $realization->created_at?->toISOString(),
                'edit_url' => route('dashboard.realizations.edit', $realization),
                'delete_url' => route('dashboard.realizations.destroy', $realization),
            ]);

        return Inertia::render('ManageRealizations', [
            'realizations' => $realizations,
            'createUrl' => route('dashboard.realizations.create'),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('CreateRealization', [
            'storeUrl' => route('dashboard.realizations.store'),
            'indexUrl' => route('dashboard.realizations.index'),
            'maxUploadSizeMb' => RealizationImageStorage::MAX_UPLOAD_SIZE_MB,
            'maxImagesPerUpload' => RealizationImageStorage::MAX_FILES_PER_UPLOAD,
            'allowedImageTypes' => RealizationImageStorage::ALLOWED_MIME_TYPES,
        ]);
    }

    public function store(
        StoreRealizationRequest $request,
        RealizationImageStorage $imageStorage,
    ): RedirectResponse
    {
        $images = $this->uploadedImages($request);

        if ($images === []) {
            throw ValidationException::withMessages([
                'images' => 'Wybierz co najmniej jedno zdjęcie realizacji.',
            ]);
        }

        /** @var list<array{disk: string, path: string}> $storedImages */
        $storedImages = [];

        try {
            foreach ($images as $index => $image) {
                $storedImages[] = $imageStorage->store($image, "images.{$index}");
            }

            /** @var array{title: string, description: string} $validated */
            $validated = $request->safe()->only(['title', 'description']);

            DB::transaction(function () use ($validated, $storedImages): void {
                $cover = $storedImages[0];
                $realization = Realization::query()->create([
                    ...$validated,
                    'image_disk' => $cover['disk'],
                    'image_path' => $cover['path'],
                ]);

                foreach (array_slice($storedImages, 1) as $index => $storedImage) {
                    $realization->images()->create([
                        ...$storedImage,
                        'sort_order' => $index,
                    ]);
                }
            });
        } catch (Throwable $exception) {
            foreach ($storedImages as $storedImage) {
                Storage::disk($storedImage['disk'])->delete($storedImage['path']);
            }

            throw $exception;
        }

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Realizacja została dodana.',
        ]);

        return to_route('dashboard.realizations.index');
    }

    public function edit(Realization $realization): Response
    {
        $realization->load('images');

        return Inertia::render('ManageRealizationGallery', [
            'realization' => [
                'id' => $realization->id,
                'title' => $realization->title,
                'description' => $realization->description,
                'cover' => [
                    'url' => $realization->imageUrl(),
                    'delete_url' => $realization->images->isEmpty()
                        ? null
                        : route('dashboard.realizations.cover.destroy', $realization),
                ],
                'images' => $realization->images->map(fn (RealizationImage $image): array => [
                    'id' => $image->id,
                    'url' => $image->url(),
                    'sort_order' => $image->sort_order,
                    'delete_url' => route('dashboard.realizations.images.destroy', [
                        'realization' => $realization,
                        'image' => $image,
                    ]),
                ]),
            ],
            'storeImagesUrl' => route('dashboard.realizations.images.store', $realization),
            'indexUrl' => route('dashboard.realizations.index'),
            'maxUploadSizeMb' => RealizationImageStorage::MAX_UPLOAD_SIZE_MB,
            'maxImagesPerUpload' => RealizationImageStorage::MAX_FILES_PER_UPLOAD,
            'allowedImageTypes' => RealizationImageStorage::ALLOWED_MIME_TYPES,
        ]);
    }

    public function storeImages(
        StoreRealizationImagesRequest $request,
        Realization $realization,
        RealizationImageStorage $imageStorage,
    ): RedirectResponse
    {
        $images = $this->uploadedImages($request);

        if ($images === []) {
            throw ValidationException::withMessages([
                'images' => 'Wybierz co najmniej jedno zdjęcie.',
            ]);
        }

        $lastSortOrder = RealizationImage::query()
            ->where('realization_id', $realization->id)
            ->max('sort_order');
        $nextSortOrder = is_numeric($lastSortOrder) ? ((int) $lastSortOrder) + 1 : 0;

        /** @var list<array{disk: string, path: string}> $storedImages */
        $storedImages = [];

        try {
            foreach ($images as $index => $image) {
                $storedImages[] = $imageStorage->store($image, "images.{$index}");
            }

            DB::transaction(function () use ($realization, $storedImages, $nextSortOrder): void {
                foreach ($storedImages as $index => $storedImage) {
                    $realization->images()->create([
                        ...$storedImage,
                        'sort_order' => $nextSortOrder + $index,
                    ]);
                }
            });
        } catch (Throwable $exception) {
            foreach ($storedImages as $storedImage) {
                Storage::disk($storedImage['disk'])->delete($storedImage['path']);
            }

            throw $exception;
        }

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => count($storedImages) === 1
                ? 'Zdjęcie zostało dodane do galerii.'
                : 'Zdjęcia zostały dodane do galerii.',
        ]);

        return to_route('dashboard.realizations.edit', $realization);
    }

    public function destroyImage(
        Realization $realization,
        RealizationImage $image,
    ): RedirectResponse
    {
        abort_unless($image->realization_id === $realization->id, 404);

        $image->delete();

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Zdjęcie zostało usunięte z galerii.',
        ]);

        return to_route('dashboard.realizations.edit', $realization);
    }

    public function destroyCover(
        Realization $realization,
        RealizationImageStorage $imageStorage,
    ): RedirectResponse
    {
        $replacement = $realization->images()->first();

        if (! $replacement instanceof RealizationImage) {
            Inertia::flash('toast', [
                'type' => 'error',
                'message' => 'Najpierw dodaj inne zdjęcie, które zastąpi okładkę.',
            ]);

            return to_route('dashboard.realizations.edit', $realization);
        }

        $oldDisk = $realization->image_disk;
        $oldPath = $realization->image_path;

        DB::transaction(function () use ($realization, $replacement): void {
            $realization->update([
                'image_disk' => $replacement->disk,
                'image_path' => $replacement->path,
            ]);

            $replacement->delete();
        });

        $imageStorage->deleteIfUnused($oldDisk, $oldPath);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Okładka została usunięta, a kolejne zdjęcie zajęło jej miejsce.',
        ]);

        return to_route('dashboard.realizations.edit', $realization);
    }

    public function destroy(Realization $realization): RedirectResponse
    {
        $realization->delete();

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Realizacja została usunięta.',
        ]);

        return to_route('dashboard.realizations.index');
    }

    /** @return list<UploadedFile> */
    private function uploadedImages(
        StoreRealizationRequest|StoreRealizationImagesRequest $request,
    ): array
    {
        $files = $request->file('images');

        if ($files instanceof UploadedFile) {
            return [$files];
        }

        if (is_array($files)) {
            return array_values($files);
        }

        $legacyImage = $request->file('image');

        return $legacyImage instanceof UploadedFile ? [$legacyImage] : [];
    }
}
