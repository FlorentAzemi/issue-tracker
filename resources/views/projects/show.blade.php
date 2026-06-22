@extends('layouts.app')

@section('title', $project->name)

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">{{ $project->name }}</h1>
        @if($project->description)
            <p class="page-subtitle">{{ $project->description }}</p>
        @endif
        @if($project->start_date || $project->deadline)
            <p class="text-sm text-muted mt-2">
                @if($project->start_date) Started {{ $project->start_date->format('M d, Y') }} @endif
                @if($project->start_date && $project->deadline) · @endif
                @if($project->deadline) Deadline {{ $project->deadline->format('M d, Y') }} @endif
            </p>
        @endif
    </div>
    <div class="flex gap-2">
        <a href="{{ route('projects.issues.create', $project) }}" class="btn btn-primary">+ New Issue</a>
        @can('update', $project)
        <a href="{{ route('projects.edit', $project) }}" class="btn btn-secondary">Edit</a>
        @endcan
    </div>
</div>

{{-- Filters --}}
<form method="GET" class="filter-bar" id="filter-form">
    <select name="status" onchange="this.form.submit()" class="form-control" style="width:auto">
        <option value="">All Statuses</option>
        @foreach(['open','in_progress','closed'] as $s)
            <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ ucfirst(str_replace('_',' ',$s)) }}</option>
        @endforeach
    </select>

    <select name="priority" onchange="this.form.submit()" class="form-control" style="width:auto">
        <option value="">All Priorities</option>
        @foreach(['low','medium','high'] as $p)
            <option value="{{ $p }}" {{ request('priority') === $p ? 'selected' : '' }}>{{ ucfirst($p) }}</option>
        @endforeach
    </select>

    <select name="tag" onchange="this.form.submit()" class="form-control" style="width:auto">
        <option value="">All Tags</option>
        @foreach($tags as $tag)
            <option value="{{ $tag->uuid }}" {{ request('tag') == $tag->uuid ? 'selected' : '' }}>{{ $tag->name }}</option>
        @endforeach
    </select>

    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search issues…"
           class="form-control" style="width:220px" id="search-input" autocomplete="off">

    @if(request()->hasAny(['status','priority','tag','search']))
        <a href="{{ route('projects.show', $project) }}" class="btn btn-ghost btn-sm">Clear</a>
    @endif
</form>

@if($issues->isEmpty())
    <div class="empty-state">
        <div style="font-size:3rem;">📋</div>
        <p>No issues found. <a href="{{ route('projects.issues.create', $project) }}">Create one</a>.</p>
    </div>
@else
    <div class="card">
        <table class="table" id="issues-table">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Status</th>
                    <th>Priority</th>
                    <th>Tags</th>
                    <th>Due</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($issues as $issue)
                <tr>
                    <td>
                        <a href="{{ route('projects.issues.show', [$project, $issue]) }}" class="font-semibold" style="color:var(--text)">
                            {{ $issue->title }}
                        </a>
                    </td>
                    <td><span class="badge badge-{{ $issue->status }}">{{ ucfirst(str_replace('_',' ',$issue->status)) }}</span></td>
                    <td><span class="badge badge-{{ $issue->priority }}">{{ ucfirst($issue->priority) }}</span></td>
                    <td>
                        <div class="flex flex-wrap gap-2">
                            @foreach($issue->tags as $tag)
                                <span class="tag-chip" style="background:{{ $tag->color ?? '#6366f1' }}">{{ $tag->name }}</span>
                            @endforeach
                        </div>
                    </td>
                    <td class="text-muted text-sm">{{ $issue->due_date ? $issue->due_date->format('M d') : '—' }}</td>
                    <td>
                        <div class="flex gap-2">
                            <a href="{{ route('projects.issues.edit', [$project, $issue]) }}" class="btn btn-ghost btn-sm">Edit</a>
                            <form method="POST" action="{{ route('projects.issues.delete', [$project, $issue]) }}"
                                  data-confirm-delete
                                  data-dialog-title="Delete issue"
                                  data-dialog-message="Delete &quot;{{ $issue->title }}&quot;? This cannot be undone.">
                                @csrf @method('DELETE')
                                <button class="btn btn-danger btn-sm">Del</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="pagination mt-4">{{ $issues->links('partials.pagination') }}</div>
@endif
@endsection

@push('scripts')
<script>
const PROJECT_URL = '{{ route('projects.show', $project) }}';

function renderRows(issues) {
    const tbody = document.querySelector('#issues-table tbody');
    if (!tbody) return;
    if (issues.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;color:var(--text-muted);padding:32px">No issues found.</td></tr>';
        return;
    }
    tbody.innerHTML = issues.map(i => `
        <tr>
            <td><a href="${i.url}" class="font-semibold" style="color:var(--text)">${escHtml(i.title)}</a></td>
            <td><span class="badge badge-${i.status}">${ucLabel(i.status)}</span></td>
            <td><span class="badge badge-${i.priority}">${ucLabel(i.priority)}</span></td>
            <td><div class="flex flex-wrap gap-2">${i.tags.map(t=>`<span class="tag-chip" style="background:${t.color}">${escHtml(t.name)}</span>`).join('')}</div></td>
            <td class="text-muted text-sm">${i.due || '—'}</td>
            <td></td>
        </tr>`).join('');
}
function escHtml(s) { return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }
function ucLabel(s) { return s.replace('_',' ').replace(/\b\w/g,c=>c.toUpperCase()); }

let searchTimer;
document.getElementById('search-input').addEventListener('input', function() {
    clearTimeout(searchTimer);
    const q = this.value.trim();
    if (!q) { document.getElementById('filter-form').submit(); return; }
    searchTimer = setTimeout(async () => {
        const params = new URLSearchParams(new FormData(document.getElementById('filter-form')));
        const res = await fetch(PROJECT_URL + '?' + params, { headers: { 'Accept': 'application/json' } });
        const data = await res.json();
        renderRows(data.issues);
    }, 350);
});
</script>
@endpush
