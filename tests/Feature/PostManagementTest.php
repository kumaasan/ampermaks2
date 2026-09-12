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

function managedPost(User $user, array $attributes = []): Post
{
    $status = $attributes['status'] ?? 'draft';

    return Post::query()->create([
        'user_id' => $user->id,
        'title' => 'Artykuł testowy',
        'slug' => 'artykul-testowy',
        'excerpt' => 'Opis artykułu testowego.',
        'content_json' => articleDocument(),
        'content_html' => '<p>Bezpieczna treść artykułu</p>',
        'content_schema_version' => 1,
        'status' => $status,
        'published_at' => $status === 'published' ? now()->subMinute() : null,
        ...$attributes,
    ]);
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
            ->where('indexUrl', route('dashboard.posts.index'))
            ->where('maxUploadSizeMb', 8)
            ->has('allowedImageTypes', 3));
});

test('administrator can open and search the paginated post list', function () {
    $admin = User::factory()->admin()->create();
    managedPost($admin, ['title' => 'Instalacja w domu', 'slug' => 'instalacja-w-domu']);
    managedPost($admin, ['title' => 'Pomiary elektryczne', 'slug' => 'pomiary-elektryczne']);

    $this->actingAs($admin)
        ->get(route('dashboard.posts.index', ['search' => 'Pomiary']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('ManagePosts')
            ->where('filters.search', 'Pomiary')
            ->where('posts.total', 1)
            ->where('posts.data.0.title', 'Pomiary elektryczne')
            ->where('posts.data.0.status', 'draft')
            ->where('posts.data.0.edit_url', route('dashboard.posts.edit', 'pomiary-elektryczne'))
            ->has('posts.links'));
});

test('administrator can open post editing with existing content', function () {
    $admin = User::factory()->admin()->create();
    $post = managedPost($admin);

    $this->actingAs($admin)
        ->get(route('dashboard.posts.edit', $post))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('EditPost')
            ->where('post.title', $post->title)
            ->where('post.slug', $post->slug)
            ->where('post.content', $post->content_json)
            ->where('post.status', 'draft')
            ->where('updateUrl', route('dashboard.posts.update', $post)));
});

test('administrator can update a post and keep its current unique slug', function () {
    $admin = User::factory()->admin()->create();
    $post = managedPost($admin);

    $response = $this->actingAs($admin)
        ->put(route('dashboard.posts.update', $post), [
            'title' => 'Zmieniony artykuł',
            'slug' => $post->slug,
            'excerpt' => 'Nowy opis.',
            'content' => articleDocument('Nowa treść <script>alert(1)</script>'),
            'status' => 'published',
        ]);

    $response->assertRedirect(route('dashboard.posts.index'));

    $post->refresh();
    expect($post->title)->toBe('Zmieniony artykuł')
        ->and($post->slug)->toBe('artykul-testowy')
        ->and($post->excerpt)->toBe('Nowy opis.')
        ->and($post->status)->toBe('published')
        ->and($post->published_at)->not->toBeNull()
        ->and($post->content_html)->toContain('&lt;script&gt;')
        ->and($post->content_html)->not->toContain('<script>');
});

test('post update validates required fields and unique slug', function () {
    $admin = User::factory()->admin()->create();
    $post = managedPost($admin);
    managedPost($admin, ['title' => 'Drugi artykuł', 'slug' => 'drugi-artykul']);

    $this->actingAs($admin)
        ->from(route('dashboard.posts.edit', $post))
        ->put(route('dashboard.posts.update', $post), [
            'title' => '',
            'slug' => 'drugi-artykul',
            'content' => ['type' => 'doc', 'content' => []],
            'status' => 'published',
        ])
        ->assertRedirect(route('dashboard.posts.edit', $post))
        ->assertSessionHasErrors(['title', 'slug', 'content.content']);

    expect($post->fresh()->slug)->toBe('artykul-testowy');
});

test('editing accepts current and new images then removes images deleted from content', function () {
    Storage::fake('public');
    $admin = User::factory()->admin()->create();
    $post = managedPost($admin);
    $id = (string) Str::ulid();
    $path = "blog/2026/09/{$id}.jpg";
    Storage::disk('public')->put($path, 'image');
    $image = PostImage::query()->create([
        'id' => $id,
        'user_id' => $admin->id,
        'post_id' => $post->id,
        'disk' => 'public',
        'path' => $path,
        'original_name' => 'image.jpg',
        'mime_type' => 'image/jpeg',
        'size' => 5,
        'width' => 1200,
        'height' => 800,
        'attached_at' => now(),
    ]);
    $newId = (string) Str::ulid();
    $newPath = "blog/2026/09/{$newId}.jpg";
    Storage::disk('public')->put($newPath, 'new image');
    $newImage = PostImage::query()->create([
        'id' => $newId,
        'user_id' => $admin->id,
        'disk' => 'public',
        'path' => $newPath,
        'original_name' => 'new-image.jpg',
        'mime_type' => 'image/jpeg',
        'size' => 9,
        'width' => 1200,
        'height' => 800,
    ]);
    $contentWithImage = [
        'type' => 'doc',
        'content' => [
            [
                'type' => 'image',
                'attrs' => [
                    'src' => $image->url(),
                    'mediaId' => $image->id,
                    'alt' => 'Rozdzielnia',
                    'width' => 600,
                    'height' => 400,
                    'align' => 'center',
                ],
            ],
            [
                'type' => 'image',
                'attrs' => [
                    'src' => $newImage->url(),
                    'mediaId' => $newImage->id,
                    'alt' => 'Nowa rozdzielnia',
                    'width' => 600,
                    'height' => 400,
                    'align' => 'left',
                ],
            ],
        ],
    ];
    $post->update(['content_json' => $contentWithImage]);

    $this->actingAs($admin)
        ->put(route('dashboard.posts.update', $post), [
            'title' => $post->title,
            'slug' => $post->slug,
            'excerpt' => $post->excerpt,
            'content' => $contentWithImage,
            'status' => 'draft',
        ])
        ->assertRedirect(route('dashboard.posts.index'));

    expect($image->fresh())->not->toBeNull();
    expect($newImage->fresh()?->post_id)->toBe($post->id);
    Storage::disk('public')->assertExists($path);
    Storage::disk('public')->assertExists($newPath);

    $this->actingAs($admin)
        ->put(route('dashboard.posts.update', $post), [
            'title' => $post->title,
            'slug' => $post->slug,
            'excerpt' => $post->excerpt,
            'content' => articleDocument('Treść bez obrazu'),
            'status' => 'draft',
        ])
        ->assertRedirect(route('dashboard.posts.index'));

    expect($image->fresh())->toBeNull();
    expect($newImage->fresh())->toBeNull();
    Storage::disk('public')->assertMissing($path);
    Storage::disk('public')->assertMissing($newPath);
});

test('editing cannot reuse an image attached to another post', function () {
    Storage::fake('public');
    $admin = User::factory()->admin()->create();
    $post = managedPost($admin);
    $otherPost = managedPost($admin, [
        'title' => 'Inny artykuł',
        'slug' => 'inny-artykul',
    ]);
    $id = (string) Str::ulid();
    $path = "blog/2026/09/{$id}.jpg";
    Storage::disk('public')->put($path, 'image');
    $image = PostImage::query()->create([
        'id' => $id,
        'user_id' => $admin->id,
        'post_id' => $otherPost->id,
        'disk' => 'public',
        'path' => $path,
        'original_name' => 'image.jpg',
        'mime_type' => 'image/jpeg',
        'size' => 5,
        'width' => 1200,
        'height' => 800,
        'attached_at' => now(),
    ]);
    $content = [
        'type' => 'doc',
        'content' => [[
            'type' => 'image',
            'attrs' => [
                'src' => $image->url(),
                'mediaId' => $image->id,
                'alt' => 'Obcy obraz',
                'width' => 600,
                'height' => 400,
                'align' => 'center',
            ],
        ]],
    ];

    $this->actingAs($admin)
        ->put(route('dashboard.posts.update', $post), [
            'title' => $post->title,
            'slug' => $post->slug,
            'excerpt' => $post->excerpt,
            'content' => $content,
            'status' => 'draft',
        ])
        ->assertSessionHasErrors('content');

    expect($image->fresh()?->post_id)->toBe($otherPost->id);
    Storage::disk('public')->assertExists($path);
});

test('administrator can delete a post and its stored images', function () {
    Storage::fake('public');
    $admin = User::factory()->admin()->create();
    $post = managedPost($admin);
    $id = (string) Str::ulid();
    $path = "blog/2026/09/{$id}.jpg";
    Storage::disk('public')->put($path, 'image');
    PostImage::query()->create([
        'id' => $id,
        'user_id' => $admin->id,
        'post_id' => $post->id,
        'disk' => 'public',
        'path' => $path,
        'original_name' => 'image.jpg',
        'mime_type' => 'image/jpeg',
        'size' => 5,
        'width' => 1200,
        'height' => 800,
        'attached_at' => now(),
    ]);

    $this->actingAs($admin)
        ->delete(route('dashboard.posts.destroy', $post))
        ->assertRedirect(route('dashboard.posts.index'));

    $this->assertDatabaseMissing('posts', ['id' => $post->id]);
    $this->assertDatabaseMissing('post_images', ['id' => $id]);
    Storage::disk('public')->assertMissing($path);
});

test('regular user cannot access any post management endpoint', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create();
    $post = managedPost($admin);

    $this->actingAs($user)->get(route('dashboard.posts.index'))->assertForbidden();
    $this->actingAs($user)->get(route('dashboard.posts.edit', $post))->assertForbidden();
    $this->actingAs($user)->put(route('dashboard.posts.update', $post), [])->assertForbidden();
    $this->actingAs($user)->delete(route('dashboard.posts.destroy', $post))->assertForbidden();
});

test('guest cannot access any post management endpoint', function () {
    $admin = User::factory()->admin()->create();
    $post = managedPost($admin);

    $this->get(route('dashboard.posts.index'))->assertRedirect(route('login'));
    $this->get(route('dashboard.posts.edit', $post))->assertRedirect(route('login'));
    $this->put(route('dashboard.posts.update', $post), [])->assertRedirect(route('login'));
    $this->delete(route('dashboard.posts.destroy', $post))->assertRedirect(route('login'));
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

test('published posts remain visible on the public blog and post page', function () {
    $author = User::factory()->create();
    $post = managedPost($author, [
        'title' => 'Publiczny artykuł',
        'slug' => 'publiczny-artykul',
        'status' => 'published',
    ]);

    $this->get(route('blog'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Blog')
            ->where('posts.total', 1)
            ->where('posts.data.0.slug', $post->slug));

    $this->get(route('blog.show', $post))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('PostShow')
            ->where('post.title', $post->title)
            ->where('post.content_html', $post->content_html));
});
