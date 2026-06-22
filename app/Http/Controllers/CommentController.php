<?php

namespace App\Http\Controllers;

use App\Filters\CommentFilters;
use App\Http\Requests\StoreCommentRequest;
use App\Http\Resources\CommentResource;
use App\Models\Comment;
use App\Models\Issue;
use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function index(Project $project, Issue $issue, Request $request, CommentFilters $filters): JsonResponse
    {
        $comments = $issue->comments()
            ->filter($filters)
            ->paginate($request->input('limit', 10));

        return response()->json([
            'data'         => CommentResource::collection($comments->items()),
            'current_page' => $comments->currentPage(),
            'last_page'    => $comments->lastPage(),
            'total'        => $comments->total(),
        ]);
    }

    public function store(StoreCommentRequest $request, Project $project, Issue $issue): JsonResponse
    {
        $comment = $issue->comments()->create($request->validated());

        $comment = $this->loadRelationships($comment, $request);

        return response()->json(new CommentResource($comment), 201);
    }

    public function delete(Project $project, Issue $issue, Comment $comment): JsonResponse
    {
        if ($comment->deleted_at) {
            $comment->forceDelete();
        } else {
            $comment->delete();
        }

        return response()->json(null, 204);
    }

    public function restore(Project $project, Issue $issue, Comment $comment): JsonResponse
    {
        $comment->restore();

        return response()->json(new CommentResource($comment), 200);
    }
}
