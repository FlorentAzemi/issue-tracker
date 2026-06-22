<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\Issue;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;

class IssueMemberController extends Controller
{
    public function toggle(Request $request, Project $project, Issue $issue)
    {
        $request->validate(['user_id' => ['required', 'exists:users,id']]);

        $userId = $request->integer('user_id');
        $result = $issue->members()->toggle($userId);

        $attached = count($result['attached']) > 0;
        $user     = User::find($userId);

        return response()->json([
            'attached' => $attached,
            'user'     => new UserResource($user),
        ]);
    }
}
