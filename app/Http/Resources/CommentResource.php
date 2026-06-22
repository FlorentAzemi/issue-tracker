<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CommentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->uuid,
            'author_name' => $this->author_name,
            'body'        => $this->body,
            'issue'       => new IssueResource($this->whenLoaded('issue')),
            'created_at'  => $this->created_at,
            'updated_at'  => $this->updated_at,
        ];
    }
}
