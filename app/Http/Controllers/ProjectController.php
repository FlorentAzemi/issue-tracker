<?php

namespace App\Http\Controllers;

use App\Filters\ProjectFilters;
use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Models\Project;
use App\Models\Tag;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function index(Request $request, ProjectFilters $filters)
    {
        $projects = Project::filter($filters)->with('user');

        if (!$request->has('sort')) {
            $projects = $projects->orderBy('projects.created_at', 'desc');
        }

        return view('projects.index', [
            'projects' => $projects->withCount('issues')->paginate($request->input('limit', 12)),
        ]);
    }

    public function create()
    {
        return view('projects.create');
    }

    public function store(StoreProjectRequest $request)
    {
        $project = Project::create(array_merge($request->validated(), ['user_id' => auth()->id()]));

        $project = $this->loadRelationships($project, $request);

        return redirect()->route('projects.show', $project)->with('success', 'Project created.');
    }

    public function show(Project $project, Request $request)
    {
        $query = $project->issues()->with('tags');

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
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $issues = $query->latest()->paginate(10)->withQueryString();
        $tags   = Tag::orderBy('name')->get();

        if ($request->wantsJson()) {
            return response()->json([
                'issues' => $issues->map(fn($i) => [
                    'uuid'    => $i->uuid,
                    'title'   => $i->title,
                    'status'  => $i->status,
                    'priority'=> $i->priority,
                    'url'     => route('projects.issues.show', [$project, $i]),
                    'tags'    => $i->tags->map(fn($t) => ['name' => $t->name, 'color' => $t->color]),
                ]),
            ]);
        }

        return view('projects.show', compact('project', 'issues', 'tags'));
    }

    public function edit(Project $project)
    {
        $this->authorize('update', $project);
        return view('projects.edit', compact('project'));
    }

    public function update(UpdateProjectRequest $request, Project $project)
    {
        $this->authorize('update', $project);
        $project->update($request->validated());

        $project = $this->loadRelationships($project, $request);

        return redirect()->route('projects.show', $project)->with('success', 'Project updated.');
    }

    public function delete(Project $project)
    {
        $this->authorize('delete', $project);
        if ($project->deleted_at) {
            $project->forceDelete();
        } else {
            $project->delete();
        }

        return redirect()->route('projects.index')->with('success', 'Project deleted.');
    }

    public function restore(Project $project)
    {
        $this->authorize('restore', $project);
        $project->restore();

        return redirect()->route('projects.index')->with('success', 'Project restored.');
    }
}
