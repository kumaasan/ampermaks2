<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PublicPostController extends Controller
{
    public function index(Request $request): Response
    {
        $search = trim((string) $request->query('search', ''));
        $sort = $request->query('sort') === 'oldest' ? 'oldest' : 'newest';

        $posts = Post::query()
            ->published()
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query
                        ->where('title', 'like', "%{$search}%")
                        ->orWhere('excerpt', 'like', "%{$search}%");
                });
            })
            ->with('author:id,name')
            ->orderBy('published_at', $sort === 'oldest' ? 'asc' : 'desc')
            ->paginate(9)
            ->withQueryString()
            ->through(fn (Post $post): array => [
                'title' => $post->title,
                'slug' => $post->slug,
                'excerpt' => $post->excerpt,
                'author' => $post->author->name,
                'published_at' => $post->published_at?->toISOString(),
            ]);

        return Inertia::render('Blog', [
            'posts' => $posts,
            'filters' => [
                'search' => $search,
                'sort' => $sort,
            ],
        ]);
    }

    public function show(Post $post): Response
    {
        abort_unless(
            $post->status === 'published' && $post->published_at?->isPast(),
            404,
        );

        $post->load('author:id,name');

        return Inertia::render('PostShow', [
            'post' => [
                'title' => $post->title,
                'slug' => $post->slug,
                'excerpt' => $post->excerpt,
                'content_html' => $post->content_html,
                'author' => $post->author->name,
                'published_at' => $post->published_at?->toISOString(),
            ],
        ]);
    }
}
