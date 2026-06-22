<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreIssueRequest;
use App\Http\Requests\UpdateIssueRequest;
use App\Models\Issue;
use App\Models\Project;
use App\Models\Tag;
use Illuminate\Http\Request;

class IssueController extends Controller
{
    public function index(Request $request)
    {
        $query = Issue::with(['project', 'tags'])->latest();

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }
        if ($priority = $request->input('priority')) {
            $query->where('priority', $priority);
        }
        if ($tag = $request->input('tag')) {
            $query->whereHas('tags', fn($q) => $q->where('tags.uuid', $tag));
        }
        if ($search = $request->input('search')) {
            $query->where(fn($q) => $q->where('title', 'like', "%{$search}%")
                                      ->orWhere('description', 'like', "%{$search}%"));
        }

        $tags = Tag::orderBy('name')->get();
        $issues = $query->paginate($request->input('limit', 15))->withQueryString();

        if ($request->wantsJson()) {
            return response()->json([
                'issues' => $issues->map(fn($i) => [
                    'uuid'    => $i->uuid,
                    'title'   => $i->title,
                    'status'  => $i->status,
                    'priority'=> $i->priority,
                    'url'     => route('issues.show', $i),
                    'project' => $i->project ? ['name' => $i->project->name, 'url' => route('projects.show', $i->project)] : null,
                    'tags'    => $i->tags->map(fn($t) => ['name' => $t->name, 'color' => $t->color]),
                ]),
            ]);
        }

        return view('issues.index', compact('issues', 'tags'));
    }

    public function showGlobal(Issue $issue, Request $request)
    {
        $issue->loadMissing('project');
        return redirect()->route('projects.issues.show', [$issue->project, $issue]);
    }

    public function create(Project $project)
    {
        return view('issues.create', compact('project'));
    }

    public function store(StoreIssueRequest $request, Project $project)
    {
        $issue = $project->issues()->create($request->validated());

        $issue = $this->loadRelationships($issue, $request);

        return redirect()->route('projects.issues.show', [$project, $issue])
            ->with('success', 'Issue created.');
    }

    public function show(Project $project, Issue $issue, Request $request)
    {
        $issue = $this->loadRelationships($issue->load(['tags', 'project', 'members']), $request);
        $allTags = Tag::orderBy('name')->get();
        $allUsers = \App\Models\User::orderBy('name')->get();

        return view('issues.show', compact('project', 'issue', 'allTags', 'allUsers'));
    }

    public function edit(Project $project, Issue $issue)
    {
        return view('issues.edit', compact('project', 'issue'));
    }

    public function update(UpdateIssueRequest $request, Project $project, Issue $issue)
    {
        $issue->update($request->validated());

        $issue = $this->loadRelationships($issue, $request);

        return redirect()->route('projects.issues.show', [$project, $issue])
            ->with('success', 'Issue updated.');
    }

    public function delete(Project $project, Issue $issue)
    {
        if ($issue->deleted_at) {
            $issue->forceDelete();
        } else {
            $issue->delete();
        }

        return redirect()->route('projects.show', $project)
            ->with('success', 'Issue deleted.');
    }

    public function restore(Project $project, Issue $issue)
    {
        $issue->restore();

        return redirect()->route('projects.issues.show', [$project, $issue])
            ->with('success', 'Issue restored.');
    }
}
