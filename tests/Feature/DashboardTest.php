<?php

use App\Models\Post;
use App\Models\Realization;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('login'));
});

test('authenticated users can visit the dashboard', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Dashboard')
            ->where('canManageContent', false)
            ->missing('stats'));
});

test('administrator dashboard contains real statistics and recent content', function () {
    $admin = User::factory()->admin()->create();
    User::factory()->create();

    Post::query()->create([
        'user_id' => $admin->id,
        'title' => 'Szkic instalacji',
        'slug' => 'szkic-instalacji',
        'content_json' => ['type' => 'doc', 'content' => []],
        'content_html' => '',
        'status' => 'draft',
    ]);
    $published = Post::query()->create([
        'user_id' => $admin->id,
        'title' => 'Opublikowany artykuł',
        'slug' => 'opublikowany-artykul',
        'content_json' => ['type' => 'doc', 'content' => []],
        'content_html' => '',
        'status' => 'published',
        'published_at' => now()->subMinute(),
    ]);
    $realization = Realization::query()->create([
        'title' => 'Nowa rozdzielnica',
        'description' => 'Wymiana rozdzielnicy w domu jednorodzinnym.',
        'image_disk' => 'public',
        'image_path' => 'realizations/test.jpg',
    ]);

    $this->actingAs($admin)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Dashboard')
            ->where('canManageContent', true)
            ->where('stats.posts', 2)
            ->where('stats.publishedPosts', 1)
            ->where('stats.realizations', 1)
            ->where('stats.users', 2)
            ->has('recentPosts', 2)
            ->where('recentPosts.0.id', $published->id)
            ->where('recentPosts.0.edit_url', route('dashboard.posts.edit', $published))
            ->has('recentRealizations', 1)
            ->where('recentRealizations.0.id', $realization->id)
            ->where('links.createRealization', route('dashboard.realizations.create'))
            ->where('links.manageRealizations', route('dashboard.realizations.index')));
});
