@extends('layouts.app')

@section('header')
    @include('partials.vertical_side_bar')
@endsection

@section('content')
    @include('partials.flash_messages')

    <div class="container-fluid">
        <div class="row">
            <!-- Profile Sidebar -->
            <div class="col-12 col-md-4 mb-4">
                <div class="card p-4">
                    <div class="text-center">
                        @if($user->image)
                            <img src="{{ asset('storage/' . $user->image->image_path) }}" alt="Profile Picture" class="img-fluid rounded-circle" style="width: 150px; height: 150px; object-fit: cover;">
                        @else
                            <img src="{{ asset('storage/images/default-profile-picture.jpg') }}" alt="Profile Picture" class="img-fluid rounded-circle" style="width: 150px; height: 150px; object-fit: cover;">
                        @endif
                    </div>

                    <h3 class="mt-3 text-center">{{ $user->username }}</h3>
                    <p class="text-center">{{ $user->biography ?? 'No biography added.' }}</p>

                    @if(Auth::id() === $user->id || Auth::user()->is_admin)
                        <div class="text-center mt-3">
                            <a href="{{ route('profile.edit', $user->id) }}" class="btn btn-outline-primary">
                                <i class="bi bi-pencil-square"></i> Edit Profile
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-12 col-md-8">
                <!-- Favorite Projects Section -->
                <section id="favorites" class="mb-5">
                    <h2>Favorite Projects</h2>
                    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4" id="favorites-container">
                        @if($favoriteProjects->isEmpty())
                            <div class="col-12">
                                <div class="alert alert-info text-center">
                                    <i class="bi bi-heart"></i> No favorite projects yet.
                                </div>
                            </div>
                        @else
                            @include('partials.project_cards', ['projects' => $favoriteProjects])
                        @endif
                    </div>
                    @if($hasMoreFavorites)
                        <div class="text-center mt-4">
                            <button class="btn btn-outline-primary load-more" data-page="2" data-section="favorites">
                                Show More Favorites
                            </button>
                        </div>
                    @endif
                </section>

                <!-- Projects Section -->
                <section id="projects">
                    <h2>Projects</h2>

                    @if(Auth::id() === $user->id && !$user->is_admin)
                        <div class="text-end mb-3">
                            <a href="#" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addProjectModal">
                                <i class="bi bi-plus-circle"></i> New Project
                            </a>
                        </div>
                    @endif

                    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4" id="projects-container">
                        @if($projects->isEmpty())
                            <div class="col-12">
                                <div class="alert alert-info text-center">
                                    <i class="bi bi-folder"></i>
                                    @if(Auth::id() === $user->id)
                                        You haven't created any projects yet. Click the "New Project" button to get started!
                                    @else
                                        This user hasn't created any projects yet.
                                    @endif
                                </div>
                            </div>
                        @else
                            @include('partials.project_cards', ['projects' => $projects])
                        @endif
                    </div>
                    @if($hasMoreProjects)
                        <div class="text-center mt-4">
                            <button class="btn btn-outline-primary load-more" data-page="2" data-section="projects">
                                Show More Projects
                            </button>
                        </div>
                    @endif
                </section>
            </div>
        </div>
    </div>

    @include('partials.add_project')
@endsection

@push('scripts')
    <script>
        window.profileUrl = "{{ route('profile.show', $user->id) }}";
    </script>
    <script src="{{ asset('js/profile-pagination.js') }}"></script>
@endpush
