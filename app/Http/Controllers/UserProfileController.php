<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use Illuminate\Http\Request;

class UserProfileController extends Controller
{
    public function __invoke(Request $request)
    {
        return UserResource::make($request->user());
    }
}
