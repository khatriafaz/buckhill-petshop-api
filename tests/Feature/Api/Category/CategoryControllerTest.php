<?php

use App\Models\Category;
use App\Models\User;
use Illuminate\Support\Str;
use Ramsey\Uuid\Uuid;

use function Pest\Laravel\actingAs;

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
