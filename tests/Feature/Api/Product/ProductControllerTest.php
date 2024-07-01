<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;
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

test('product can be updated', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create([
        'title' => 'Test category'
    ]);
    $product = Product::factory()->create([
        'category_uuid' => $category->uuid,
        'title' => 'Test product',
        'price' => 16.69,
        'description' => 'Test description',
    ]);

    $response = actingAs($user)->putJson(route('api.v1.products.update', $product), [
        'title' => 'Test updated product',
        'price' => 18.69,
        'description' => 'Test updated description',
    ]);
    $response->assertOk();

    $response->assertJson([
        'data' => [
            'uuid' => $product->uuid,
            'title' => 'Test updated product',
            'price' => 18.69,
            'description' => 'Test updated description'
        ]
    ]);

    $product->refresh();
    expect($product->title)->toBe('Test updated product');
    expect($product->price)->toBe(18.69);
    expect($product->description)->toBe('Test updated description');
});


test('product update fields are required when present', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create([
        'title' => 'Test category'
    ]);
    $product = Product::factory()->create([
        'category_uuid' => $category->uuid,
        'title' => 'Test product',
        'price' => 16.69,
        'description' => 'Test description',
    ]);

    $response = actingAs($user)->putJson(route('api.v1.products.update', $product), [
        'title' => '',
        'price' => null,
    ]);
    $response->assertInvalid(['title', 'price']);

    $product->refresh();
    expect($product->title)->toBe('Test product');
    expect($product->price)->toBe(16.69);
    expect($product->description)->toBe('Test description');
});

test('a non-existing product cannot be updated', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create([
        'title' => 'Test category'
    ]);
    $product = Product::factory()->create([
        'category_uuid' => $category->uuid,
        'title' => 'Test product',
        'price' => 16.69,
        'description' => 'Test description',
    ]);

    $response = actingAs($user)->putJson(route('api.v1.products.update', (string) Str::orderedUuid()), [
        'title' => '',
        'price' => null,
    ]);
    $response->assertNotFound();

    $product->refresh();
    expect($product->title)->toBe('Test product');
    expect($product->price)->toBe(16.69);
    expect($product->description)->toBe('Test description');
});

test('product can be deleted', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create();
    $product = Product::factory()->for($category)->create();

    $response = actingAs($user)->deleteJson(route('api.v1.products.destroy', $product));
    $response->assertNoContent();

    $existsFromSoftDelete = Product::query()->where('uuid', $product->uuid)->exists();
    expect($existsFromSoftDelete)->toBe(false);

    $existsInDB = DB::table((new Product())->getTable())->where('uuid', $product->uuid)->exists();
    expect($existsInDB)->toBe(true);
});

test('product delete endpoint throws not found for non-existing products', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create();
    $product = Product::factory()->for($category)->create();

    $response = actingAs($user)->deleteJson(route('api.v1.products.destroy', (string) Str::orderedUuid()));
    $response->assertNotFound();

    $exists = Product::query()->where('uuid', $product->uuid)->exists();
    expect($exists)->toBe(true);
});

test('can get a single product', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create();
    $product = Product::factory()->for($category)->create();

    $response = actingAs($user)->getJson(route('api.v1.products.show', $product));
    $response->assertOk();

    $product = $product->fresh('category');

    $response->assertJson([
        'data' => [
            'uuid' => $product->uuid,
            'category' => [
                'uuid' => $product->category->uuid,
                'title' => $product->category->title,
                'slug' => $product->category->slug,
            ],
            'title' => $product->title,
            'description' => $product->description
        ]
    ]);

    // assertJson does not case the amount with no decimal to float,
    // so casting it and adding additional assertion
    expect((float) $response->json('data.price'))->toBe($product->price);
});

test('get single product throws not-found for non-existing product', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create();
    $product = Product::factory()->for($category)->create();

    $response = actingAs($user)->getJson(route('api.v1.products.show', (string) Str::orderedUuid()));
    $response->assertNotFound();
});

test('products can be listed', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create();
    $products = Product::factory()->for($category)->count(10)->create();

    $response = actingAs($user)->getJson(route('api.v1.products.index'));
    $response->assertOk();

    // response must include categories as well
    $products = $products->fresh('category');

    $response->assertJson([
        'data' => $products->map->toArray()->toArray()
    ]);
});

test('ensure products request is paginated', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create();
    $products = Product::factory()->for($category)->count(100)->create();

    $response = actingAs($user)->getJson(route('api.v1.products.index'));
    $response->assertOk();

    $response->assertJsonCount((new Product())->getPerPage(), 'data');
});

test('products request accepts limit parameter', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create();
    $products = Product::factory()->for($category)->count(100)->create();

    $response = actingAs($user)->getJson(route('api.v1.products.index', ['limit' => 10]));
    $response->assertOk();

    $response->assertJsonCount(10, 'data');
});

test('products request accepts page parameter', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create();
    $products = Product::factory()->for($category)->count(100)->create();

    $response = actingAs($user)->getJson(route('api.v1.products.index', ['page' => 1]));
    $response->assertOk();

    $response->assertJson([
        'data' => array_values($products->slice(0, 15)->map->toArray()->toArray()),
    ]);

    $response = actingAs($user)->getJson(route('api.v1.products.index', ['page' => 2]));
    $response->assertOk();

    $response->assertJson([
        'data' => array_values($products->slice(15, 15)->map->toArray()->toArray()),
    ]);
});

test('products request accepts sort_by parameter', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create();
    $products = Product::factory()->for($category)->count(100)->create();

    $response = actingAs($user)->getJson(route('api.v1.products.index', [
        'sort_by' => ['field' => 'id', 'direction' => 'desc']
    ]));
    $response->assertOk();

    $response->assertJson([
        'data' => array_values($products->reverse()->slice(0, 15)->map->toArray()->toArray()),
    ]);

    $response = actingAs($user)->getJson(route('api.v1.products.index', [
        'sort_by' => ['field' => 'title', 'direction' => 'asc']
    ]));
    $response->assertOk();

    $response->assertJson([
        'data' => array_values($products->sortBy('title')->slice(0, 15)->map->toArray()->toArray()),
    ]);
});

test('products request sort_by.field is allowed for specified fields', function ($field) {
    $user = User::factory()->create();
    $category = Category::factory()->create();
    $products = Product::factory()->for($category)->count(100)->create();

    $response = actingAs($user)->getJson(route('api.v1.products.index', [
        'sort_by' => ['field' => $field, 'direction' => 'desc']
    ]));
    $response->assertOk();

    $response = actingAs($user)->getJson(route('api.v1.products.index', [
        'sort_by' => ['field' => 'asdsa', 'direction' => 'desc']
    ]));
    $response->assertInvalid(['sort_by.field']);
})->with(['id', 'title', 'price']);

test('products request sort_by.direction is either asc|desc', function ($direction) {
    $user = User::factory()->create();
    $category = Category::factory()->create();
    $products = Product::factory()->for($category)->count(100)->create();

    $response = actingAs($user)->getJson(route('api.v1.products.index', [
        'sort_by' => ['field' => 'id', 'direction' => $direction]
    ]));
    $response->assertOk();

    $response = actingAs($user)->getJson(route('api.v1.products.index', [
        'sort_by' => ['field' => 'id', 'direction' => 'asdasd']
    ]));
    $response->assertInvalid(['sort_by.direction']);
})->with(['asc', 'desc']);
