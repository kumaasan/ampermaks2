<?php

use App\Models\Post;
use App\Models\PostImage;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Testing\AssertableInertia as Assert;

function articleDocument(string $text = 'Bezpieczna treść artykułu'): array
{
    return [
        'type' => 'doc',
        'content' => [
            [
                'type' => 'paragraph',
                'content' => [
                    ['type' => 'text', 'text' => $text],
                ],
            ],
        ],
    ];
}

test('guest cannot open the post creation page', function () {
    $this->get(route('dashboard.posts.create'))
        ->assertRedirect(route('login'));
});

test('authenticated user can open the post creation page', function () {
    $user = User::factory()->admin()->create();

    $this->actingAs($user)
        ->get(route('dashboard.posts.create'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('CreatePost')
            ->where('maxUploadSizeMb', 8)
            ->has('allowedImageTypes', 3));
});

test('regular authenticated user cannot manage posts', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('dashboard.posts.create'))
        ->assertForbidden();
});

test('user can publish a post and html is generated safely on the server', function () {
    $user = User::factory()->admin()->create();

    $response = $this->actingAs($user)->post(route('dashboard.posts.store'), [
        'title' => 'Pierwszy artykuł',
        'excerpt' => 'Krótki opis artykułu.',
        'content' => articleDocument('<script>alert("xss")</script>'),
        'status' => 'published',
    ]);

    $post = Post::query()->sole();

    $response->assertRedirect(route('blog.show', $post));
    expect($post->content_html)
        ->toContain('&lt;script&gt;')
        ->not->toContain('<script>');
    expect($post->content_json['type'])->toBe('doc');
    expect($post->published_at)->not->toBeNull();
});

test('javascript links are rejected', function () {
    $user = User::factory()->admin()->create();
    $content = articleDocument('Kliknij');
    $content['content'][0]['content'][0]['marks'] = [
        ['type' => 'link', 'attrs' => ['href' => 'javascript:alert(1)']],
    ];

    $this->actingAs($user)
        ->post(route('dashboard.posts.store'), [
            'title' => 'Niebezpieczny link',
            'content' => $content,
            'status' => 'draft',
        ])
        ->assertSessionHasErrors('content');

    expect(Post::query()->count())->toBe(0);
});

test('validated image can be uploaded to public storage', function () {
    Storage::fake('public');
    $user = User::factory()->admin()->create();

    $response = $this->actingAs($user)
        ->postJson(route('dashboard.post-images.store'), [
            'image' => UploadedFile::fake()->image('rozdzielnia.jpg', 1200, 800)->size(1024),
        ]);

    $response
        ->assertCreated()
        ->assertJsonStructure(['id', 'url', 'width', 'height']);

    $image = PostImage::query()->sole();
    Storage::disk('public')->assertExists($image->path);
    expect($image->post_id)->toBeNull();
    expect($image->mime_type)->toBe('image/jpeg');
});

test('uploaded image is attached to the post and client supplied source is ignored', function () {
    Storage::fake('public');
    $user = User::factory()->admin()->create();
    $id = (string) Str::ulid();
    $path = "blog/2026/09/{$id}.jpg";
    Storage::disk('public')->put($path, 'image');

    $image = PostImage::query()->create([
        'id' => $id,
        'user_id' => $user->id,
        'disk' => 'public',
        'path' => $path,
        'original_name' => 'image.jpg',
        'mime_type' => 'image/jpeg',
        'size' => 5,
        'width' => 1200,
        'height' => 800,
    ]);

    $content = [
        'type' => 'doc',
        'content' => [[
            'type' => 'image',
            'attrs' => [
                'src' => 'javascript:alert(1)',
                'mediaId' => $image->id,
                'alt' => 'Rozdzielnia elektryczna',
                'width' => 600,
                'height' => 1,
                'align' => 'right',
            ],
        ]],
    ];

    $this->actingAs($user)
        ->post(route('dashboard.posts.store'), [
            'title' => 'Artykuł z obrazem',
            'content' => $content,
            'status' => 'draft',
        ])
        ->assertRedirect(route('dashboard.posts.create'));

    $post = Post::query()->sole();
    expect($post->content_html)
        ->toContain(Storage::disk('public')->url($path))
        ->toContain('width="600" height="400"')
        ->toContain('data-align="right"')
        ->not->toContain('javascript:');
    expect($image->fresh()->post_id)->toBe($post->id);
});

test('draft post is not publicly available', function () {
    $user = User::factory()->create();
    $post = Post::query()->create([
        'user_id' => $user->id,
        'title' => 'Szkic',
        'slug' => 'szkic',
        'content_json' => articleDocument(),
        'content_html' => '<p>Treść</p>',
        'content_schema_version' => 1,
        'status' => 'draft',
    ]);

    $this->get(route('blog.show', $post))->assertNotFound();
});
