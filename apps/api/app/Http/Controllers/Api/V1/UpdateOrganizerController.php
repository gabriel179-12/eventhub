<?php

namespace App\Http\Controllers\Api\V1;

use App\Domains\Organizers\Actions\UpdateOrganizer;
use App\Http\Controllers\Controller;
use App\Http\Requests\Organizers\UpdateOrganizerRequest;
use App\Http\Resources\Api\V1\OrganizerResource;
use App\Models\Organizer;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

class UpdateOrganizerController extends Controller
{
   public function __invoke(
        UpdateOrganizerRequest $request,
        Organizer $organizer,
        UpdateOrganizer $updateOrganizer,
   ): JsonResponse{
        Gate::authorize('update', $organizer);

        $organizer = $updateOrganizer->handle(
            $organizer,
            $request->validated(),
        );

        return OrganizerResource::make($organizer)->response();
   }
}
