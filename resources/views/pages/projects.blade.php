@extends('layouts.app')

@section('header')
    @include('partials.vertical_side_bar')
@endsection

@section('content')
    <div class="container-fluid">
        @include('partials.flash_messages')

        <div class="main-content">
            <div class="d-flex flex-column mb-4">
                <!-- Header Section -->
                <div class="d-flex flex-wrap justify-content-between align-items-center">
                    <div class="d-flex flex-column">
                        <div class="welcome-message me-3">
                            <h2 class="mb-0 fw-bold">Welcome, {{ Auth::user()->username }}!</h2>
                        </div>
                        <div class="current-date mt-2">
                            <h5 class="mb-0 text-muted">{{ \Carbon\Carbon::createFromFormat('U', time())->format('l, F j, Y') }}</h5>
                        </div>
                    </div>

                    <form method="GET" action="{{ route('search') }}" class="d-flex mt-3 mt-md-0" id="search-form" style="max-width: 100%; width: 350px;">
                        <div class="input-group">
                            <span class="input-group-text p-2" id="basic-addon1">
                                <i class="bi bi-search"></i>
                            </span>
                            <input type="search" name="query" id="query" value="{{ request('query') }}" placeholder="Search..." class="form-control" aria-label="Search" aria-describedby="basic-addon1">
                        </div>
                    </form>
                </div>

                <hr class="mt-3 mb-3 text-secondary">
            </div>

            <!-- Main Content Area -->
            <div class="row">
                <!-- Left Column: Tasks Due -->
                <div class="col-md-6 mb-4">
                    <section id="tasks-due">
                        <h4 class="mb-3">Tasks Due</h4>
                        @include('partials.tasks_list')
                    </section>
                </div>

                <!-- Right Column: Notifications -->
                <div class="col-md-6 mb-4">
                    <section id="notifications-section">
                        <h4 class="mb-3">Recent Notifications</h4>
                        <div class="card shadow-sm">
                            <div class="card-body">
                                <div class="list-group">
                                    @if($notifications->isEmpty())
                                        <p class="text-muted">No Notifications to show.</p>
                                    @else
                                        @foreach($notifications as $notification)
                                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                                <div>
                                                    @if($notification instanceof App\Notifications\TaskCompletedNotification)
                                                        <strong>{{ $notification->task->task_title }}</strong>
                                                        <small class="d-block text-muted">
                                                            Completed in "{{ $notification->task->project->project_name }}"
                                                        </small>
                                                    @elseif($notification instanceof App\Notifications\TaskAssignedNotification)
                                                        <strong>{{ $notification->task->task_title }}</strong>
                                                        <small class="d-block text-muted">
                                                            Assigned in "{{ $notification->task->project->project_name }}"
                                                        </small>
                                                    @endif
                                                </div>
                                                <span class="badge bg-info text-white">
                                                    {{ $notification->created_at->diffForHumans() }}
                                                </span>
                                            </div>
                                        @endforeach
                                    @endif
                                </div>
                            </div>
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </div>
@endsection
