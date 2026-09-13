<?php

namespace App\Http\Controllers;

use App\Models\Realization;
use App\Models\RealizationImage;
use Inertia\Inertia;
use Inertia\Response;

class PublicRealizationController extends Controller
{
    public function show(Realization $realization): Response
    {
        $realization->load('images');

        $images = collect([
            [
                'id' => 'cover-'.$realization->id,
                'url' => $realization->imageUrl(),
                'is_cover' => true,
            ],
        ])->concat(
            $realization->images->map(fn (RealizationImage $image): array => [
                'id' => (string) $image->id,
                'url' => $image->url(),
                'is_cover' => false,
            ]),
        )->values();

        return Inertia::render('RealizationShow', [
            'realization' => [
                'id' => $realization->id,
                'title' => $realization->title,
                'description' => $realization->description,
                'images' => $images,
            ],
            'backUrl' => route('home').'#realizacje',
        ]);
    }
}
