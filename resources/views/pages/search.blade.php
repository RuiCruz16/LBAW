@extends('layouts.app')

@section('header')
    @include('partials.vertical_side_bar')
@endsection

@section('content')
    <div class="container-fluid">
        @include('partials.flash_messages')

        <div class="main-content">
            <div class="d-flex justify-content-center mb-4">
                <form method="GET" class="d-flex w-50" id="search-form">
                    <div class="input-group">
                        <span class="input-group-text" id="basic-addon1">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="search" name="query" id="query" value="{{ $query }}" placeholder="Search..." class="form-control custom-search-height" aria-label="Search" aria-describedby="basic-addon1">
                    </div>
                </form>
            </div>

            <div class="tabs mb-4">
                <ul class="nav nav-pills">
                    <li class="nav-item">
                        <a class="nav-link active" id="all-tab" data-bs-toggle="pill" href="#all">All ({{ $users->count() + $tasks->count() + $projects->count() }})</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="users-tab" data-bs-toggle="pill" href="#users">Users ({{ $users->count() }})</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="tasks-tab" data-bs-toggle="pill" href="#tasks">Tasks ({{ $tasks->count() }})</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="projects-tab" data-bs-toggle="pill" href="#projects">Projects ({{ $projects->count() }})</a>
                    </li>
                </ul>
            </div>

            <div class="tab-content">
                <!-- All Tab (Mixed results) -->
                <div class="tab-pane fade show active" id="all">
                    <h4>All Results</h4>
                    <div class="list-group">
                        @foreach($users as $user)
                            <a href="/profile/{{ $user->id }}" class="list-group-item list-group-item-action">
                                <strong>{{ $user->username }}</strong> <small>({{ $user->email }})</small>
                            </a>
                        @endforeach

                        @foreach($tasks as $task)
                            <a href="{{ $task['project_url'] }}" class="list-group-item list-group-item-action">
                                <strong>{{ $task['task_title'] }}</strong>
                                <p class="mb-1 text-muted">Project: <strong>{{ $task['project_name'] }}</strong></p>
                            </a>
                        @endforeach

                        @foreach($projects as $project)
                            <a href="{{ $project['url'] }}" class="list-group-item list-group-item-action">
                                <strong>{{ $project['project_name'] }}</strong>
                                <p class="mb-1 text-muted">{{ Str::limit($project['project_description'], 100) }}</p>
                            </a>
                        @endforeach
                    </div>
                </div>

                <!-- Users Tab -->
                <div class="tab-pane fade" id="users">
                    <h4>Users ({{ $users->count() }})</h4>
                    <div class="d-flex mb-3">
                        <form method="GET" class="w-100 d-flex" id="users-filter-form">
                            <input type="text" name="user_filter" value="{{ request('user_filter') }}" placeholder="Filter by username" class="form-control" />
                            <button type="submit" class="btn btn-primary ms-2">Apply Filter</button>
                        </form>
                    </div>
                    @if($users->isNotEmpty())
                        <div class="list-group">
                            @foreach($users as $user)
                                <a href="/profile/{{ $user->id }}" class="list-group-item list-group-item-action">
                                    <strong>{{ $user->username }}</strong> <small>({{ $user->email }})</small>
                                </a>
                            @endforeach
                        </div>
                    @else
                        <p>No users found.</p>
                    @endif
                </div>

                <!-- Tasks Tab -->
                <div class="tab-pane fade" id="tasks">
                    <h4>Tasks ({{ $tasks->count() }})</h4>
                    <div class="d-flex mb-3">
                        <form method="GET" class="w-100 d-flex" id="tasks-filter-form">
                            <input type="text" name="task_filter" value="{{ request('task_filter') }}" placeholder="Filter by task title" class="form-control" />
                            <button type="submit" class="btn btn-primary ms-2">Apply Filter</button>
                        </form>
                    </div>
                    @if($tasks->isNotEmpty())
                        <div class="list-group">
                            @foreach($tasks as $task)
                                <a href="{{ $task['project_url'] }}" class="list-group-item list-group-item-action">
                                    <strong>{{ $task['task_title'] }}</strong>
                                    <p class="mb-1 text-muted">Project: <strong>{{ $task['project_name'] }}</strong></p> <!-- Display project name -->
                                </a>
                            @endforeach
                        </div>
                    @else
                        <p>No tasks found.</p>
                    @endif
                </div>

                <!-- Projects Tab -->
                <div class="tab-pane fade" id="projects">
                    <h4>Projects ({{ $projects->count() }})</h4>
                    <div class="d-flex mb-3">
                        <form method="GET" class="w-100 d-flex" id="projects-filter-form">
                            <input type="text" name="project_filter" value="{{ request('project_filter') }}" placeholder="Filter by project name" class="form-control" />
                            <button type="submit" class="btn btn-primary ms-2">Apply Filter</button>
                        </form>
                    </div>
                    @if($projects->isNotEmpty())
                        <div class="list-group">
                            @foreach($projects as $project)
                                <a href="{{ $project['url'] }}" class="list-group-item list-group-item-action">
                                    <strong>{{ $project['project_name'] }}</strong>
                                    <p class="mb-1 text-muted">{{ Str::limit($project['project_description'], 100) }}</p>
                                </a>
                            @endforeach
                        </div>
                    @else
                        <p>No projects found.</p>
                    @endif
                </div>
            </div>

            @if ($users->isEmpty() && $tasks->isEmpty() && $projects->isEmpty())
                <div class="text-center mt-5">
                    <div class="mb-3">
                        <i class="bi bi-emoji-dizzy" style="font-size: 50px; color: #6c757d;"></i>
                    </div>
                    <h3 class="mt-3">Oops... No results found!</h3>
                    <p class="text-muted">Try again with different keywords or browse our projects.</p>
                </div>
            @endif
        </div>
    </div>
@endsection