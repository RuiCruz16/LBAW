@extends('layouts.app')

@section('header')
    @include('partials.vertical_side_bar')
@endsection

@section('content')
    <div class="container my-5">
        <div class="row">
            <div class="col-12 text-center mb-4">
                <h2 class="display-4">Personal Notifications</h2>
                <p class="text-muted">Keep track of your latest updates</p>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-12 col-md-10 col-lg-8">
                <div class="list-group">
                    <a href="{{ route('project.notifications') }}" class="list-group-item list-group-item-action px-4 py-3 border-0 mb-3 shadow-sm rounded-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-bell-fill text-primary me-3" style="font-size: 1.7rem;"></i>
                                <div>
                                    <strong>Project Invitations</strong>
                                    <small class="text-muted d-block">See all project invitations</small>
                                </div>
                            </div>
                            <span class="badge bg-danger" id="invitation-count" style="display: none;">0</span>
                        </div>
                    </a>

                    <a href="{{route('change-role.index')}}" class="list-group-item list-group-item-action px-4 py-3 border-0 mb-3 shadow-sm rounded-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-envelope-fill text-primary me-3" style="font-size: 1.7rem;"></i>
                                <div>
                                    <strong>User Notifications</strong>
                                    <small class="text-muted d-block">View all role changes and project departures</small>
                                </div>
                            </div>
                            <span class="badge bg-danger" id="role-change-count" style="display: none;">0</span>
                        </div>
                    </a>
                    
                    <a href="{{ route('assigned-task.notifications') }}" class="list-group-item list-group-item-action px-4 py-3 border-0 mb-3 shadow-sm rounded-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-clipboard-check text-success me-3" style="font-size: 1.7rem;"></i>
                                <div>
                                    <strong>Assigned Task Notifications</strong>
                                    <small class="text-muted d-block">See all task assignment notifications</small>
                                </div>
                            </div>
                            <span class="badge bg-danger" id="assigned-tasks-count" style="display: none;">0</span>
                        </div>
                    </a>

                    <a href="{{ route('task-completed.notifications') }}" class="list-group-item list-group-item-action px-4 py-3 border-0 mb-3 shadow-sm rounded-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-check-circle-fill text-success me-3" style="font-size: 1.7rem;"></i>
                                <div>
                                    <strong>Task Completed Notifications</strong>
                                    <small class="text-muted d-block">See all task completed notifications</small>
                                </div>
                            </div>
                            <span class="badge bg-danger" id="completed-tasks-count" style="display: none;">0</span>
                        </div>
                    </a>
                    <div class="col-12 text-center mb-4">
                        <button id="mark-all-read" class="btn btn-primary">Mark all as read</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

<!-- Mark all as read modal -->
    <div class="modal fade" id="confirmMarkAllReadModal" tabindex="-1" aria-labelledby="confirmMarkAllReadModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 id="confirmMarkAllReadModalLabel" class="modal-title">Confirm Action</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to mark all notifications as read?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No</button>
                    <button type="button" class="btn btn-primary" id="confirm-yes">Yes</button>
                </div>
            </div>
        </div>
    </div>

    @section('scripts')
    <script src="{{ asset('js/notifications.js') }}"></script>
@endsection
@endsection