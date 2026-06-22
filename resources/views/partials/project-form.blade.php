<form method="POST" action="{{ $action }}">
    @csrf
    @if($method !== 'POST') @method($method) @endif

    <div class="form-group">
        <label class="form-label">Project Name <span style="color:var(--danger)">*</span></label>
        <input type="text" name="name" class="form-control" value="{{ old('name', $project?->name) }}" required>
        @error('name') <p class="form-error">{{ $message }}</p> @enderror
    </div>

    <div class="form-group">
        <label class="form-label">Description</label>
        <textarea name="description" class="form-control">{{ old('description', $project?->description) }}</textarea>
        @error('description') <p class="form-error">{{ $message }}</p> @enderror
    </div>

    <div class="grid grid-2">
        <div class="form-group">
            <label class="form-label">Start Date</label>
            <input type="date" name="start_date" class="form-control"
                   value="{{ old('start_date', $project?->start_date?->format('Y-m-d')) }}">
            @error('start_date') <p class="form-error">{{ $message }}</p> @enderror
        </div>
        <div class="form-group">
            <label class="form-label">Deadline</label>
            <input type="date" name="deadline" class="form-control"
                   value="{{ old('deadline', $project?->deadline?->format('Y-m-d')) }}">
            @error('deadline') <p class="form-error">{{ $message }}</p> @enderror
        </div>
    </div>

    <div class="flex gap-2 mt-4">
        <button type="submit" class="btn btn-primary">{{ $project ? 'Save Changes' : 'Create Project' }}</button>
        <a href="{{ $project ? route('projects.show', $project) : route('projects.index') }}" class="btn btn-secondary">Cancel</a>
    </div>
</form>
