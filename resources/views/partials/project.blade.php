<div class="box-project">
    <article class="project card shadow-sm">
        <div class="card-body">
            <h2 class="card-title">{{ $project->project_name }}</h2>
            <p class="card-text">{{ Str::limit($project->project_description, 100) }}</p>
            <a href="{{ route('project.show', ['id' => $project->id]) }}" class="btn btn-primary mt-3">View Project</a>
        </div>
    </article>
</div>
