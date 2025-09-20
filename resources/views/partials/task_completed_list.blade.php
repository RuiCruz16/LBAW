<div id="task-completed-list">
    @if($taskCompletedNotifications->count() > 0)
        @foreach ($taskCompletedNotifications as $notification)
            <a href="#" class="list-group-item list-group-item-action px-4 py-3 border-0 mb-3 shadow-sm rounded-3">
                <div class="d-flex align-items-center">
                    <i class="bi bi-check-circle-fill text-success me-3" style="font-size: 1.7rem;"></i>
                    <div>
                        <strong>
                            Task '{{ $notification->task->task_title }}' in '{{ $notification->task->project->project_name }}' has been completed.
                        </strong>
                        <small class="text-muted d-block">{{ $notification->created_at->diffForHumans() }}</small>
                    </div>
                </div>
            </a>
        @endforeach

        <div class="d-flex justify-content-center mt-4" id="pagination-task-completed">
            {{ $taskCompletedNotifications->links() }}
        </div>
    @else
        <p class="text-center">You have no task completed notifications.</p>
    @endif
</div>