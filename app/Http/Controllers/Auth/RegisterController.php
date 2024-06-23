<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\JwtService;
use Illuminate\Auth\Events\Registered;

class RegisterController extends Controller
{
    public function store(RegisterRequest $request, JwtService $jwtService)
    {
        $user = User::query()->create(
            $request->validated()
        );

        event(new Registered($user));

        $token = $jwtService->issue($user);

        $user->recordToken($token);

        return [
            'user' => UserResource::make($user),
            'token' => $token->toString()
        ];
    }
}
