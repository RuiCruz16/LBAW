@extends('layouts.app')

@section('header')
    @include('partials.vertical_side_bar')
@endsection

@section('content')
    <div class="container mt-5">
        @include('partials.flash_messages')

        <!-- Search Bar -->
        <div class="row justify-content-center mb-4">
            <div class="col-12 col-md-8 col-lg-6">
                <form method="GET" action="{{ route('projects.search') }}">
                    <div class="input-group">
                        <input type="search" name="project" id="project" value="{{ request('project') }}" placeholder="Search projects..."
                               class="form-control form-control-lg">
                        <button class="btn btn-primary btn-lg" type="submit">
                            <i class="bi bi-search"></i> Search
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Navigation Bar (Tabs) -->
        <ul class="nav nav-tabs justify-content-center mb-4" id="adminTabs">
            <li class="nav-item">
                <a class="nav-link" id="users-tab" href="{{ route('admin.users') }}">Users</a>
            </li>
            <li class="nav-item">
                <a class="nav-link active" id="projects-tab" href="{{ route('admin.projects') }}">Projects</a>
            </li>
        </ul>

        <!-- Projects Tab -->
        <div class="tab-pane fade show active" id="projects" role="tabpanel" aria-labelledby="projects-tab">
            <h4 class="text-center mb-4">Projects</h4>
            <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 g-4">
                @forelse($projects as $project)
                    <div class="col">
                        <div class="card shadow-sm h-100">
                            <div class="card-body">
                                <a href="{{ route('project.show', ['id' => $project->id]) }}" class="project-link text-decoration-none">
                                    <h5 class="project-name mb-0">{{ $project->project_name }}</h5>
                                </a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="alert alert-warning text-center">No projects found.</div>
                    </div>
                @endforelse
            </div>

            <!-- Projects Pagination -->
            <div class="d-flex justify-content-center mt-4">
                <nav aria-label="Projects Pagination">
                    <ul class="pagination pagination-sm">
                        @if ($projects->onFirstPage())
                            <li class="page-item disabled">
                                <span class="page-link">Previous</span>
                            </li>
                        @else
                            <li class="page-item">
                                <a class="page-link" href="{{ $projects->previousPageUrl() }}" aria-label="Previous">Previous</a>
                            </li>
                        @endif

                        @foreach ($projects->getUrlRange(1, $projects->lastPage()) as $page => $url)
                            <li class="page-item {{ $page == $projects->currentPage() ? 'active' : '' }}">
                                <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                            </li>
                        @endforeach

                        @if ($projects->hasMorePages())
                            <li class="page-item">
                                <a class="page-link" href="{{ $projects->nextPageUrl() }}" aria-label="Next">Next</a>
                            </li>
                        @else
                            <li class="page-item disabled">
                                <span class="page-link">Next</span>
                            </li>
                        @endif
                    </ul>
                </nav>
            </div>
        </div>
    </div>
@endsection
