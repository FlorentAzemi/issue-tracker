<form method="POST" action="{{ $action }}">
    @csrf
    @if($method !== 'POST') @method($method) @endif

    <div class="form-group">
        <label class="form-label">Title <span style="color:var(--danger)">*</span></label>
        <input type="text" name="title" class="form-control" value="{{ old('title', $issue?->title) }}" required>
        @error('title') <p class="form-error">{{ $message }}</p> @enderror
    </div>

    <div class="form-group">
        <label class="form-label">Description</label>
        <textarea name="description" class="form-control">{{ old('description', $issue?->description) }}</textarea>
        @error('description') <p class="form-error">{{ $message }}</p> @enderror
    </div>

    <div class="grid grid-2">
        <div class="form-group">
            <label class="form-label">Status</label>
            <select name="status" class="form-control">
                @foreach(['open','in_progress','closed'] as $s)
                    <option value="{{ $s }}" {{ old('status', $issue?->status ?? 'open') === $s ? 'selected' : '' }}>
                        {{ ucfirst(str_replace('_',' ',$s)) }}
                    </option>
                @endforeach
            </select>
            @error('status') <p class="form-error">{{ $message }}</p> @enderror
        </div>
        <div class="form-group">
            <label class="form-label">Priority</label>
            <select name="priority" class="form-control">
                @foreach(['low','medium','high'] as $p)
                    <option value="{{ $p }}" {{ old('priority', $issue?->priority ?? 'medium') === $p ? 'selected' : '' }}>
                        {{ ucfirst($p) }}
                    </option>
                @endforeach
            </select>
            @error('priority') <p class="form-error">{{ $message }}</p> @enderror
        </div>
    </div>

    <div class="form-group">
        <label class="form-label">Due Date</label>
        <input type="date" name="due_date" class="form-control"
               value="{{ old('due_date', $issue?->due_date?->format('Y-m-d')) }}" style="max-width:200px">
        @error('due_date') <p class="form-error">{{ $message }}</p> @enderror
    </div>

    <div class="flex gap-2 mt-4">
        <button type="submit" class="btn btn-primary">{{ $issue ? 'Save Changes' : 'Create Issue' }}</button>
        <a href="{{ $issue ? route('projects.issues.show', [$project, $issue]) : route('projects.show', $project) }}" class="btn btn-secondary">Cancel</a>
    </div>
</form>
