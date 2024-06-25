<?php

use App\Models\File;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Ramsey\Uuid\Uuid;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;

test('file can be uploaded', function () {
    Storage::fake('pet-shop');
    $uuid = 'eadbfeac-5258-45c2-bab7-ccb9b5ef74f9';
    Str::createUuidsUsing(function () use ($uuid) {
        return Uuid::fromString($uuid);
    });
    $user = User::factory()->create();
    $file = UploadedFile::fake()->image('avatar.jpg');

    $response = actingAs($user)->post(route('api.v1.files.store'), [
        'file' => $file
    ]);
    $response->assertOk();
    $response->assertJson(['data' => ['uuid' => $uuid]]);

    Storage::disk('pet-shop')->assertExists($file->hashName());
    $exists = File::query()->where('name', $file->getClientOriginalName())->exists();

    expect($exists)->toBe(true);
});

test('file is required to upload', function() {
    Storage::fake('pet-shop');
    $user = User::factory()->create();

    $response = actingAs($user)->post(route('api.v1.files.store'), []);

    $response->assertInvalid(['file']);

    $count = File::query()->count();
    expect($count)->toBe(0);
});

test('file more than 5mb is not allowed', function() {
    Storage::fake('pet-shop');
    $user = User::factory()->create();
    $file = UploadedFile::fake()->image('avatar.jpg')->size(5121); // More than 5mb

    $response = actingAs($user)->post(route('api.v1.files.store'), [
        'file' => $file
    ]);

    $response->assertInvalid(['file']);

    Storage::disk('pet-shop')->assertMissing($file->hashName());
    $exists = File::query()->where('name', $file->getClientOriginalName())->exists();

    expect($exists)->toBe(false);
});

test('file cannot be uploaded without auth user', function() {
    $file = UploadedFile::fake()->image('avatar.jpg');
    $response = postJson(route('api.v1.files.store'), [
        'file' => $file
    ]);

    $response->assertUnauthorized();

    Storage::disk('pet-shop')->assertMissing($file->hashName());
    $exists = File::query()->where('name', $file->getClientOriginalName())->exists();
    expect($exists)->toBe(false);
});

test('file can be downloaded', function() {
    Storage::fake('pet-shop');
    $user = User::factory()->create();
    $uuid = 'eadbfeac-5258-45c2-bab7-ccb9b5ef74f9';
    Str::createUuidsUsing(function () use ($uuid) {
        return Uuid::fromString($uuid);
    });
    $file = UploadedFile::fake()->image('avatar.jpg');
    $path = Storage::disk('pet-shop')->putFileAs('', $file, $file->hashName());

    File::query()->create([
        'name' => $file->getClientOriginalName(),
        'path' => $path,
        'size' => Storage::disk('pet-shop')->size($path),
        'type' => Storage::disk('pet-shop')->mimeType($path),
    ]);

    $response = actingAs($user)->get(route('api.v1.files.show', $uuid));
    $response->assertOk();
    $response->assertDownload($file->getClientOriginalName());
});

test('throws error if file not found for download', function() {
    Storage::fake('pet-shop');
    $user = User::factory()->create();
    $uuid = 'eadbfeac-5258-45c2-bab7-ccb9b5ef74f9';
    Str::createUuidsUsing(function () use ($uuid) {
        return Uuid::fromString($uuid);
    });
    $file = UploadedFile::fake()->image('avatar.jpg');
    $path = Storage::disk('pet-shop')->putFileAs('', $file, $file->hashName());

    File::query()->create([
        'name' => $file->getClientOriginalName(),
        'path' => $path,
        'size' => Storage::disk('pet-shop')->size($path),
        'type' => Storage::disk('pet-shop')->mimeType($path),
    ]);

    $response = actingAs($user)->get(route('api.v1.files.show', 'eadbfeac-5258-45c2-bab7-ccb9b5ef74f8'));
    $response->assertNotFound();
});

test('cannot download file without auth user', function() {
    Storage::fake('pet-shop');
    $uuid = 'eadbfeac-5258-45c2-bab7-ccb9b5ef74f9';
    Str::createUuidsUsing(function () use ($uuid) {
        return Uuid::fromString($uuid);
    });
    $file = UploadedFile::fake()->image('avatar.jpg');
    $path = Storage::disk('pet-shop')->putFileAs('', $file, $file->hashName());

    File::query()->create([
        'name' => $file->getClientOriginalName(),
        'path' => $path,
        'size' => Storage::disk('pet-shop')->size($path),
        'type' => Storage::disk('pet-shop')->mimeType($path),
    ]);

    $response = getJson(route('api.v1.files.show', $uuid));
    $response->assertUnauthorized();
});
