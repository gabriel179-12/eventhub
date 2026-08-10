<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class AuthenticatedUserController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        return UserResource::make($request->user())->response();
    }
}