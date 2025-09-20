<div id="assigned-task-list">
    @foreach ($tasknotifications as $tasknotification)
        @if($tasknotification->notify_type == 'NewAssignment')
            <a href="#" class="list-group-item list-group-item-action px-4 py-3 border-0 mb-3 shadow-sm rounded-3">
                <div class="d-flex align-items-center">
                    <i class="bi bi-clipboard-check text-success me-3" style="font-size: 1.7rem;">
                    </i>
                    <div>
                        <strong>You were assigned to task '{{ $tasknotification->task->task_title }}' in '{{ $tasknotification->task->project->project_name }}'</strong>
                        <small class="text-muted d-block">{{ $tasknotification->created_at->diffForHumans() }}</small>
                    </div>
                    <form action="{{ route('notifications.destroy', $tasknotification->id) }}" method="POST" class="delete-notification-form ms-auto">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                    </form>
                </div>
            </a>
        @endif
    @endforeach
    <div class="d-flex justify-content-center mt-4" id="pagination-assigned-task">
        {{ $tasknotifications->links('pagination::bootstrap-4') }}
    </div>
</div>
