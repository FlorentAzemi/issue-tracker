@extends('layouts.app')

@section('title', 'Edit Project')

@section('content')
<div class="page-header">
    <h1 class="page-title">Edit Project</h1>
    <a href="{{ route('projects.show', $project) }}" class="btn btn-secondary">← Back</a>
</div>

<div class="card" style="max-width:640px">
    <div class="card-body">
        @include('partials.project-form', ['action' => route('projects.update', $project), 'method' => 'PUT'])
    </div>
</div>
@endsection
