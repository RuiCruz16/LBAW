<div class="modal fade" id="addProjectModal" tabindex="-1" aria-labelledby="addProjectModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg"> <!-- Added responsive classes -->
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addProjectModalLabel">Add a New Project</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="POST" action="{{ route('projects.create') }}">
                    @csrf
                    <!-- Project Name -->
                    <div class="mb-3">
                        <label for="projectname" class="form-label d-flex align-items-center">
                            Project Name
                            <span class="help-icon ms-2" style="position: relative; cursor: default;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#007bff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <line x1="12" y1="16" x2="12" y2="12"></line>
                                    <line x1="12" y1="8" x2="12" y2="8"></line>
                                </svg>
                                <span class="help-tooltip">
                                    Define a clear and concise name for your project to help identify it easily. You can change the project name later if necessary.
                                </span>
                            </span>
                        </label>
                        <input id="projectname" type="text" name="projectname" value="{{ old('projectname') }}" required class="form-control" placeholder="Project Name">
                        @if ($errors->has('projectname'))
                            <span class="error text-danger">{{ $errors->first('projectname') }}</span>
                        @endif
                    </div>

                    <!-- Description -->
                    <div class="mb-3">
                        <label for="description" class="form-label d-flex align-items-center">
                            Description
                            <span class="help-icon ms-2" style="position: relative; cursor: default;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#007bff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <line x1="12" y1="16" x2="12" y2="12"></line>
                                    <line x1="12" y1="8" x2="12" y2="8"></line>
                                </svg>
                                <span class="help-tooltip">
                                    Summarize the key purpose, features, and scope of your project to provide a clear understanding of its goals and direction.
                                </span>
                            </span>
                        </label>
                        <textarea id="description" name="description" required class="form-control" placeholder="Description" rows="4">{{ old('description') }}</textarea>
                        @if ($errors->has('description'))
                            <span class="error text-danger">{{ $errors->first('description') }}</span>
                        @endif
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="btn btn-primary w-100">Add Project</button>
                </form>
            </div>
        </div>
    </div>
</div>
