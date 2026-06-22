@extends('layouts.app')

@section('title', 'All Issues')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">All Issues</h1>
        <p class="page-subtitle">{{ $issues->total() }} total</p>
    </div>
</div>

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
    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search…"
           class="form-control" style="width:220px" id="search-input" autocomplete="off">
    @if(request()->hasAny(['status','priority','tag','search']))
        <a href="{{ route('issues.index') }}" class="btn btn-ghost btn-sm">Clear</a>
    @endif
</form>

@if($issues->isEmpty())
    <div class="empty-state" id="empty-state"><div style="font-size:3rem">📋</div><p>No issues match your filters.</p></div>
@endif
<div class="card" id="issues-card" style="{{ $issues->isEmpty() ? 'display:none' : '' }}">
    <table class="table" id="issues-table">
        <thead>
            <tr>
                <th>Title</th>
                <th>Project</th>
                <th>Status</th>
                <th>Priority</th>
                <th>Tags</th>
                <th>Due</th>
            </tr>
        </thead>
        <tbody>
            @foreach($issues as $issue)
            <tr>
                <td>
                    <a href="{{ route('issues.show', $issue) }}" style="color:var(--text);font-weight:500">
                        {{ $issue->title }}
                    </a>
                </td>
                <td>
                    @if($issue->project)
                        <a href="{{ route('projects.show', $issue->project) }}" class="text-muted text-sm">{{ $issue->project->name }}</a>
                    @else
                        <span class="text-muted text-sm">—</span>
                    @endif
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
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
<div class="pagination mt-4" id="pagination-wrap">{{ $issues->links('partials.pagination') }}</div>
@endsection

@push('scripts')
<script>
const ISSUES_URL = '{{ route('issues.index') }}';

function escHtml(s) { return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }
function ucLabel(s) { return s.replace('_',' ').replace(/\b\w/g,c=>c.toUpperCase()); }

function renderRows(issues) {
    const tbody = document.querySelector('#issues-table tbody');
    const empty = document.getElementById('empty-state');
    const card  = document.getElementById('issues-card');
    const pagination = document.getElementById('pagination-wrap');
    if (issues.length === 0) {
        card.style.display = 'none';
        pagination.style.display = 'none';
        if (!empty) {
            const d = document.createElement('div');
            d.id = 'empty-state';
            d.className = 'empty-state';
            d.innerHTML = '<div style="font-size:3rem">📋</div><p>No issues match your filters.</p>';
            card.insertAdjacentElement('beforebegin', d);
        } else { empty.style.display = ''; }
        return;
    }
    if (empty) empty.style.display = 'none';
    card.style.display = '';
    pagination.style.display = 'none'; // hide server pagination during AJAX search
    tbody.innerHTML = issues.map(i => `
        <tr>
            <td><a href="${i.url}" style="color:var(--text);font-weight:500">${escHtml(i.title)}</a></td>
            <td>${i.project ? `<a href="${i.project.url}" class="text-muted text-sm">${escHtml(i.project.name)}</a>` : '<span class="text-muted text-sm">—</span>'}</td>
            <td><span class="badge badge-${i.status}">${ucLabel(i.status)}</span></td>
            <td><span class="badge badge-${i.priority}">${ucLabel(i.priority)}</span></td>
            <td><div class="flex flex-wrap gap-2">${i.tags.map(t=>`<span class="tag-chip" style="background:${t.color}">${escHtml(t.name)}</span>`).join('')}</div></td>
            <td class="text-muted text-sm">—</td>
        </tr>`).join('');
}

let t;
document.getElementById('search-input').addEventListener('input', function() {
    clearTimeout(t);
    const q = this.value.trim();
    if (!q) { document.getElementById('filter-form').submit(); return; }
    t = setTimeout(async () => {
        const params = new URLSearchParams(new FormData(document.getElementById('filter-form')));
        const res = await fetch(ISSUES_URL + '?' + params, { headers: { 'Accept': 'application/json' } });
        const data = await res.json();
        renderRows(data.issues);
    }, 350);
});
</script>
@endpush
