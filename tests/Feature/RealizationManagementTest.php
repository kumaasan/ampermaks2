<?php

use App\Models\Post;
use App\Models\Realization;
use App\Models\RealizationImage;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

function storedRealization(array $attributes = []): Realization
{
    return Realization::query()->create([
        'title' => 'Wymiana instalacji elektrycznej',
        'description' => 'Kompleksowa wymiana starej instalacji aluminiowej na miedzianą.',
        'image_disk' => 'public',
        'image_path' => 'realizations/2026/09/test.jpg',
        ...$attributes,
    ]);
}

function storedRealizationImage(Realization $realization, array $attributes = []): RealizationImage
{
    return $realization->images()->create([
        'disk' => 'public',
        'path' => "realizations/{$realization->id}/gallery.jpg",
        'sort_order' => 0,
        ...$attributes,
    ]);
}

test('administrator can open realization list and creation form', function () {
    $admin = User::factory()->admin()->create();
    $realization = storedRealization();

    $this->actingAs($admin)
        ->get(route('dashboard.realizations.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('ManageRealizations')
            ->where('realizations.total', 1)
            ->where('realizations.data.0.id', $realization->id)
            ->where('realizations.data.0.title', $realization->title)
            ->where('realizations.data.0.images_count', 1)
            ->where(
                'realizations.data.0.edit_url',
                route('dashboard.realizations.edit', $realization),
            )
            ->where(
                'realizations.data.0.delete_url',
                route('dashboard.realizations.destroy', $realization),
            )
            ->has('realizations.links')
            ->where('createUrl', route('dashboard.realizations.create')));

    $this->actingAs($admin)
        ->get(route('dashboard.realizations.create'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('CreateRealization')
            ->where('storeUrl', route('dashboard.realizations.store'))
            ->where('indexUrl', route('dashboard.realizations.index'))
            ->where('maxUploadSizeMb', 8)
            ->where('maxImagesPerUpload', 10)
            ->has('allowedImageTypes', 3));
});

test('administrator can add realization and store its image', function () {
    Storage::fake('public');
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)
        ->post(route('dashboard.realizations.store'), [
            'title' => 'Montaż rozdzielnicy',
            'description' => 'Nowa rozdzielnica z zabezpieczeniami przepięciowymi.',
            'image' => UploadedFile::fake()
                ->image('rozdzielnica.jpg', 1600, 1200)
                ->size(2048),
        ]);

    $response->assertRedirect(route('dashboard.realizations.index'));

    $realization = Realization::query()->sole();
    expect($realization->title)->toBe('Montaż rozdzielnicy')
        ->and($realization->description)->toBe('Nowa rozdzielnica z zabezpieczeniami przepięciowymi.')
        ->and($realization->image_disk)->toBe('public')
        ->and($realization->image_path)->toStartWith('realizations/');
    Storage::disk('public')->assertExists($realization->image_path);
});

test('administrator can create realization with multiple assigned images', function () {
    Storage::fake('public');
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->post(route('dashboard.realizations.store'), [
            'title' => 'Instalacja w nowym domu',
            'description' => 'Kompletna instalacja elektryczna wraz z rozdzielnicą.',
            'images' => [
                UploadedFile::fake()->image('cover.jpg', 1600, 1200)->size(1024),
                UploadedFile::fake()->image('room.png', 1200, 900)->size(1024),
                UploadedFile::fake()->image('board.jpg', 1200, 900)->size(1024),
            ],
        ])
        ->assertRedirect(route('dashboard.realizations.index'))
        ->assertSessionHasNoErrors();

    $realization = Realization::query()->sole();
    $galleryImages = RealizationImage::query()
        ->where('realization_id', $realization->id)
        ->orderBy('sort_order')
        ->get();

    expect($galleryImages)->toHaveCount(2)
        ->and($galleryImages->pluck('sort_order')->all())->toBe([0, 1])
        ->and($galleryImages->every(
            fn (RealizationImage $image): bool => $image->realization_id === $realization->id,
        ))->toBeTrue();

    Storage::disk('public')->assertExists($realization->image_path);
    $galleryImages->each(
        fn (RealizationImage $image) => Storage::disk('public')->assertExists($image->path),
    );
});

test('administrator can open gallery manager and add images to existing realization', function () {
    Storage::fake('public');
    $admin = User::factory()->admin()->create();
    $realization = storedRealization();
    $existingImage = storedRealizationImage($realization);

    $this->actingAs($admin)
        ->get(route('dashboard.realizations.edit', $realization))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('ManageRealizationGallery')
            ->where('realization.id', $realization->id)
            ->where('realization.cover.url', $realization->imageUrl())
            ->where('realization.images.0.id', $existingImage->id)
            ->where(
                'storeImagesUrl',
                route('dashboard.realizations.images.store', $realization),
            ));

    $this->actingAs($admin)
        ->post(route('dashboard.realizations.images.store', $realization), [
            'images' => [
                UploadedFile::fake()->image('detail-1.jpg', 1200, 900),
                UploadedFile::fake()->image('detail-2.png', 1200, 900),
            ],
        ])
        ->assertRedirect(route('dashboard.realizations.edit', $realization))
        ->assertSessionHasNoErrors();

    $addedImages = $realization->images()->whereKeyNot($existingImage->id)->get();
    expect($addedImages)->toHaveCount(2)
        ->and($addedImages->pluck('sort_order')->all())->toBe([1, 2]);
    $addedImages->each(
        fn (RealizationImage $image) => Storage::disk('public')->assertExists($image->path),
    );
});

test('administrator can delete one gallery image without deleting realization', function () {
    Storage::fake('public');
    $admin = User::factory()->admin()->create();
    $realization = storedRealization();
    $image = storedRealizationImage($realization);
    $remainingImage = storedRealizationImage($realization, [
        'path' => "realizations/{$realization->id}/remaining.jpg",
        'sort_order' => 1,
    ]);
    Storage::disk('public')->put($image->path, 'image');
    Storage::disk('public')->put($remainingImage->path, 'image');

    $this->actingAs($admin)
        ->delete(route('dashboard.realizations.images.destroy', [$realization, $image]))
        ->assertRedirect(route('dashboard.realizations.edit', $realization));

    $this->assertDatabaseHas('realizations', ['id' => $realization->id]);
    $this->assertDatabaseMissing('realization_images', ['id' => $image->id]);
    $this->assertDatabaseHas('realization_images', ['id' => $remainingImage->id]);
    Storage::disk('public')->assertMissing($image->path);
    Storage::disk('public')->assertExists($remainingImage->path);
});

test('administrator can delete cover and first gallery image becomes new cover', function () {
    Storage::fake('public');
    $admin = User::factory()->admin()->create();
    $realization = storedRealization();
    $oldCoverPath = $realization->image_path;
    $newCover = storedRealizationImage($realization, [
        'path' => "realizations/{$realization->id}/new-cover.jpg",
    ]);
    $remainingImage = storedRealizationImage($realization, [
        'path' => "realizations/{$realization->id}/remaining.jpg",
        'sort_order' => 1,
    ]);
    Storage::disk('public')->put($oldCoverPath, 'old cover');
    Storage::disk('public')->put($newCover->path, 'new cover');
    Storage::disk('public')->put($remainingImage->path, 'remaining image');

    $this->actingAs($admin)
        ->delete(route('dashboard.realizations.cover.destroy', $realization))
        ->assertRedirect(route('dashboard.realizations.edit', $realization));

    $realization->refresh();

    expect($realization->image_path)->toBe($newCover->path);
    $this->assertDatabaseMissing('realization_images', ['id' => $newCover->id]);
    $this->assertDatabaseHas('realization_images', ['id' => $remainingImage->id]);
    Storage::disk('public')->assertMissing($oldCoverPath);
    Storage::disk('public')->assertExists($newCover->path);
    Storage::disk('public')->assertExists($remainingImage->path);
});

test('gallery image route cannot delete image from another realization', function () {
    Storage::fake('public');
    $admin = User::factory()->admin()->create();
    $realization = storedRealization();
    $otherRealization = storedRealization(['title' => 'Inna realizacja']);
    $image = storedRealizationImage($otherRealization);

    $this->actingAs($admin)
        ->delete(route('dashboard.realizations.images.destroy', [$realization, $image]))
        ->assertNotFound();

    $this->assertDatabaseHas('realization_images', ['id' => $image->id]);
});

test('latest realizations are visible on the public home page', function () {
    Storage::fake('public');
    $older = storedRealization([
        'title' => 'Starsza realizacja',
        'image_path' => 'realizations/older.jpg',
        'created_at' => now()->subDay(),
    ]);
    $latest = storedRealization([
        'title' => 'Najnowsza realizacja',
        'image_path' => 'realizations/latest.jpg',
        'created_at' => now(),
    ]);

    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('HomePage')
            ->has('realizations', 2)
            ->where('realizations.0.id', $latest->id)
            ->where('realizations.0.title', 'Najnowsza realizacja')
            ->where('realizations.0.description', $latest->description)
            ->where('realizations.0.image', $latest->imageUrl())
            ->where('realizations.0.show_url', route('realizations.show', $latest))
            ->where('realizations.1.id', $older->id));
});

test('public realization gallery includes cover and ordered additional images', function () {
    Storage::fake('public');
    $realization = storedRealization();
    $second = storedRealizationImage($realization, [
        'path' => 'realizations/second.jpg',
        'sort_order' => 1,
    ]);
    $first = storedRealizationImage($realization, [
        'path' => 'realizations/first.jpg',
        'sort_order' => 0,
    ]);

    $this->get(route('realizations.show', $realization))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('RealizationShow')
            ->where('realization.id', $realization->id)
            ->has('realization.images', 3)
            ->where('realization.images.0.url', $realization->imageUrl())
            ->where('realization.images.0.is_cover', true)
            ->where('realization.images.1.id', (string) $first->id)
            ->where('realization.images.2.id', (string) $second->id)
            ->where('backUrl', route('home').'#realizacje'));
});

test('legacy realization without gallery images still has a public gallery', function () {
    Storage::fake('public');
    $realization = storedRealization();

    $this->get(route('realizations.show', $realization))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('RealizationShow')
            ->has('realization.images', 1)
            ->where('realization.images.0.url', $realization->imageUrl()));
});

test('public home page shows at most six latest realizations', function () {
    foreach (range(1, 7) as $number) {
        storedRealization([
            'title' => "Realizacja {$number}",
            'image_path' => "realizations/{$number}.jpg",
            'created_at' => now()->addSeconds($number),
        ]);
    }

    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('realizations', 6)
            ->where('realizations.0.title', 'Realizacja 7')
            ->where('realizations.5.title', 'Realizacja 2'));
});

test('administrator can delete realization and its image', function () {
    Storage::fake('public');
    $admin = User::factory()->admin()->create();
    $realization = storedRealization();
    $galleryImage = storedRealizationImage($realization);
    Storage::disk('public')->put($realization->image_path, 'image');
    Storage::disk('public')->put($galleryImage->path, 'image');

    $this->actingAs($admin)
        ->delete(route('dashboard.realizations.destroy', $realization))
        ->assertRedirect(route('dashboard.realizations.index'));

    $this->assertDatabaseMissing('realizations', ['id' => $realization->id]);
    $this->assertDatabaseMissing('realization_images', ['id' => $galleryImage->id]);
    Storage::disk('public')->assertMissing($realization->image_path);
    Storage::disk('public')->assertMissing($galleryImage->path);
});

test('deleting realization succeeds when its image is already missing', function () {
    Storage::fake('public');
    $admin = User::factory()->admin()->create();
    $realization = storedRealization();
    $galleryImage = storedRealizationImage($realization);

    $this->actingAs($admin)
        ->delete(route('dashboard.realizations.destroy', $realization))
        ->assertRedirect(route('dashboard.realizations.index'));

    $this->assertDatabaseMissing('realizations', ['id' => $realization->id]);
    $this->assertDatabaseMissing('realization_images', ['id' => $galleryImage->id]);
});

test('shared realization image is not removed while another record uses it', function () {
    Storage::fake('public');
    $admin = User::factory()->admin()->create();
    $realization = storedRealization();
    storedRealization([
        'title' => 'Druga realizacja',
        'image_path' => $realization->image_path,
    ]);
    Storage::disk('public')->put($realization->image_path, 'image');

    $this->actingAs($admin)
        ->delete(route('dashboard.realizations.destroy', $realization))
        ->assertRedirect(route('dashboard.realizations.index'));

    Storage::disk('public')->assertExists($realization->image_path);
});

test('regular user cannot manage realizations', function () {
    Storage::fake('public');
    $user = User::factory()->create();
    $realization = storedRealization();
    $galleryImage = storedRealizationImage($realization);

    $this->actingAs($user)->get(route('dashboard.realizations.index'))->assertForbidden();
    $this->actingAs($user)->get(route('dashboard.realizations.create'))->assertForbidden();
    $this->actingAs($user)
        ->get(route('dashboard.realizations.edit', $realization))
        ->assertForbidden();
    $this->actingAs($user)
        ->post(route('dashboard.realizations.store'), [
            'title' => 'Niedozwolona realizacja',
            'description' => 'Zwykły użytkownik nie może jej dodać.',
            'image' => UploadedFile::fake()->image('image.jpg'),
        ])
        ->assertForbidden();
    $this->actingAs($user)
        ->post(route('dashboard.realizations.images.store', $realization), [
            'images' => [UploadedFile::fake()->image('image.jpg')],
        ])
        ->assertForbidden();
    $this->actingAs($user)
        ->delete(route('dashboard.realizations.images.destroy', [$realization, $galleryImage]))
        ->assertForbidden();
    $this->actingAs($user)
        ->delete(route('dashboard.realizations.cover.destroy', $realization))
        ->assertForbidden();
    $this->actingAs($user)
        ->delete(route('dashboard.realizations.destroy', $realization))
        ->assertForbidden();
});

test('guest cannot manage realizations', function () {
    $realization = storedRealization();
    $galleryImage = storedRealizationImage($realization);

    $this->get(route('dashboard.realizations.index'))->assertRedirect(route('login'));
    $this->get(route('dashboard.realizations.create'))->assertRedirect(route('login'));
    $this->get(route('dashboard.realizations.edit', $realization))->assertRedirect(route('login'));
    $this->post(route('dashboard.realizations.store'), [])->assertRedirect(route('login'));
    $this->post(route('dashboard.realizations.images.store', $realization), [])
        ->assertRedirect(route('login'));
    $this->delete(route('dashboard.realizations.images.destroy', [$realization, $galleryImage]))
        ->assertRedirect(route('login'));
    $this->delete(route('dashboard.realizations.cover.destroy', $realization))
        ->assertRedirect(route('login'));
    $this->delete(route('dashboard.realizations.destroy', $realization))
        ->assertRedirect(route('login'));
});

test('realization validates content and verifies that upload is an image', function () {
    Storage::fake('public');
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->from(route('dashboard.realizations.create'))
        ->post(route('dashboard.realizations.store'), [
            'title' => '',
            'description' => str_repeat('a', 301),
            'image' => UploadedFile::fake()->createWithContent(
                'not-an-image.jpg',
                '<?php echo "not an image";',
            ),
        ])
        ->assertRedirect(route('dashboard.realizations.create'))
        ->assertSessionHasErrors(['title', 'description', 'image']);

    expect(Realization::query()->count())->toBe(0);
});

test('realization rejects unsupported image formats and oversized files', function () {
    Storage::fake('public');
    $admin = User::factory()->admin()->create();
    $payload = [
        'title' => 'Realizacja testowa',
        'description' => 'Opis realizacji testowej.',
    ];

    $this->actingAs($admin)
        ->post(route('dashboard.realizations.store'), [
            ...$payload,
            'image' => UploadedFile::fake()->image('image.gif', 320, 180),
        ])
        ->assertSessionHasErrors('image');

    $this->actingAs($admin)
        ->post(route('dashboard.realizations.store'), [
            ...$payload,
            'image' => UploadedFile::fake()->image('large.jpg', 320, 180)->size(8193),
        ])
        ->assertSessionHasErrors('image');

    expect(Realization::query()->count())->toBe(0);
});

test('multiple realization images are validated separately and limited per request', function () {
    Storage::fake('public');
    $admin = User::factory()->admin()->create();
    $payload = [
        'title' => 'Realizacja testowa',
        'description' => 'Opis realizacji testowej.',
    ];

    $this->actingAs($admin)
        ->post(route('dashboard.realizations.store'), [
            ...$payload,
            'images' => [
                UploadedFile::fake()->image('valid.jpg', 320, 180),
                UploadedFile::fake()->createWithContent('invalid.jpg', 'not an image'),
            ],
        ])
        ->assertSessionHasErrors('images.1');

    $this->actingAs($admin)
        ->post(route('dashboard.realizations.store'), [
            ...$payload,
            'images' => array_map(
                fn (int $number) => UploadedFile::fake()->image("{$number}.jpg", 320, 180),
                range(1, 11),
            ),
        ])
        ->assertSessionHasErrors('images');

    expect(Realization::query()->count())->toBe(0);
});

test('existing public blog remains available', function () {
    $author = User::factory()->create();
    Post::query()->create([
        'user_id' => $author->id,
        'title' => 'Artykuł bez zmian',
        'slug' => 'artykul-bez-zmian',
        'excerpt' => 'Opis artykułu.',
        'content_json' => ['type' => 'doc', 'content' => []],
        'content_html' => '<p>Treść artykułu.</p>',
        'status' => 'published',
        'published_at' => now()->subMinute(),
    ]);

    $this->get(route('blog'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Blog')
            ->where('posts.total', 1)
            ->where('posts.data.0.slug', 'artykul-bez-zmian'));
});
