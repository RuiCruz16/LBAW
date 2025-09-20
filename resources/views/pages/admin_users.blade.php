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
                <form method="GET" action="{{ route('admin.users.search') }}">
                    <div class="input-group">
                        <input type="search" name="user" id="user" value="{{ request('user') }}" placeholder="Search users..."
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
                <a class="nav-link active" id="users-tab" href="{{ route('admin.users') }}">Users</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="projects-tab" href="{{ route('admin.projects') }}">Projects</a>
            </li>
        </ul>

        <!-- Users Table -->
        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead>
                <tr>
                    <th>Username</th>
                    <th>Status</th>
                    <th>Is Admin</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                @forelse($users as $account)
                    <tr>
                        <td>
                            <a href="{{ route('profile.show', $account->id) }}" class="text-decoration-none">{{ $account->username }}</a>
                        </td>
                        <td>{{ $account->user_status }}</td>
                        <td>{{ $account->isAdmin() ? 'Yes' : 'No' }}</td>
                        <td>
                            @if(!$account->isAdmin())
                                <div class="d-flex flex-wrap gap-2 justify-content-center">
                                    @if($account->user_status === 'Blocked')
                                        <form action="{{ route('users.unblock', $account->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to unblock this user?');">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-sm btn-success flex-grow-1">
                                                <i class="bi bi-person-check-fill"></i> Unblock
                                            </button>
                                        </form>
                                    @else
                                        <form action="{{ route('users.block', $account->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to block this user?');">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-sm btn-danger flex-grow-1">
                                                <i class="bi bi-person-x-fill"></i> Block
                                            </button>
                                        </form>
                                    @endif

                                    <form action="{{ route('profile.delete', $account->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this account? This action cannot be undone.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger flex-grow-1">
                                            <i class="bi bi-trash-fill"></i> Delete Account
                                        </button>
                                    </form>
                                </div>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center">No users found.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <!-- Users Pagination -->
        <div class="d-flex justify-content-center mt-4">
            <nav aria-label="Users Pagination">
                <ul class="pagination pagination-sm">
                    @if ($users->onFirstPage())
                        <li class="page-item disabled">
                            <span class="page-link">Previous</span>
                        </li>
                    @else
                        <li class="page-item">
                            <a class="page-link" href="{{ $users->previousPageUrl() }}" aria-label="Previous">Previous</a>
                        </li>
                    @endif

                    @foreach ($users->getUrlRange(1, $users->lastPage()) as $page => $url)
                        <li class="page-item {{ $page == $users->currentPage() ? 'active' : '' }}">
                            <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                        </li>
                    @endforeach

                    @if ($users->hasMorePages())
                        <li class="page-item">
                            <a class="page-link" href="{{ $users->nextPageUrl() }}" aria-label="Next">Next</a>
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
@endsection
