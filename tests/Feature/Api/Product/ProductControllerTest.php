<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Str;
use Ramsey\Uuid\Uuid;

use function Pest\Laravel\actingAs;

beforeEach(function() {
    // Reset the UUID logic
    Str::createUuidsUsing();
});

test('product can be created', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create([
        'title' => 'Test category'
    ]);
    $uuid = 'eadbfeac-5258-45c2-bab7-ccb9b5ef74f9';
    Str::createUuidsUsing(function () use ($uuid) {
        return Uuid::fromString($uuid);
    });
    $response = actingAs($user)->postJson(route('api.v1.products.store'), [
        'category_uuid' => $category->uuid,
        'title' => 'Test product',
        'price' => 16.69,
        'description' => 'Test description',
    ]);
    $response->assertCreated();

    $response->assertJson([
        'data' => [
            'uuid' => $uuid,
            'title' => 'Test product',
            'price' => 16.69,
            'description' => 'Test description'
        ]
    ]);

    expect(Product::query()->count())->toBe(1);
    $product = Product::query()->first();

    expect($product->title)->toBe('Test product');
    expect($product->price)->toBe(16.69);
});

test('creating product requires title, category and price', function () {
    $user = User::factory()->create();

    $response = actingAs($user)->postJson(route('api.v1.products.store'), [
        'description' => 'Test description',
    ]);
    $response->assertInvalid([
        'category_uuid',
        'title',
        'price'
    ]);

    expect(Product::query()->count())->toBe(0);
});

test('creating product requires existing category', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create([
        'title' => 'Test category'
    ]);

    $response = actingAs($user)->postJson(route('api.v1.products.store'), [
        'category_uuid' => (string) Str::orderedUuid(),
        'title' => 'Test product',
        'price' => 16.69,
        'description' => 'Test description',
    ]);
    $response->assertInvalid([
        'category_uuid',
    ]);

    expect(Product::query()->count())->toBe(0);
});