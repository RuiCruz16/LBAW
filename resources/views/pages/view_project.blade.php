@extends('layouts.app')

@section('header')
    @include('partials.vertical_side_bar')
@endsection

@section('content')
    @include('partials.flash_messages')
    <div id="flash-message-container"></div>

    <div class="container py-4">
        <article class="project">
            <div class="project-header mb-4 pb-3 border-bottom d-flex justify-content-between align-items-center">
                <div>
                    <div class="d-flex align-items-center">
                        <h2 class="h4 fw-bold text-dark m-0 d-flex align-items-center">
                            {{ $project->project_name }}
                            @if ($project->project_status !== 'Archived' && !(Auth::user()->isAdmin()))
                                <button
                                        class="btn btn-sm btn-favorite ms-3 d-flex align-items-center"
                                        data-favorited="{{ $project->isFavoritedBy(Auth::user()) ? 'true' : 'false' }}"
                                        data-project-id="{{ $project->id }}"
                                        style="background: none; border: none; cursor: pointer;">
                                    <i class="bi {{ $project->isFavoritedBy(Auth::user()) ? 'bi-star-fill' : 'bi-star' }}"
                                       style="font-size: 1.5rem; color: {{ $project->isFavoritedBy(Auth::user()) ? '#c9a227' : 'black' }};"></i>
                                    <span class="ms-2 text-dark" style="font-size: 1.2rem;">{{ $project->isFavoritedBy(Auth::user()) ? 'Unfavorite' : 'Favorite' }}</span>
                                </button>
                            @endif
                        </h2>
                    </div>
                    <p class="text-muted mb-0 mt-2">{{ $project->project_description }}</p>
                    <div id="flash-message-container" class="mt-2"></div>
                </div>

                <div class="d-flex align-items-center">
                    @if(Auth::check() && $project->project_status === 'Archived')
                        <div class="dropdown">
                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle custom-dropdown" type="button" id="projectActionsDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-three-dots"></i>
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="projectActionsDropdown">
                                <li>
                                    <form action="{{ route('projects.unarchive', $project->id) }}" method="POST">
                                        @csrf
                                        @method('POST')
                                        <button type="submit" class="dropdown-item text-primary" style="background: transparent; border: none;">
                                            <i class="bi bi-arrow-up"></i> Unarchive Project
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    @elseif(Auth::check() && (Auth::user()->id === $project->creator_id || Auth::user()->isProjectCoordinator($project) || Auth::user()->isAdmin()))
                        <div class="d-flex align-items-center">
                            <a href="{{ route('projects.invite', $project->id) }}" class="btn btn-sm btn-primary">+ Add User</a>
                            <div class="dropdown ms-3">
                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle custom-dropdown" type="button" id="projectActionsDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="bi bi-three-dots"></i>
                                </button>
                                <ul class="dropdown-menu" aria-labelledby="projectActionsDropdown">
                                    <li>
                                        <form action="{{ route('projects.edit', $project->id) }}" method="GET">
                                            <button type="submit" class="dropdown-item">
                                                <i class="bi bi-pencil-square"></i> Edit Project
                                            </button>
                                        </form>
                                    </li>
                                    <li>
                                        <form action="/projects/{{ $project->id }}/archive" method="POST">
                                            @csrf
                                            @method('POST')
                                            <button type="submit" class="dropdown-item text-danger" style="background: transparent; border: none;">
                                                <i class="bi bi-archive"></i> Archive Project
                                            </button>
                                        </form>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    @elseif(Auth::check() && !(Auth::user()->id === $project->creator_id || Auth::user()->isProjectCoordinator($project) || Auth::user()->isAdmin()))
                        <form action="{{ route('projects.leave', $project->id) }}" method="POST" class="d-inline me-2" id="leaveProjectForm">
                            @csrf
                            @method('POST')
                            <button type="button" class="btn btn-sm btn-outline-danger ms-3" data-bs-toggle="modal" data-bs-target="#leaveProjectModal">
                                <i class="bi bi-box-arrow-right"></i> Leave Project
                            </button>
                        </form>
                    @endif
                </div>

            </div>

            <div class="row">
                <section class="task-board col-md-9 mb-5">
                    <div class="row gy-4">
                        @foreach ($tasksGroupedByStatus as $statusId => $tasks)
                            <div class="col-md-4">
                                @include('partials.task_status_column', [
                                    'statusTitle' => data_get($statuses[$statusId], 'task_status_name', 'Unknown'),
                                    'tasks' => $tasks,
                                    'statusId' => $statusId,
                                    'project' => $project,
                                ])
                            </div>
                        @endforeach
                    </div>
                </section>
                <div id="projectId" data-project-id="{{ $project->id }}" style="display:none;"></div>
                <section class="project-users col-md-3">
                    <h4 class="fw-bold mb-3">Contributors</h4>

                    <input type="text" id="userSearch" class="form-control mb-3" placeholder="Search Users...">

                    <ul id="userList" class="list-group">

                    </ul>

                    @if($project->users->count() > 5)
                        <button id="showMoreBtn" class="btn btn-link mt-3">Show More</button>
                    @endif
                </section>
            </div>
        </article>
    </div>

    @include('partials.add_user_popup')
    @include('partials.add_task_popup', ['projectId' => $project->id])
    @include('partials.leave_project')

@endsection