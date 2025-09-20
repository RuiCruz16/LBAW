<div id="add-task-popup" class="popup d-none position-fixed top-0 start-0 w-100 h-100">
    <!-- Overlay -->
    <div class="popup-overlay position-absolute w-100 h-100 bg-dark opacity-50"></div>

    <!-- Popup Content -->
    <div class="popup-content position-absolute top-50 start-50 translate-middle bg-white shadow-lg rounded"
         style="width: 90%; max-width: 400px;">
        <!-- Popup Header -->
        <div class="popup-header px-4 py-3 border-bottom d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Add New Task</h5>
            <button id="close-add-task-popup" class="btn btn-close"></button>
        </div>

        <!-- Popup Body -->
        <div class="popup-body p-4">
            <form id="add-task-form" method="POST" action="{{ route('tasks.store', ['project' => $project->id]) }}">
                {{ csrf_field() }}
                <input type="hidden" id="popup-task-status-id" name="task_status_id">

                <!-- Task Title -->
                <div class="mb-3">
                    <label for="task-title" class="form-label">Task Title</label>
                    <input type="text" id="task-title" name="task_title" class="form-control" placeholder="Enter task title" required>
                </div>

                <!-- Deadline -->
                <div class="mb-3">
                    <label for="task-deadline" class="form-label">Deadline</label>
                    <input type="date" id="task-deadline" name="deadline" class="form-control" required>
                </div>

                <!-- Description -->
                <div class="mb-3">
                    <label for="task-description" class="form-label">Description</label>
                    <textarea id="task-description" name="task_description" class="form-control" placeholder="Enter task description"></textarea>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn btn-primary w-100">Add Task</button>
            </form>
        </div>
    </div>
</div>
