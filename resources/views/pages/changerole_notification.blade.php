@extends('layouts.app')

@section('header')
    @include('partials.vertical_side_bar')
@endsection

@section('content')
    <div class="container my-5">
        <div class="row">
            <div class="col-12 text-center mb-4">
                <h2 class="display-4">User Notifications</h2>
                <p class="text-muted">Stay updated with changes in project members</p>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-12 col-md-10 col-lg-8">
                <div class="list-group">
                    @if($notifications->isEmpty())
                        <p class="text-muted fst-italic">You do not have any notifications at the moment.</p>
                    @else
                        <div id="invitations-received-list">
                            @foreach($notifications as $notification)
                                <div class="list-group-item list-group-item-action px-4 py-3 border-0 mb-3 shadow-sm rounded-3">
                                    <div class="d-flex align-items-center">
                                        <img src="{{ $notification->sender->image }}" alt="Sender Image" class="rounded-circle me-3" width="50" height="50">
                                        <div>
                                            <strong>{{ $notification->change_role_message }}</strong>
                                            <small class="text-muted d-block">{{ $notification->sent_at->diffForHumans() }}</small>
                                            <p class="mb-1">Project: {{ $notification->project->project_name }}</p>
                                            <p class="mb-1">Sent by: {{ $notification->sender->username }}</p>
                                        </div>
                                    </div>
                                    <div class="d-flex gap-2 mt-2">
                                        <form method="POST" action="{{ route('change-role.delete', $notification->id) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger">
                                                <i class="fas fa-trash me-1"></i> Delete
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>

        @if($notifications->count() > 0)
            <div class="d-flex justify-content-center mt-4" id="pagination-invitations-received">
                {{ $notifications->links('pagination::bootstrap-4') }}
            </div>
        @endif
    </div>

    <script src="{{ asset('js/pagination-notifications.js') }}"></script>
@endsection