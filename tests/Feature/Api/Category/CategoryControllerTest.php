<?php

use App\Models\Category;
use App\Models\User;
use Illuminate\Support\Str;
use Ramsey\Uuid\Uuid;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\postJson;

test('category can be created', function () {
    $user = User::factory()->create();
    $uuid = 'eadbfeac-5258-45c2-bab7-ccb9b5ef74f9';
    Str::createUuidsUsing(function () use ($uuid) {
        return Uuid::fromString($uuid);
    });
    $response = actingAs($user)->postJson(route('api.v1.categories.store'), [
        'title' => 'Test category'
    ]);
    $response->assertCreated()
        ->assertJson([
            'data' => [
                'uuid' => $uuid,
                'title' => 'Test category'
            ]
        ]);

    $category = Category::first();
    expect($category->title)->toBe('Test category');
    expect($category->slug)->toBe('test-category');
});


test('category cannot be created without logged in user', function () {
    $response = postJson(route('api.v1.categories.store'), [
        'title' => 'Test category'
    ]);
    $response->assertUnauthorized();

    expect(Category::count())->toBe(0);
});

test('category title is required', function () {
    $user = User::factory()->create();
    $response = actingAs($user)->postJson(route('api.v1.categories.store'), [
        'title' => ''
    ]);
    $response->assertInvalid(['title' => 'required']);

    expect(Category::count())->toBe(0);
});

test('category slug is created', function () {
    $user = User::factory()->create();
    $response = actingAs($user)->postJson(route('api.v1.categories.store'), [
        'title' => 'First Category'
    ]);
    $response->assertCreated()
        ->assertJson([
            'data' => [
                'slug' => 'first-category'
            ]
        ]);

    $response = actingAs($user)->postJson(route('api.v1.categories.store'), [
        'title' => 'Another Category'
    ]);
    $response->assertCreated()
        ->assertJson([
            'data' => [
                'slug' => 'another-category'
            ]
        ]);
});

test('categories can be listed', function () {
    $user = User::factory()->create();
    $categories = Category::factory()->count(10)->create();
    $response = actingAs($user)->getJson(route('api.v1.categories.index'));
    $response->assertOk();

    $response->assertJson([
        'data' => $categories->map->toArray()->toArray()
    ]);
});

test('ensure categories request is paginated', function () {
    $user = User::factory()->create();
    $categories = Category::factory()->count(100)->create();
    $response = actingAs($user)->getJson(route('api.v1.categories.index'));
    $response->assertOk();

    $response->assertJsonCount((new Category())->getPerPage(), 'data');
});

test('categories request accepts limit parameter', function () {
    $user = User::factory()->create();
    $categories = Category::factory()->count(100)->create();
    $response = actingAs($user)->getJson(route('api.v1.categories.index', ['limit' => 10]));
    $response->assertOk();

    $response->assertJsonCount(10, 'data');
});

test('categories request accepts page parameter', function () {
    $user = User::factory()->create();
    $categories = Category::factory()->count(100)->create();
    $response = actingAs($user)->getJson(route('api.v1.categories.index', ['page' => 1]));
    $response->assertOk();

    $response->assertJson([
        'data' => array_values($categories->slice(0, 15)->map->toArray()->toArray()),
    ]);

    $response = actingAs($user)->getJson(route('api.v1.categories.index', ['page' => 2]));
    $response->assertOk();

    $response->assertJson([
        'data' => array_values($categories->slice(15, 15)->map->toArray()->toArray()),
    ]);
});

test('categories request accepts sort_by parameter', function () {
    $user = User::factory()->create();
    $categories = Category::factory()->count(100)->create();
    $response = actingAs($user)->getJson(route('api.v1.categories.index', [
        'sort_by' => ['field' => 'id', 'direction' => 'desc']
    ]));
    $response->assertOk();

    $response->assertJson([
        'data' => array_values($categories->reverse()->slice(0, 15)->map->toArray()->toArray()),
    ]);

    $response = actingAs($user)->getJson(route('api.v1.categories.index', [
        'sort_by' => ['field' => 'title', 'direction' => 'asc']
    ]));
    $response->assertOk();

    $response->assertJson([
        'data' => array_values($categories->sortBy('title')->slice(0, 15)->map->toArray()->toArray()),
    ]);
});

test('category can be updated', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create([
        'title' => 'Original title'
    ]);

    $response = actingAs($user)->putJson(route('api.v1.categories.update', $category), [
        'title' => 'Updated title'
    ]);
    $response->assertOk();

    $response->assertJson([
        'data' => [
            'uuid' => $category->uuid,
            'title' => 'Updated title',
        ]
    ]);

    expect(Category::query()->count())->toBe(1);
});

test('category slug stays original on update', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create([
        'title' => 'Original title'
    ]);

    $response = actingAs($user)->putJson(route('api.v1.categories.update', $category), [
        'title' => 'Updated title'
    ]);
    $response->assertOk();

    $response->assertJson([
        'data' => [
            'uuid' => $category->uuid,
            'title' => 'Updated title',
            'slug' => $category->slug
        ]
    ]);

    expect(Category::query()->count())->toBe(1);
});

test('cannot update non existing category', function () {
    $user = User::factory()->create();

    $response = actingAs($user)->putJson(route('api.v1.categories.update', Str::uuid()), [
        'title' => 'Updated title'
    ]);
    $response->assertNotFound();
});

test('category can be deleted', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create([
        'title' => 'Original title'
    ]);

    $response = actingAs($user)->deleteJson(route('api.v1.categories.destroy', $category));
    $response->assertNoContent();

    expect(Category::query()->count())->toBe(0);
});

test('cannot delete non-existent category', function () {
    $user = User::factory()->create();

    $response = actingAs($user)->deleteJson(route('api.v1.categories.destroy', Str::orderedUuid()));
    $response->assertNotFound();
});
