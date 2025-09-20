<div class="col-12 col-md-12">
    <section id="tasks-due" class="card p-3 mb-4">
        <ul class="list-group">
            @foreach($tasksDue as $task)
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <a href="{{ route('project.show', ['id' => $task->project_id]) . '?task=' . $task->id }}"
                       class="text-decoration-none w-100">
                        <div class="d-flex flex-wrap justify-content-between">
                            <div class="me-3">
                                <strong>{{ $task->task_title }}</strong>
                                <p class="mb-0 text-muted">{{ $task->project->project_name }}</p>
                            </div>
                            <span class="badge bg-info text-white align-self-start">
                                {{ $task->deadline->diffForHumans(null, true) }}
                            </span>
                        </div>
                    </a>
                </li>
            @endforeach
        </ul>
        <div class="mt-3">
            {{ $tasksDue->links('vendor.pagination.simple-bootstrap-5') }}
        </div>
    </section>
</div>
