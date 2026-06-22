<?php

namespace App\Http\Controllers;

use App\Models\Issue;
use App\Models\Project;
use App\Models\Tag;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class IssueTagController extends Controller
{
    public function toggle(Request $request, Project $project, Issue $issue): JsonResponse
    {
        $request->validate(['tag_id' => ['required', 'exists:tags,id']]);

        $tag = Tag::findOrFail($request->tag_id);
        $issue->tags()->toggle($tag->id);
        $attached = $issue->tags()->where('tags.id', $tag->id)->exists();

        return response()->json([
            'attached' => $attached,
            'tag'      => [
                'id'    => $tag->id,
                'name'  => $tag->name,
                'color' => $tag->color,
            ],
        ]);
    }
}
