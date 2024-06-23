<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateProfileRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class UserProfileController extends Controller
{
    public function show(Request $request)
    {
        return UserResource::make($request->user());
    }

    public function update(UpdateProfileRequest $request)
    {
        $data = $request->validated();
        $avatar = Arr::get($data, 'avatar');

        if ($avatar && Storage::exists($avatar) === false) {
            unset($data['avatar']);
        }

        $request->user()->update($data);

        return UserResource::make($request->user()->fresh());
    }

    public function destroy(Request $request)
    {
        $user = $request->user();

        DB::transaction(function() use ($user) {
            $user->jwtTokens()->delete();
            $user->delete();
        });

        return response()->noContent();
    }
}
