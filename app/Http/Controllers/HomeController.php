<?php

namespace App\Http\Controllers;

use App\Models\Realization;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function __invoke(): Response
    {
        $realizations = Realization::query()
            ->select(['id', 'title', 'description', 'image_disk', 'image_path'])
            ->latest()
            ->latest('id')
            ->limit(6)
            ->get()
            ->map(fn (Realization $realization): array => [
                'id' => $realization->id,
                'title' => $realization->title,
                'description' => $realization->description,
                'image' => $realization->imageUrl(),
                'show_url' => route('realizations.show', $realization),
            ]);

        return Inertia::render('HomePage', [
            'realizations' => $realizations,
        ]);
    }
}
