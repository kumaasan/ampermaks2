<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdatePostRequest;
use App\Models\Post;
use App\Models\PostImage;
use App\Models\User;
use App\Services\PostContentRenderer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class PostController extends Controller
{
    public function index(Request $request): Response
    {
        $search = Str::limit(trim((string) $request->query('search', '')), 160, '');

        $posts = Post::query()
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where('title', 'like', "%{$search}%");
            })
            ->latest('updated_at')
            ->latest('id')
            ->paginate(10)
            ->withQueryString()
            ->through(fn (Post $post): array => [
                'id' => $post->id,
                'title' => $post->title,
                'slug' => $post->slug,
                'status' => $post->status,
                'created_at' => $post->created_at?->toISOString(),
                'updated_at' => $post->updated_at?->toISOString(),
                'published_at' => $post->published_at?->toISOString(),
                'edit_url' => route('dashboard.posts.edit', $post),
                'delete_url' => route('dashboard.posts.destroy', $post),
            ]);

        return Inertia::render('ManagePosts', [
            'posts' => $posts,
            'filters' => ['search' => $search],
            'indexUrl' => route('dashboard.posts.index'),
            'createUrl' => route('dashboard.posts.create'),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('CreatePost', [
            'storeUrl' => route('dashboard.posts.store'),
            'indexUrl' => route('dashboard.posts.index'),
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

    public function edit(Post $post): Response
    {
        return Inertia::render('EditPost', [
            'post' => [
                'title' => $post->title,
                'slug' => $post->slug,
                'excerpt' => $post->excerpt ?? '',
                'content' => $post->content_json,
                'status' => $post->status,
            ],
            'updateUrl' => route('dashboard.posts.update', $post),
            'indexUrl' => route('dashboard.posts.index'),
            'uploadUrl' => route('dashboard.post-images.store'),
            'maxUploadSizeMb' => 8,
            'allowedImageTypes' => ['image/jpeg', 'image/png', 'image/webp'],
        ]);
    }

    public function update(
        UpdatePostRequest $request,
        Post $post,
        PostContentRenderer $renderer,
    ): RedirectResponse {
        $user = $request->user();
        abort_unless($user instanceof User, 403);

        /** @var array{title: string, slug: string, excerpt?: string|null, content: array<string, mixed>, status: 'draft'|'published'} $validated */
        $validated = $request->validated();
        $userId = (int) $user->getKey();
        $rendered = $renderer->render($validated['content'], $userId, $post);
        $wasPublished = $post->status === 'published' && $post->published_at !== null;

        /** @var list<PostImage> $removedImages */
        $removedImages = DB::transaction(function () use (
            $post,
            $userId,
            $validated,
            $rendered,
            $wasPublished,
        ): array {
            /** @var list<string> $currentMediaIds */
            $currentMediaIds = $post->images()
                ->whereIn('id', $rendered['media_ids'])
                ->pluck('id')
                ->all();
            $newMediaIds = array_values(array_diff($rendered['media_ids'], $currentMediaIds));

            if ($newMediaIds !== []) {
                $attached = PostImage::query()
                    ->where('user_id', $userId)
                    ->whereNull('post_id')
                    ->whereIn('id', $newMediaIds)
                    ->update([
                        'post_id' => $post->id,
                        'attached_at' => now(),
                    ]);

                if ($attached !== count($newMediaIds)) {
                    throw ValidationException::withMessages([
                        'content' => 'Nie udało się przypisać wszystkich obrazów do artykułu.',
                    ]);
                }
            }

            $removedImages = $post->images()
                ->whereNotIn('id', $rendered['media_ids'])
                ->get();

            $post->update([
                'title' => $validated['title'],
                'slug' => $validated['slug'],
                'excerpt' => $validated['excerpt'] ?? null,
                'content_json' => $rendered['json'],
                'content_html' => $rendered['html'],
                'content_schema_version' => 1,
                'status' => $validated['status'],
                'published_at' => $validated['status'] === 'published'
                    ? ($wasPublished ? $post->published_at : now())
                    : null,
            ]);

            return $removedImages->all();
        });

        foreach ($removedImages as $removedImage) {
            $removedImage->delete();
        }

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Zmiany w artykule zostały zapisane.',
        ]);

        return to_route('dashboard.posts.index');
    }

    public function destroy(Post $post): RedirectResponse
    {
        $post->delete();

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Artykuł został usunięty.',
        ]);

        return to_route('dashboard.posts.index');
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
