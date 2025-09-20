@extends('layouts.app')

@section('header')
    @include('partials.vertical_side_bar')
@endsection

@section('content')
    @include('partials.flash_messages')
    <div id="flash-message-container"></div>

    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white d-flex align-items-center">
                        <i class="fas fa-user-plus me-2"></i>
                        <h4 class="mb-0">Invite User to {{ $project->project_name }}</h4>
                    </div>
                    <div class="card-body">
                        <div class="mb-4 p-3 bg-light rounded">
                            <h5 class="fw-bold text-primary">
                                <i class="fas fa-project-diagram me-2"></i>
                                {{ $project->project_name }}
                            </h5>
                            <p class="text-muted mb-0">
                                <i class="fas fa-info-circle me-2"></i>
                                {{ $project->project_description }}
                            </p>
                        </div>

                        <form action="{{ route('projects.invite.create') }}" method="POST" class="needs-validation" novalidate>
                            @csrf
                            <input type="hidden" name="project_id" value="{{ $project->id }}">

                            <!-- User Search -->
                            <div class="mb-4">
                                <label for="receiver_id" class="form-label fw-bold">
                                    <i class="fas fa-users me-2"></i>
                                    Select User
                                    <span class="help-icon ms-2" style="position: relative; cursor: default;">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#007bff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <circle cx="12" cy="12" r="10"></circle>
                                            <line x1="12" y1="16" x2="12" y2="12"></line>
                                            <line x1="12" y1="8" x2="12" y2="8"></line>
                                        </svg>
                                    </span>
                                </label>
                                <div class="form-group position-relative">
                                    <input type="text" id="user-search" class="form-control @error('receiver_id') is-invalid @enderror" placeholder="Search user..." required>
                                    <input type="hidden" name="receiver_id" id="receiver_id">
                                    <div id="user-search-results" class="dropdown-menu"></div>
                                    @error('receiver_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Invitation Message -->
                            <div class="mb-4">
                                <label for="invitation_message" class="form-label fw-bold">
                                    <i class="fas fa-envelope me-2"></i>
                                    Message
                                    <span class="help-icon ms-2" style="position: relative; cursor: default;">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#007bff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <circle cx="12" cy="12" r="10"></circle>
                                            <line x1="12" y1="16" x2="12" y2="12"></line>
                                            <line x1="12" y1="8" x2="12" y2="8"></line>
                                        </svg>
                                    </span>
                                </label>
                                <textarea name="invitation_message"
                                          id="invitation_message"
                                          class="form-control @error('invitation_message') is-invalid @enderror"
                                          rows="4"
                                          placeholder="Write a message to send with the invite..."
                                          required>{{ old('invitation_message') }}</textarea>
                                @error('invitation_message')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Actions -->
                            <div class="d-flex flex-wrap justify-content-end gap-2">
                                <a href="{{ route('project.show', $project->id) }}"
                                   class="btn btn-secondary mb-2">
                                    <i class="fas fa-arrow-left me-2"></i>
                                    Cancel
                                </a>
                                <button type="submit" class="btn btn-primary mb-2">
                                    <i class="fas fa-paper-plane me-2"></i>
                                    Send Invite
                                </button>
                                <button type="button" class="btn btn-outline-success mb-2" id="send-email-btn">
                                    Send Email
                                </button>
                            </div>
                        </form>

                        <!-- Hidden Form for Email -->
                        <form id="send-email-form" action="{{ route('projects.invite.email') }}" method="POST" style="display: none;">
                            @csrf
                            <input type="hidden" name="project_id" value="{{ $project->id }}">
                            <input type="hidden" name="email" id="hidden-email">
                            <input type="hidden" name="invitation_message" id="hidden-message">
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/invite-user.js') }}"></script>
@endpush
