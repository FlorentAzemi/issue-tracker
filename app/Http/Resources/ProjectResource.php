<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->uuid,
            'name'        => $this->name,
            'description' => $this->description,
            'start_date'  => $this->start_date?->toDateString(),
            'deadline'    => $this->deadline?->toDateString(),
            'issues'      => IssueResource::collection($this->whenLoaded('issues')),
            'created_at'  => $this->created_at,
            'updated_at'  => $this->updated_at,
            'deleted_at'  => $this->deleted_at,
        ];
    }
}
