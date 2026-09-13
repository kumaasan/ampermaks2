<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRealizationRequest;
use App\Models\Realization;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class RealizationController extends Controller
{
    public function index(): Response
    {
        $realizations = Realization::query()
            ->latest()
            ->latest('id')
            ->paginate(12)
            ->through(fn (Realization $realization): array => [
                'id' => $realization->id,
                'title' => $realization->title,
                'description' => $realization->description,
                'image_url' => $realization->imageUrl(),
                'created_at' => $realization->created_at?->toISOString(),
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
            'maxUploadSizeMb' => 8,
            'allowedImageTypes' => ['image/jpeg', 'image/png', 'image/webp'],
        ]);
    }

    public function store(StoreRealizationRequest $request): RedirectResponse
    {
        $image = $request->file('image');

        if (! $image instanceof UploadedFile) {
            throw ValidationException::withMessages([
                'image' => 'Wybierz zdjęcie realizacji.',
            ]);
        }

        $dimensions = getimagesize($image->getRealPath());

        if ($dimensions === false || ($dimensions[0] * $dimensions[1]) > 24_000_000) {
            throw ValidationException::withMessages([
                'image' => 'Zdjęcie może mieć maksymalnie 24 miliony pikseli.',
            ]);
        }

        $mimeType = $image->getMimeType();
        $extension = match ($mimeType) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            default => throw ValidationException::withMessages([
                'image' => 'Dozwolone są pliki JPG, PNG i WebP.',
            ]),
        };

        $id = (string) Str::ulid();
        $directory = 'realizations/'.now()->format('Y/m');
        $path = Storage::disk('public')->putFileAs(
            $directory,
            $image,
            "{$id}.{$extension}",
        );

        if ($path === false) {
            throw ValidationException::withMessages([
                'image' => 'Nie udało się zapisać zdjęcia. Spróbuj ponownie.',
            ]);
        }

        try {
            /** @var array{title: string, description: string} $validated */
            $validated = $request->safe()->only(['title', 'description']);

            Realization::query()->create([
                ...$validated,
                'image_disk' => 'public',
                'image_path' => $path,
            ]);
        } catch (Throwable $exception) {
            Storage::disk('public')->delete($path);

            throw $exception;
        }

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Realizacja została dodana.',
        ]);

        return to_route('dashboard.realizations.index');
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
}
