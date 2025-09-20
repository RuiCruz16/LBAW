{{-- views/pages/view_your_projects.blade.php --}}
@extends('layouts.app')

@section('header')
    @include('partials.vertical_side_bar')
@endsection

@section('content')
    <div class="container my-4">
        <div class="row align-items-center justify-content-between">
            <div class="col">
                <h2 class="mb-0">
                    @switch($filter)
                        @case('all')
                            All Projects
                            @break
                        @case('myProjects')
                            My Projects
                            @break
                        @case('sharedProjects')
                            Shared Projects
                            @break
                        @case('archivedProjects')
                            Archived Projects
                            @break
                        @default
                            All Projects
                    @endswitch
                </h2>
            </div>
            <div class="col-md-auto d-flex">
                @if(!Auth::user()->isAdmin())
                    <a href="#" class="btn btn-success me-2" data-bs-toggle="modal" data-bs-target="#addProjectModal">
                        <i class="bi bi-plus-circle"></i> Add Project
                    </a>
                @endif
                <div class="dropdown">
                    <button class="btn btn-primary dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-filter"></i> Filter Projects
                    </button>
                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                        <li><a class="dropdown-item" href="{{ route('view_your_projects', ['filter' => 'all']) }}">All Projects</a></li>
                        <li><a class="dropdown-item" href="{{ route('view_your_projects', ['filter' => 'myProjects']) }}">My Projects</a></li>
                        <li><a class="dropdown-item" href="{{ route('view_your_projects', ['filter' => 'sharedProjects']) }}">Shared Projects</a></li>
                        <li><a class="dropdown-item" href="{{ route('view_your_projects', ['filter' => 'archivedProjects']) }}">Archived Projects</a></li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="row row-cols-1 row-cols-md-3 g-4 mt-3" id="projects-container">
            @include('partials.project_cards', ['projects' => $projects])
        </div>

        @if($hasMore)
            <div class="text-center mt-4">
                <button id="load-more" class="btn btn-outline-primary" data-page="2" data-filter="{{ $filter }}">
                    Show More
                </button>
            </div>
        @endif
    </div>

    @include('partials.add_project')
@endsection

@push('scripts')
    <script>
        window.projectsUrl = "{{ route('view_your_projects') }}";
    </script>
    <script src="{{ asset('js/pagination-view-projects.js') }}"></script>
@endpush