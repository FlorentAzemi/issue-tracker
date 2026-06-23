@extends('layouts.app')

@section('title', $issue->title)

@section('content')
<div class="page-header">
    <div>
        <p class="text-sm text-muted"><a href="{{ route('projects.show', $project) }}">{{ $project->name }}</a> / Issue</p>
        <h1 class="page-title" style="margin-top:4px">{{ $issue->title }}</h1>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('projects.issues.edit', [$project, $issue]) }}" class="btn btn-secondary">Edit</a>
        <form method="POST" action="{{ route('projects.issues.delete', [$project, $issue]) }}"
              data-confirm-delete
              data-dialog-title="Delete issue"
              data-dialog-message="Delete &quot;{{ $issue->title }}&quot; and all its comments? This cannot be undone.">
            @csrf @method('DELETE')
            <button class="btn btn-danger">Delete</button>
        </form>
    </div>
</div>

<div class="issue-detail-grid">
    {{-- Main column --}}
    <div>
        {{-- Description --}}
        @if($issue->description)
        <div class="card mb-4">
            <div class="card-header"><span class="font-semibold">Description</span></div>
            <div class="card-body" style="white-space:pre-wrap">{{ $issue->description }}</div>
        </div>
        @endif

        {{-- Comments --}}
        <div class="card">
            <div class="card-header">
                <span class="font-semibold">Comments</span>
                <span id="comment-count" class="text-muted text-sm"></span>
            </div>
            <div class="card-body">
                {{-- Comment list --}}
                <p id="no-comments" class="text-muted text-sm" style="display:none">No comments yet.</p>
                <div id="comment-list"></div>
                <div id="load-more-wrap" style="display:none;margin-top:12px;text-align:center">
                    <button id="load-more-btn" class="btn btn-secondary btn-sm">Load more</button>
                </div>

                {{-- Add comment form --}}
                <form id="comment-form" style="margin-top:24px;padding-top:24px;border-top:1px solid var(--border)">
                    @csrf
                    <p class="font-semibold text-sm" style="margin-bottom:12px">Add a comment</p>
                    <div class="form-group">
                        <label class="form-label">Your name <span style="color:var(--danger)">*</span></label>
                        <input type="text" id="author_name" name="author_name" class="form-control" style="max-width:280px">
                        <p class="form-error" id="err-author"></p>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Comment <span style="color:var(--danger)">*</span></label>
                        <textarea id="body" name="body" class="form-control"></textarea>
                        <p class="form-error" id="err-body"></p>
                    </div>
                    <button type="submit" class="btn btn-primary" id="comment-submit">
                        <span id="comment-spinner" class="spinner" style="display:none"></span>
                        Post Comment
                    </button>
                    <p class="form-error mt-2" id="err-general"></p>
                </form>
            </div>
        </div>
    </div>

    {{-- Sidebar --}}
    <div>
        {{-- Meta --}}
        <div class="sidebar-card mb-4">
            <div class="sidebar-card-header">Details</div>
            <div class="sidebar-card-body" style="display:grid;gap:12px">
                <div>
                    <p class="text-sm text-muted">Status</p>
                    <span class="badge badge-{{ $issue->status }}">{{ ucfirst(str_replace('_',' ',$issue->status)) }}</span>
                </div>
                <div>
                    <p class="text-sm text-muted">Priority</p>
                    <span class="badge badge-{{ $issue->priority }}">{{ ucfirst($issue->priority) }}</span>
                </div>
                @if($issue->due_date)
                <div>
                    <p class="text-sm text-muted">Due Date</p>
                    <p class="text-sm font-semibold">{{ $issue->due_date->format('M d, Y') }}</p>
                </div>
                @endif
            </div>
        </div>

        {{-- Tags --}}
        <div class="sidebar-card mb-4">
            <div class="sidebar-card-header" style="display:flex;align-items:center;justify-content:space-between">
                Tags
                <button class="btn btn-ghost btn-sm" onclick="document.getElementById('tag-modal').classList.add('open')">
                    + add
                </button>
            </div>
            <div class="sidebar-card-body">
                <div id="issue-tags" class="tags-panel">
                    @forelse($issue->tags as $tag)
                        <span class="tag-chip" id="tag-chip-{{ $tag->id }}" style="background:{{ $tag->color ?? '#6366f1' }}">
                            {{ $tag->name }}
                        </span>
                    @empty
                        <span class="text-muted text-sm" id="no-tags-msg">No tags attached.</span>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Members --}}
        <div class="sidebar-card">
            <div class="sidebar-card-header" style="display:flex;align-items:center;justify-content:space-between">
                Assignees
                <button class="btn btn-ghost btn-sm" onclick="document.getElementById('member-modal').classList.add('open')">
                    + add
                </button>
            </div>
            <div class="sidebar-card-body">
                <div id="issue-members" style="display:flex;flex-direction:column;gap:8px">
                    @forelse($issue->members as $member)
                        <div class="flex items-center gap-2" id="member-row-{{ $member->id }}">
                            <span style="width:28px;height:28px;border-radius:50%;background:var(--primary);color:#fff;font-size:.75rem;display:flex;align-items:center;justify-content:center;font-weight:600;flex-shrink:0">
                                {{ strtoupper(substr($member->name,0,1)) }}
                            </span>
                            <span class="text-sm">{{ $member->name }}</span>
                        </div>
                    @empty
                        <span class="text-muted text-sm" id="no-members-msg">No assignees yet.</span>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Member modal --}}
<div class="modal-backdrop" id="member-modal" onclick="if(event.target===this)this.classList.remove('open')">
    <div class="modal">
        <div class="modal-header">
            <span class="modal-title">Manage Assignees</span>
            <button class="modal-close" onclick="document.getElementById('member-modal').classList.remove('open')">×</button>
        </div>
        <div class="modal-body">
            <div id="all-members-list" style="display:flex;flex-direction:column;gap:8px">
                @foreach($allUsers as $user)
                    @php $assigned = $issue->members->contains($user->id); @endphp
                    <button class="member-toggle {{ $assigned ? 'assigned' : '' }}"
                            id="modal-member-{{ $user->id }}"
                            data-user-id="{{ $user->id }}"
                            onclick="toggleMember({{ $user->id }}, '{{ addslashes($user->name) }}')"
                            style="display:flex;align-items:center;gap:10px;width:100%;padding:8px 12px;border-radius:6px;border:1px solid {{ $assigned ? 'var(--primary)' : 'var(--border)' }};background:{{ $assigned ? '#eef2ff' : 'var(--surface)' }};cursor:pointer;font-family:inherit;text-align:left">
                        <span style="width:28px;height:28px;border-radius:50%;background:var(--primary);color:#fff;font-size:.75rem;display:flex;align-items:center;justify-content:center;font-weight:600;flex-shrink:0">
                            {{ strtoupper(substr($user->name,0,1)) }}
                        </span>
                        <span class="text-sm" style="flex:1">{{ $user->name }}</span>
                        <span class="text-sm" style="color:var(--text-muted)">{{ $assigned ? '✓' : '' }}</span>
                    </button>
                @endforeach
            </div>
            <p id="member-error" class="form-error mt-2"></p>
        </div>
    </div>
</div>

{{-- Tag modal --}}
<div class="modal-backdrop" id="tag-modal" onclick="if(event.target===this)this.classList.remove('open')">
    <div class="modal">
        <div class="modal-header">
            <span class="modal-title">Manage Tags</span>
            <button class="modal-close" onclick="document.getElementById('tag-modal').classList.remove('open')">×</button>
        </div>
        <div class="modal-body">
            <div class="tags-panel" id="all-tags-list">
                @foreach($allTags as $tag)
                    @php $attached = $issue->tags->contains($tag->id); @endphp
                    <button class="tag-chip tag-toggle {{ $attached ? 'attached' : '' }}"
                            id="modal-tag-{{ $tag->id }}"
                            data-tag-id="{{ $tag->id }}"
                            style="background:{{ $attached ? ($tag->color ?? '#6366f1') : '#e2e8f0' }};color:{{ $attached ? '#fff' : '#334155' }};cursor:pointer;border:none;padding:6px 14px;font-size:.875rem;border-radius:999px;font-family:inherit"
                            onclick="toggleTag({{ $tag->id }}, '{{ addslashes($tag->name) }}', '{{ $tag->color ?? '#6366f1' }}')">
                        {{ $tag->name }}
                    </button>
                @endforeach
            </div>
            <p class="text-muted text-sm mt-4">Click a tag to attach or detach it.</p>
            <p id="tag-error" class="form-error mt-2"></p>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const CSRF = document.querySelector('meta[name="csrf-token"]').content;
const TAG_TOGGLE_URL    = '{{ route('projects.issues.tags.toggle', [$project, $issue]) }}';
const MEMBER_TOGGLE_URL = '{{ route('projects.issues.members.toggle', [$project, $issue]) }}';
const COMMENTS_URL      = '{{ route('projects.issues.comments.index', [$project, $issue]) }}';
const COMMENT_STORE     = '{{ route('projects.issues.comments.store', [$project, $issue]) }}';

// ── Member toggle ─────────────────────────────────────────────────
async function toggleMember(userId, userName) {
    document.getElementById('member-error').textContent = '';
    try {
        const res = await fetch(MEMBER_TOGGLE_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            body: JSON.stringify({ user_id: userId }),
        });
        const data = await res.json();
        if (!res.ok) throw new Error(data.message || 'Error');

        const btn = document.getElementById('modal-member-' + userId);
        const memberRow = document.getElementById('member-row-' + userId);
        const noMsg = document.getElementById('no-members-msg');
        const container = document.getElementById('issue-members');
        const checkEl = btn.querySelector('span:last-child');

        if (data.attached) {
            btn.style.border = '1px solid var(--primary)';
            btn.style.background = '#eef2ff';
            if (checkEl) checkEl.textContent = '✓';
            if (noMsg) noMsg.remove();
            if (!memberRow) {
                const row = document.createElement('div');
                row.className = 'flex items-center gap-2';
                row.id = 'member-row-' + userId;
                row.innerHTML = `<span style="width:28px;height:28px;border-radius:50%;background:var(--primary);color:#fff;font-size:.75rem;display:flex;align-items:center;justify-content:center;font-weight:600;flex-shrink:0">${userName.charAt(0).toUpperCase()}</span><span class="text-sm">${escHtml(userName)}</span>`;
                container.appendChild(row);
            }
        } else {
            btn.style.border = '1px solid var(--border)';
            btn.style.background = 'var(--surface)';
            if (checkEl) checkEl.textContent = '';
            if (memberRow) memberRow.remove();
            const remaining = container.querySelectorAll('[id^="member-row-"]');
            if (remaining.length === 0) {
                const msg = document.createElement('span');
                msg.id = 'no-members-msg';
                msg.className = 'text-muted text-sm';
                msg.textContent = 'No assignees yet.';
                container.appendChild(msg);
            }
        }
    } catch (e) {
        document.getElementById('member-error').textContent = e.message;
    }
}

// ── Tag toggle ────────────────────────────────────────────────────
async function toggleTag(tagId, tagName, tagColor) {
    document.getElementById('tag-error').textContent = '';
    try {
        const res = await fetch(TAG_TOGGLE_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            body: JSON.stringify({ tag_id: tagId }),
        });
        const data = await res.json();
        if (!res.ok) throw new Error(data.message || 'Error');

        const modalBtn = document.getElementById('modal-tag-' + tagId);
        const chip = document.getElementById('tag-chip-' + tagId);
        const noTagsMsg = document.getElementById('no-tags-msg');

        if (data.attached) {
            // Add chip to sidebar
            if (noTagsMsg) noTagsMsg.remove();
            if (!chip) {
                const newChip = document.createElement('span');
                newChip.className = 'tag-chip';
                newChip.id = 'tag-chip-' + tagId;
                newChip.style.background = tagColor;
                newChip.textContent = tagName;
                document.getElementById('issue-tags').appendChild(newChip);
            }
            // Update modal button
            modalBtn.style.background = tagColor;
            modalBtn.style.color = '#fff';
            modalBtn.classList.add('attached');
        } else {
            // Remove chip from sidebar
            if (chip) chip.remove();
            const remaining = document.querySelectorAll('#issue-tags .tag-chip');
            if (remaining.length === 0) {
                const msg = document.createElement('span');
                msg.id = 'no-tags-msg';
                msg.className = 'text-muted text-sm';
                msg.textContent = 'No tags attached.';
                document.getElementById('issue-tags').appendChild(msg);
            }
            // Update modal button
            modalBtn.style.background = '#e2e8f0';
            modalBtn.style.color = '#334155';
            modalBtn.classList.remove('attached');
        }
    } catch (e) {
        document.getElementById('tag-error').textContent = e.message;
    }
}

// ── Comments ──────────────────────────────────────────────────────
let currentPage = 1, lastPage = 1;

async function loadComments(page = 1) {
    const res = await fetch(COMMENTS_URL + '?page=' + page, {
        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF }
    });
    const data = await res.json();
    lastPage = data.last_page;
    currentPage = data.current_page;

    const list = document.getElementById('comment-list');
    const noMsg = document.getElementById('no-comments');
    const countEl = document.getElementById('comment-count');
    countEl.textContent = data.total + ' comment' + (data.total !== 1 ? 's' : '');

    if (page === 1) list.innerHTML = '';

    if (data.total === 0) {
        noMsg.style.display = 'block';
    } else {
        noMsg.style.display = 'none';
        data.data.forEach(c => list.appendChild(buildComment(c)));
    }

    document.getElementById('load-more-wrap').style.display =
        currentPage < lastPage ? 'block' : 'none';
}

function buildComment(c) {
    const div = document.createElement('div');
    div.className = 'comment-item';
    div.innerHTML = `<p style="white-space:pre-wrap">${escHtml(c.body)}</p>
        <p class="comment-meta"><strong>${escHtml(c.author_name)}</strong> · ${escHtml(c.created_at)}</p>`;
    return div;
}

function escHtml(str) {
    return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

document.getElementById('load-more-btn').addEventListener('click', () => loadComments(currentPage + 1));

// ── Comment form submit ────────────────────────────────────────────
document.getElementById('comment-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    clearErrors();

    const author = document.getElementById('author_name').value;
    const body   = document.getElementById('body').value;
    const spinner = document.getElementById('comment-spinner');
    const submitBtn = document.getElementById('comment-submit');

    spinner.style.display = 'inline-block';
    submitBtn.disabled = true;

    try {
        const res = await fetch(COMMENT_STORE, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            body: JSON.stringify({ author_name: author, body }),
        });
        const data = await res.json();

        if (!res.ok) {
            if (data.errors) {
                if (data.errors.author_name) document.getElementById('err-author').textContent = data.errors.author_name[0];
                if (data.errors.body) document.getElementById('err-body').textContent = data.errors.body[0];
            } else {
                document.getElementById('err-general').textContent = data.message || 'Error posting comment.';
            }
            return;
        }

        // Append new comment
        const list = document.getElementById('comment-list');
        const noMsg = document.getElementById('no-comments');
        noMsg.style.display = 'none';
        list.appendChild(buildComment(data));

        // Update count
        const countEl = document.getElementById('comment-count');
        const prev = parseInt(countEl.textContent) || 0;
        const newCount = prev + 1;
        countEl.textContent = newCount + ' comment' + (newCount !== 1 ? 's' : '');

        // Reset form
        document.getElementById('author_name').value = '';
        document.getElementById('body').value = '';
    } catch {
        document.getElementById('err-general').textContent = 'Network error. Please try again.';
    } finally {
        spinner.style.display = 'none';
        submitBtn.disabled = false;
    }
});

function clearErrors() {
    ['err-author','err-body','err-general'].forEach(id => document.getElementById(id).textContent = '');
}

// Initial load
loadComments(1);
</script>
@endpush
