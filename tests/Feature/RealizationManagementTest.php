<?php

use App\Models\Post;
use App\Models\Realization;
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
            ->where('realizations.1.id', $older->id));
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
    Storage::disk('public')->put($realization->image_path, 'image');

    $this->actingAs($admin)
        ->delete(route('dashboard.realizations.destroy', $realization))
        ->assertRedirect(route('dashboard.realizations.index'));

    $this->assertDatabaseMissing('realizations', ['id' => $realization->id]);
    Storage::disk('public')->assertMissing($realization->image_path);
});

test('deleting realization succeeds when its image is already missing', function () {
    Storage::fake('public');
    $admin = User::factory()->admin()->create();
    $realization = storedRealization();

    $this->actingAs($admin)
        ->delete(route('dashboard.realizations.destroy', $realization))
        ->assertRedirect(route('dashboard.realizations.index'));

    $this->assertDatabaseMissing('realizations', ['id' => $realization->id]);
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

    $this->actingAs($user)->get(route('dashboard.realizations.index'))->assertForbidden();
    $this->actingAs($user)->get(route('dashboard.realizations.create'))->assertForbidden();
    $this->actingAs($user)
        ->post(route('dashboard.realizations.store'), [
            'title' => 'Niedozwolona realizacja',
            'description' => 'Zwykły użytkownik nie może jej dodać.',
            'image' => UploadedFile::fake()->image('image.jpg'),
        ])
        ->assertForbidden();
    $this->actingAs($user)
        ->delete(route('dashboard.realizations.destroy', $realization))
        ->assertForbidden();
});

test('guest cannot manage realizations', function () {
    $realization = storedRealization();

    $this->get(route('dashboard.realizations.index'))->assertRedirect(route('login'));
    $this->get(route('dashboard.realizations.create'))->assertRedirect(route('login'));
    $this->post(route('dashboard.realizations.store'), [])->assertRedirect(route('login'));
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
