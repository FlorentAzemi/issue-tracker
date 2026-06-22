@extends('layouts.app')

@section('title', 'Edit Issue')

@section('content')
<div class="page-header">
    <h1 class="page-title">Edit Issue</h1>
    <a href="{{ route('projects.issues.show', [$project, $issue]) }}" class="btn btn-secondary">← Back</a>
</div>

<div class="card" style="max-width:640px">
    <div class="card-body">
        @include('partials.issue-form', ['action' => route('projects.issues.update', [$project, $issue]), 'method' => 'PUT'])
    </div>
</div>
@endsection
