<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Domains\Identity\Actions\RegisterUser;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\Api\V1\UserResource;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final class RegisterController extends Controller
{
    public function __invoke(
        RegisterRequest $request,
        RegisterUser $registerUser,
    ): JsonResponse {
        $user = $registerUser->handle($request->validated());

        return UserResource::make($user)
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }
}