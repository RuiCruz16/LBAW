<section class="task-status-col card border-0 shadow-sm">
    <div class="task-status-header card-header bg-light d-flex justify-content-between align-items-center">
        <h5 class="mb-0">{{ $statusTitle }}</h5>
        @if($project->project_status !== 'Archived' && $statusTitle === 'To Do')
            <button
                    class="btn btn-sm btn-outline-primary add-task-popup-trigger"
                    data-status-id="{{ $statusId }}"
                    data-project-id="{{ $project->id }}">
                + Add Task
            </button>
        @endif
    </div>

    <div class="task-list card-body p-3">
        @if($tasks->isEmpty())
            <p class="text-muted fst-italic">No tasks available.</p>
        @else
            <ul class="list-unstyled">
                @foreach ($tasks as $task)
                    <li class="task-item border-bottom py-2 d-flex align-items-center">
                        <span class="badge bg-secondary me-2">{{ $task->priority }}</span>
                        <a href="#offcanvasExample" class="task-link text-decoration-none text-dark d-block" data-task-id="{{ $task->id }}" data-bs-toggle="offcanvas" role="button" aria-controls="offcanvasExample">
                            <strong>{{ $task->task_title }}</strong>
                        </a>
                        @forelse($task->users as $member)
                            @if($member->image)
                                <img src="{{ asset('storage/' . $member->image->image_path) }}" alt="Profile Picture"
                                     class="rounded-circle" width="30" height="30">
                            @else
                                <img src="{{ asset('storage/images/default-profile-picture.jpg') }}" alt="Profile Picture"
                                     class="rounded-circle" width="30" height="30">
                            @endif
                        @empty
                        @endforelse
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</section>

<div class="popup-overlay d-none"></div>

<div class="offcanvas offcanvas-end offcanvas-custom" tabindex="-1" id="offcanvasExample" data-task-id="" aria-labelledby="offcanvasExampleLabel" style="width: 800px;">
    <div class="popup-header px-4 py-3 border-bottom d-flex justify-content-between align-items-center">
        <h5 id="view-task-title" class="mb-0">Not Defined</h5>
        <div class="popup-buttons d-flex align-items-center">
            @if($project->project_status !== 'Archived')
                <button id="menu-button" class="btn me-2 p-2">
                    <i class="bi bi-three-dots"></i>
                </button>
                <div id="task-menu" class="popup-options shadow-sm p-2 bg-white rounded d-none position-absolute">
                    <button id="edit-task" class="dropdown-item text-dark p-2 rounded">
                        <i class="bi bi-pencil-square"></i> Edit Task
                    </button>
                    <button id="delete-task" class="dropdown-item text-danger p-2 rounded">
                        <i class="bi bi-trash"></i> Delete Task
                    </button>
                </div>
            @endif
                <button id="close-popup" class="btn btn-close" data-bs-dismiss="offcanvas"></button>
        </div>
    </div>

    <div class="popup-body p-4">
        <div id="view-task-section">

            <div class="mb-4">
                <h6 class="text-muted">Description</h6>
                <div class="card p-4 shadow-sm border-0">
                    <p id="view-task-description" class="mb-0 fs-5">Not Defined</p>
                </div>
            </div>

            @if($project->project_status !== 'Archived')
                <div class="mb-3">
                    <form id="assign-task-form" method="POST" action="{{ route('assigned-task.create') }}">
                        @csrf
                        <div class="mb-3">
                            <label for="assign-task-user" class="form-label">Assign Task to User:</label>
                            <div class="input-group">
                                <select class="form-control" id="assign-task-user" name="user_id">
                                    <option value="" disabled selected>Select a user</option>
                                    @foreach($project->users as $user)
                                        <option value="{{ $user->id }}">{{ $user->username }}</option>
                                    @endforeach
                                </select>
                                <input type="hidden" name="task_id" id="assign-task-id" value="{{ isset($task) ? $task->id : '' }}">
                                <button type="submit" class="btn btn-primary">Assign Task</button>
                            </div>
                        </div>
                    </form>
                </div>
            @endif

            <div class="mb-3">
                <h6 class="text-muted">Assigned Users</h6>
                <ul id="assigned-users-list" class="list-group">
                    @if(isset($task) && $task->users->isNotEmpty())
                        @foreach($task->users as $assignedUser)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                {{ $assignedUser->username }}
                                <form method="POST" action="{{ route('assigned-task.destroy', ['user_id' => $assignedUser->id, 'task_id' => $task->id]) }}" class="remove-assigned-user-form">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm remove-assign-user" data-user-id="{{ $assignedUser->id }}" data-task-id="{{ $task->id }}">Remove</button>
                                </form>
                            </li>
                        @endforeach
                    @else
                        <p class="text-muted fst-italic">No users assigned to this task.</p>
                    @endif
                </ul>
            </div>

            <div class="row g-4">
                <div class="col-md-3 col-12">
                    <div class="card p-3 text-center shadow-sm border-0">
                        <h6 class="text-muted">Created</h6>
                        <p id="view-task-created-at" class="fs-6 mb-0">Not Defined</p>
                    </div>
                </div>

                <div class="col-md-3 col-12">
                    <div class="card p-3 text-center shadow-sm border-0">
                        <h6 class="text-muted">Deadline</h6>
                        <p id="view-task-deadline" class="fs-6 text-danger mb-0">Not Defined</p>
                    </div>
                </div>

                <div class="col-md-3 col-12">
                    <div class="card p-3 text-center shadow-sm border-0">
                        <h6 class="text-muted">Priority</h6>
                        <p id="view-task-priority" class="fs-6 mb-0">Not Defined</p>
                    </div>
                </div>

                <div class="col-md-3 col-12">
                    <div class="card p-3 text-center shadow-sm border-0">
                        <h6 class="text-muted">Status</h6>
                        <p id="view-task-status" class="fs-6 mb-0">Not Defined</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Comments Section -->
        <div id="comments-section" class="mt-5 pt-4">
            <h6 class="text-muted">Comments</h6>

            @if($project->project_status !== 'Archived')
                <div class="position-relative mt-3">
                    <form id="comment-form" method="POST" action="#">
                        @csrf
                        <div class="mb-3 position-relative">
                    <textarea
                            id="comment-content"
                            name="comment_content"
                            class="form-control pe-5"
                            rows="3"
                            placeholder="Write a comment..."
                            required
                            style="resize: none; overflow-y: auto; height: 60px;"
                    ></textarea>
                            <button
                                    type="submit"
                                    class="btn position-absolute top-50 end-0 translate-middle-y me-3"
                                    id="submit-comment"
                                    style="border: none; background: none;"
                            >
                                <i class="bi bi-send-fill text-muted"></i>
                            </button>
                        </div>
                    </form>
                </div>
            @endif

            <div id="comments-list" class="mt-4">
                @if(isset($task) && $task->comments->isNotEmpty())
                    @foreach($task->comments as $comment)
                        <div class="card mb-2 p-3 shadow-sm border-0">
                            <div class="d-flex justify-content-between">
                                <strong>{{ $comment->user->username }}</strong>
                                <small class="text-muted">{{ $comment->created_at->format('Y-m-d H:i:s') }}</small>
                            </div>
                            <p class="fs-6 mb-1">{{ $comment->comment_content }}</p>
                        </div>
                    @endforeach
                @else
                    <p id="no-comments-message" class="text-muted">No comments found.</p>
                @endif
            </div>
        </div>

        <form id="task-edit-form" class="d-none mt-4" method="POST" action="#">
            {{ csrf_field() }}
            @method('PUT')

            <div class="mb-3">
                <label for="edit-task-title" class="form-label d-flex align-items-center">
                    Task Title
                    <span class="help-icon ms-2" style="position: relative; cursor: default;">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#007bff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="16" x2="12" y2="12"></line>
                    <line x1="12" y1="8" x2="12" y2="8"></line>
                </svg>
                <span class="help-tooltip">
                    Provide a concise title that summarizes the task's objective or goal.
                </span>
            </span>
                </label>
                <input type="text" id="edit-task-title" name="task_title" class="form-control" required>
            </div>

            <div class="mb-3">
                <label for="edit-task-description" class="form-label d-flex align-items-center">
                    Description
                    <span class="help-icon ms-2" style="position: relative; cursor: default;">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#007bff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="16" x2="12" y2="12"></line>
                    <line x1="12" y1="8" x2="12" y2="8"></line>
                </svg>
                <span class="help-tooltip">
                    Describe the task in detail, outlining specific requirements and steps.
                </span>
            </span>
                </label>
                <textarea id="edit-task-description" name="task_description" class="form-control"></textarea>
            </div>

            <div class="mb-3">
                <label for="edit-task-deadline" class="form-label d-flex align-items-center">
                    Deadline
                    <span class="help-icon ms-2" style="position: relative; cursor: default;">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#007bff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="16" x2="12" y2="12"></line>
                    <line x1="12" y1="8" x2="12" y2="8"></line>
                </svg>
                <span class="help-tooltip">
                    Set a deadline to ensure timely completion of the task.
                </span>
            </span>
                </label>
                <input type="date" id="edit-task-deadline" name="deadline" class="form-control">
            </div>

            <div class="mb-3">
                <label for="edit-task-priority" class="form-label d-flex align-items-center">
                    Priority
                    <span class="help-icon ms-2" style="position: relative; cursor: default;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#007bff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="12" y1="16" x2="12" y2="12"></line>
                            <line x1="12" y1="8" x2="12" y2="8"></line>
                        </svg>
                        <span class="help-tooltip">
                            Set the priority level to indicate the importance of this task: Low, Medium, or High.
                        </span>
                    </span>
                </label>
                <select id="edit-task-priority" name="task_priority" class="form-select">
                    <option value="Low">Low</option>
                    <option value="Medium">Medium</option>
                    <option value="High">High</option>
                </select>
            </div>

            <div class="mb-3">
                <label for="edit-task-status" class="form-label d-flex align-items-center">
                    Status
                    <span class="help-icon ms-2" style="position: relative; cursor: default;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#007bff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="12" y1="16" x2="12" y2="12"></line>
                            <line x1="12" y1="8" x2="12" y2="8"></line>
                        </svg>
                        <span class="help-tooltip">
                            Select the current status of this task (e.g., To Do, In Progress, Completed).
                        </span>
                    </span>
                </label>
                <select id="edit-task-status" name="task_status_id" class="form-select">
                </select>
            </div>

            <div class="d-flex justify-content-end mt-3">
                <button type="submit" id="save-task" class="btn btn-primary">Save Changes</button>
                <button type="button" id="cancel-edit" class="btn btn-secondary ms-2">Cancel</button>
            </div>
        </form>
    </div>
</div>
