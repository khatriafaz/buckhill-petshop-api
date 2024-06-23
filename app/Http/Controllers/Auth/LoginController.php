<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\UserResource;
use App\Services\JwtService;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function login(LoginRequest $request, JwtService $jwtService)
    {
        Auth::attempt($request->validated());

        $user = $request->user();

        $token = $jwtService->issue($user);

        $user->recordToken($token);

        return [
            'user' => UserResource::make($user),
            'token' => $token->toString()
        ];
    }

    public function logout()
    {
        Auth::logout();

        return response()->noContent();
    }
}
