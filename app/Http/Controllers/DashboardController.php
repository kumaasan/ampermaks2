<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Realization;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $user = $request->user();
        abort_unless($user instanceof User, 403);

        if (! $user->can('manage-posts')) {
            return Inertia::render('Dashboard', [
                'canManageContent' => false,
            ]);
        }

        $recentPosts = Post::query()
            ->latest('updated_at')
            ->latest('id')
            ->limit(5)
            ->get()
            ->map(fn (Post $post): array => [
                'id' => $post->id,
                'title' => $post->title,
                'status' => $post->status,
                'updated_at' => $post->updated_at?->toISOString(),
                'edit_url' => route('dashboard.posts.edit', $post),
            ]);

        $recentRealizations = Realization::query()
            ->latest()
            ->latest('id')
            ->limit(4)
            ->get()
            ->map(fn (Realization $realization): array => [
                'id' => $realization->id,
                'title' => $realization->title,
                'image_url' => $realization->imageUrl(),
                'created_at' => $realization->created_at?->toISOString(),
            ]);

        return Inertia::render('Dashboard', [
            'canManageContent' => true,
            'stats' => [
                'posts' => Post::query()->count(),
                'publishedPosts' => Post::query()->published()->count(),
                'realizations' => Realization::query()->count(),
                'users' => User::query()->count(),
            ],
            'recentPosts' => $recentPosts,
            'recentRealizations' => $recentRealizations,
            'links' => [
                'createPost' => route('dashboard.posts.create'),
                'managePosts' => route('dashboard.posts.index'),
                'createRealization' => route('dashboard.realizations.create'),
                'manageRealizations' => route('dashboard.realizations.index'),
            ],
        ]);
    }
}
