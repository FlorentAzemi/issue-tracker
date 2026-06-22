@extends('layouts.app')

@section('title', 'Projects')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Projects</h1>
        <p class="page-subtitle">{{ $projects->total() }} project{{ $projects->total() !== 1 ? 's' : '' }}</p>
    </div>
    <a href="{{ route('projects.create') }}" class="btn btn-primary">+ New Project</a>
</div>

@if($projects->isEmpty())
    <div class="empty-state">
        <div style="font-size:3rem;">📂</div>
        <p>No projects yet. <a href="{{ route('projects.create') }}">Create your first one</a>.</p>
    </div>
@else
    <div class="grid grid-3">
        @foreach($projects as $project)
        <div class="project-card">
            <h3><a href="{{ route('projects.show', $project) }}">{{ $project->name }}</a></h3>
            @if($project->description)
                <p class="text-muted text-sm" style="margin-bottom:12px;overflow:hidden;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;">
                    {{ $project->description }}
                </p>
            @endif
            <div class="flex items-center gap-2 mt-2 mb-1" style="font-size:.8125rem;color:var(--text-muted)">
                <span style="width:22px;height:22px;border-radius:50%;background:var(--primary);color:#fff;font-size:.7rem;display:inline-flex;align-items:center;justify-content:center;font-weight:600;flex-shrink:0">{{ strtoupper(substr($project->user->name ?? '?', 0, 1)) }}</span>
                <span>{{ $project->user->name ?? '—' }}</span>
            </div>
            <div class="flex items-center justify-between mt-2 text-sm text-muted">
                <span>{{ $project->issues_count }} issue{{ $project->issues_count !== 1 ? 's' : '' }}</span>
                @if($project->deadline)
                    <span>Due {{ $project->deadline->format('M d, Y') }}</span>
                @endif
            </div>
            <div class="flex gap-2 mt-4">
                <a href="{{ route('projects.show', $project) }}" class="btn btn-secondary btn-sm">View</a>
                @can('update', $project)
                <a href="{{ route('projects.edit', $project) }}" class="btn btn-ghost btn-sm">Edit</a>
                @endcan
                @can('delete', $project)
                <form method="POST" action="{{ route('projects.delete', $project) }}" style="margin-left:auto"
                      data-confirm-delete
                      data-dialog-title="Delete project"
                      data-dialog-message="Delete &quot;{{ $project->name }}&quot; and all its issues? This cannot be undone.">
                    @csrf @method('DELETE')
                    <button class="btn btn-danger btn-sm">Delete</button>
                </form>
                @endcan
            </div>
        </div>
        @endforeach
    </div>

    <div class="pagination mt-4">
        {{ $projects->links('partials.pagination') }}
    </div>
@endif
@endsection
