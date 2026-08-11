<?php

namespace App\Http\Controllers\Api\V1;

use App\Domains\Organizers\Actions\AddOrganizerMember;
use App\Http\Controllers\Controller;
use App\Http\Requests\Organizers\StoreOrganizerMemberRequest;
use App\Http\Resources\Api\V1\UserResource;
use App\Models\Organizer;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class AddOrganizerMemberController extends Controller
{
    public function __invoke(
        StoreOrganizerMemberRequest $request,
        Organizer $organizer,
        AddOrganizerMember $addOrganizerMember,
    ): JsonResponse{
        Gate::authorize('manageTeam', $organizer);

        $member = $addOrganizerMember-> handle(
            $organizer,
            $request->validated(),
        );

        return UserResource::make($member)
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }
}
