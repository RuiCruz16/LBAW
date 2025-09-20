@extends('layouts.app')

@section('header')
    @include('partials.vertical_side_bar')
@endsection

@section('content')
<div class="container my-5">
    <!-- Page Title -->
    <div class="row">
        <div class="col-12 text-center mb-4">
            <h2 class="display-4">Task Completed Notifications</h2>
            <p class="text-muted">See all your completed tasks notifications</p>
        </div>
    </div>

    <!-- Task Completed Notifications -->
    <div class="row mb-4">
        <div class="col-12">
            @if($taskCompletedNotifications->isEmpty())
                <p class="text-muted">You have no completed task notifications yet.</p>
            @else
                <div class="list-group mb-3" id="task-completed-list">
                @foreach ($taskCompletedNotifications as $notification)
                    @if( $notification->notify_type == 'TaskCompleted' )
                        <a href="#" class="list-group-item list-group-item-action d-flex align-items-center px-4 py-3 mb-3 shadow-sm rounded-3">
                            <i class="bi bi-check-circle-fill text-success me-3 fs-2"></i>
                            <div>
                                <strong>Task '{{ $notification->task->task_title }}' in the project '{{ $notification->task->project->project_name }}' was completed.</strong>
                                <small class="text-muted d-block">{{ $notification->created_at->diffForHumans() }}</small>
                            </div>
                            <form action="{{ route('task-completed-notifications.destroy', $notification->id) }}" method="POST" class="ms-auto">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                            </form>
                        </a>
                    @endif
                @endforeach

                </div>
                <!-- Pagination -->
                <div class="d-flex justify-content-center mt-4" id="pagination-task-completed">
                    {{ $taskCompletedNotifications->links('pagination::bootstrap-4') }}
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Include JavaScript -->
<script src="{{ asset('js/pagination-task-notifications.js') }}"></script>
@endsection
