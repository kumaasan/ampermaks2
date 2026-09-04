<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePostRequest;
use App\Models\Post;
use App\Models\PostImage;
use App\Models\User;
use App\Services\PostContentRenderer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class PostController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('CreatePost', [
            'storeUrl' => route('dashboard.posts.store'),
            'uploadUrl' => route('dashboard.post-images.store'),
            'maxUploadSizeMb' => 8,
            'allowedImageTypes' => ['image/jpeg', 'image/png', 'image/webp'],
        ]);
    }

    public function store(StorePostRequest $request, PostContentRenderer $renderer): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user instanceof User, 403);

        /** @var array{title: string, excerpt?: string|null, content: array<string, mixed>, status: 'draft'|'published'} $validated */
        $validated = $request->validated();
        $userId = (int) $user->getKey();
        $rendered = $renderer->render($validated['content'], $userId);

        $post = DB::transaction(function () use ($userId, $validated, $rendered): Post {
            $post = Post::query()->create([
                'user_id' => $userId,
                'title' => $validated['title'],
                'slug' => $this->uniqueSlug($validated['title']),
                'excerpt' => $validated['excerpt'] ?? null,
                'content_json' => $rendered['json'],
                'content_html' => $rendered['html'],
                'content_schema_version' => 1,
                'status' => $validated['status'],
                'published_at' => $validated['status'] === 'published' ? now() : null,
            ]);

            if ($rendered['media_ids'] !== []) {
                $attached = PostImage::query()
                    ->where('user_id', $userId)
                    ->whereNull('post_id')
                    ->whereIn('id', $rendered['media_ids'])
                    ->update([
                        'post_id' => $post->id,
                        'attached_at' => now(),
                    ]);

                if ($attached !== count($rendered['media_ids'])) {
                    throw ValidationException::withMessages([
                        'content' => 'Nie udało się przypisać wszystkich obrazów do artykułu.',
                    ]);
                }
            }

            return $post;
        });

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => $post->status === 'published'
                ? 'Artykuł został opublikowany.'
                : 'Szkic został zapisany.',
        ]);

        if ($post->status === 'published') {
            return to_route('blog.show', $post);
        }

        return to_route('dashboard.posts.create');
    }

    private function uniqueSlug(string $title): string
    {
        $base = Str::slug($title);
        $base = $base !== '' ? $base : 'artykul';
        $slug = $base;
        $suffix = 2;

        while (Post::query()->where('slug', $slug)->exists()) {
            $slug = "{$base}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }
}
