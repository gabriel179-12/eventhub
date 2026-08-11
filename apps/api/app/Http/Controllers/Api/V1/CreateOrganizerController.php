<?php

namespace App\Http\Controllers\Api\V1;

use App\Domains\Organizers\Actions\CreateOrganizer;
use App\Http\Controllers\Controller;
use App\Http\Requests\Organizers\StoreOrganizerRequest;
use App\Http\Resources\Api\V1\OrganizerResource;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final class CreateOrganizerController extends Controller
{
    public function __invoke(
        StoreOrganizerRequest $request,
        CreateOrganizer $createOrganizer,
    ): JsonResponse{
        $organizer = $createOrganizer->handle(
            $request->user(),
            $request->validated(),
        );

        return OrganizerResource::make($organizer)
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }
}