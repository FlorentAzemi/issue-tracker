@extends('layouts.app')

@section('title', 'New Issue')

@section('content')
<div class="page-header">
    <h1 class="page-title">New Issue — {{ $project->name }}</h1>
    <a href="{{ route('projects.show', $project) }}" class="btn btn-secondary">← Back</a>
</div>

<div class="card" style="max-width:640px">
    <div class="card-body">
        @include('partials.issue-form', ['issue' => null, 'action' => route('projects.issues.store', $project), 'method' => 'POST'])
    </div>
</div>
@endsection
