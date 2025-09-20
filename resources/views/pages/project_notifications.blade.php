@extends('layouts.app')

@section('header')
    @include('partials.vertical_side_bar')
@endsection

@section('content')
    <div class="container my-5">
        <!-- Título da Página -->
        <div class="row">
            <div class="col-12 text-center mb-4">
                <h2 class="display-4">Projects Notifications</h2>
                <p class="text-muted">Keep track of your latest invites</p>
            </div>
        </div>

        <!-- Convites Recebidos -->
        <div class="row mb-4">
            <div class="col-12">
                <h4>Invitations Received</h4>
                @if($invitations_received->isEmpty())
                    <p class="text-muted">You have no new invitations.</p>
                @else
                    <div class="list-group mb-3" id="invitations-received-list">
                        @foreach($invitations_received as $invitation)
                            <div class="list-group-item list-group-item-action">
                                <div class="d-flex w-100 justify-content-between">
                                    <h5 class="mb-1">
                                        <strong>{{ $invitation->sender->username }}</strong> invited you to join 
                                        <strong>{{ $invitation->project->project_name }}</strong>.
                                    </h5>
                                    <small>{{ $invitation->sent_at->diffForHumans() }}</small>
                                </div>
                                <p class="mb-1">{{ $invitation->invitation_message }}</p>
                                <div class="d-flex gap-2">
                                    <form method="POST" action="{{ route('project.invitation.accept', $invitation->id) }}">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success">
                                            <i class="fas fa-check me-1"></i>Accept
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('project.invitation.reject', $invitation->id) }}">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-danger">
                                            <i class="fas fa-times me-1"></i>Reject
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <!-- Paginação -->
                    <div class="d-flex justify-content-center mt-4" id="pagination-invitations-received">
                        {{ $invitations_received->withQueryString()->links('pagination::bootstrap-4') }}
                    </div>
                @endif
            </div>
        </div>

        <!-- Convites Enviados -->
        <div class="row mb-4">
            <div class="col-12">
                <h4>Invitations Sent</h4>
                @if($invitations_sent->isEmpty())
                    <p class="text-muted">You dosent any invitations.</p>
                @else
                    <div class="list-group mb-3" id="invitations-sent-list">
                        @foreach($invitations_sent as $invitation)
                            <div class="list-group-item list-group-item-action">
                                <div class="d-flex w-100 justify-content-between">
                                    <h5 class="mb-1">
                                        You invited <strong>{{ $invitation->receiver->username }}</strong> to join 
                                        <strong>{{ $invitation->project->project_name }}</strong>
                                        <small>{{ $invitation->sent_at->diffForHumans() }}</small>
                                    </h5>
                                    <form method="POST" action="{{ route('project.myinvitation.delete', $invitation->id) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">
                                            <i class="fas fa-trash me-1"></i>Delete
                                        </button>
                                    </form>
                                </div>
                                <p class="mb-1">{{ $invitation->invitation_message }}</p>
                                <a href="{{ route('project.show', $invitation->project_id) }}" class="btn btn-sm btn-primary">View Project</a>
                            </div>
                        @endforeach
                    </div>
                    <!-- Paginação -->
                    <div class="d-flex justify-content-center mt-4" id="pagination-invitations-sent">
                        {{ $invitations_sent->withQueryString()->links('pagination::bootstrap-4') }}
                    </div>
                @endif
            </div>
        </div>

        <!-- Respostas -->
        <div class="row mb-4">
            <div class="col-12">
                <h4>Responses</h4>
                @if($responses->isEmpty())
                    <p class="text-muted">You have no responses yet.</p>
                @else
                    <div class="list-group mb-3" id="responses-list">
                        @foreach($responses as $response)
                            <div class="list-group-item list-group-item-action">
                                <div class="d-flex w-100 justify-content-between">
                                    <h5 class="mb-1">
                                        Response from <strong>{{ $response->sender->username }}</strong>
                                        <small>{{ $response->sent_at->diffForHumans() }}</small>

                                    </h5>
                                    <form method="POST" action="{{ route('project.invitation.delete', $response->id) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">
                                            <i class="fas fa-trash me-1"></i>Delete
                                        </button>
                                    </form>
                                </div>
                                <p class="mb-1">{{ $response->invitation_message }}</p>
                            </div>
                        @endforeach
                    </div>
                    <!-- Paginação -->
                    <div class="d-flex justify-content-center mt-4" id="pagination-responses">
                        {{ $responses->withQueryString()->links('pagination::simple-bootstrap-4') }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    <script src="{{ asset('js/pagination-notifications.js') }}"></script>

@endsection