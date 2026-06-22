@extends('layouts.app')

@section('title', 'New Project')

@section('content')
<div class="page-header">
    <h1 class="page-title">New Project</h1>
    <a href="{{ route('projects.index') }}" class="btn btn-secondary">← Back</a>
</div>

<div class="card" style="max-width:640px">
    <div class="card-body">
        @include('partials.project-form', ['project' => null, 'action' => route('projects.store'), 'method' => 'POST'])
    </div>
</div>
@endsection
