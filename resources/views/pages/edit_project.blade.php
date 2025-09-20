@extends('layouts.app')

@section('header')
    @include('partials.vertical_side_bar')
@endsection

@section('content')
    @include('partials.flash_messages')
    <div id="flash-message-container"></div>

    <div class="container-fluid py-4">
        <article class="project">
            <div class="project-header mb-4 pb-3 border-bottom d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="h4 fw-bold text-dark">Edit Project</h2>
                </div>
            </div>

            <div class="row">
                <section class="form-section col-md-9 mb-5">
                    <div class="card shadow-sm border-0">
                        <div class="card-body">
                            <form action="{{ route('project.update', $project->id) }}" method="POST">
                                @csrf
                                @method('PUT')

                                <div class="mb-4">
                                    <label for="project_name" class="form-label fw-bold">Project Name</label>
                                    <input type="text"
                                           class="form-control @error('project_name') is-invalid @enderror"
                                           id="project_name"
                                           name="project_name"
                                           placeholder="Enter project name"
                                           value="{{ old('project_name', $project->project_name) }}"
                                           required>
                                    @error('project_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-4">
                                    <label for="project_description" class="form-label fw-bold">Project Description</label>
                                    <textarea class="form-control @error('project_description') is-invalid @enderror"
                                              id="project_description"
                                              name="project_description"
                                              rows="10"
                                              placeholder="Enter project description"
                                              required>{{ old('project_description', $project->project_description) }}</textarea>
                                    @error('project_description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
 
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="{{ route('project.show', $project->id) }}" class="btn btn-outline-secondary px-4">Cancel</a>
                                    <button type="submit" class="btn btn-primary px-4">Save Changes</button>
                                </div>
                            </form>
                        

@endsection
