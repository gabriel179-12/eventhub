<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\OrganizerResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class ListMyOrganizersController extends Controller
{
    public function __invoke(Request $request): AnonymousResourceCollection
    {
        $organizers = $request->user()
            ->organizers()
            ->orderBy('organizers.name')
            ->get();
        return OrganizerResource::collection($organizers);
    }
}