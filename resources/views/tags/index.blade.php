@extends('layouts.app')

@section('title', 'Tags')

@section('content')
<div class="page-header">
    <h1 class="page-title">Tags</h1>
</div>

<div class="grid grid-2" style="align-items:start">
    {{-- Tag list --}}
    <div class="card">
        <div class="card-header"><span class="font-semibold">All Tags</span></div>
        @if($tags->isEmpty())
            <div class="card-body text-muted text-sm">No tags yet.</div>
        @else
        <table class="table">
            <thead>
                <tr>
                    <th>Tag</th>
                    <th>Color</th>
                    <th>Issues</th>
                </tr>
            </thead>
            <tbody>
                @foreach($tags as $tag)
                <tr>
                    <td>
                        <span class="tag-chip" style="background:{{ $tag->color ?? '#6366f1' }}">{{ $tag->name }}</span>
                    </td>
                    <td>
                        @if($tag->color)
                            <span class="flex items-center gap-2">
                                <span style="display:inline-block;width:16px;height:16px;border-radius:50%;background:{{ $tag->color }}"></span>
                                <code style="font-size:.8rem">{{ $tag->color }}</code>
                            </span>
                        @else
                            <span class="text-muted text-sm">—</span>
                        @endif
                    </td>
                    <td class="text-muted text-sm">{{ $tag->issues_count }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>

    {{-- Create tag form --}}
    <div class="card">
        <div class="card-header"><span class="font-semibold">New Tag</span></div>
        <div class="card-body">
            <form method="POST" action="{{ route('tags.store') }}">
                @csrf
                <div class="form-group">
                    <label class="form-label">Name <span style="color:var(--danger)">*</span></label>
                    <input type="text" name="name" class="form-control" value="{{ old('name') }}"
                           placeholder="e.g. bug, enhancement" style="max-width:280px">
                    @error('name') <p class="form-error">{{ $message }}</p> @enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Color</label>
                    <div class="flex items-center gap-2">
                        <input type="color" name="color" value="{{ old('color', '#6366f1') }}"
                               style="height:36px;width:48px;padding:2px;border:1px solid var(--border);border-radius:6px;cursor:pointer">
                        <input type="text" id="color-text" class="form-control" value="{{ old('color', '#6366f1') }}"
                               style="max-width:120px" placeholder="#rrggbb">
                    </div>
                    @error('color') <p class="form-error">{{ $message }}</p> @enderror
                </div>
                <button type="submit" class="btn btn-primary">Create Tag</button>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Sync color picker <-> text input
const colorPicker = document.querySelector('input[type="color"]');
const colorText   = document.getElementById('color-text');
colorPicker.addEventListener('input', () => colorText.value = colorPicker.value);
colorText.addEventListener('input', () => {
    if (/^#[0-9a-fA-F]{6}$/.test(colorText.value)) colorPicker.value = colorText.value;
});
// Make sure the hidden name="color" gets the text value on submit
colorText.name = 'color';
colorPicker.removeAttribute('name');
</script>
@endpush
