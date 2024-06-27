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
