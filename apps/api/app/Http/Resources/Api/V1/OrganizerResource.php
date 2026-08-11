<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class OrganizerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return[
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description'=>$this->description,
            'logo_path'=>$this->logo_path,
            'banner_path'=>$this->banner_path,
            'social_links'=>$this->social_links,
            'created_at'=>$this->created_at?->toISOString(),
            'updated_at'=>$this->updated_at?->toISOString(),
        ];
    }
}