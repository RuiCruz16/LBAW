<ul class="list-group">
    @forelse($users as $account)
        <li class="list-group-item d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center">
                <a href="{{ route('profile.show', $account->id) }}" class="d-flex align-items-center text-decoration-none">
                    <div class="user-image me-3">
                        @if($account->image)
                            <img src="{{ asset('storage/' . $account->image->image_path) }}" alt="Profile Picture" class="rounded-circle" width="40" height="40">
                        @else
                            <img src="{{ asset('storage/images/default-profile-picture.jpg') }}" alt="Profile Picture" class="rounded-circle" width="40" height="40">
                        @endif
                    </div>
                    <p class="user-name mb-0">{{ $account->username }}</p>
                </a>
            </div>
            <form action="{{ route('projects.addMember', ['projectId' => $projectId]) }}" method="POST" class="add-user-form">
                @csrf
                <input type="hidden" name="user_id" value="{{ $account->id }}">
                <button type="submit" class="btn btn-sm btn-primary add-button">
                    Add
                </button>
            </form>
        </li>
    @empty
        <li class="list-group-item text-center text-muted">No users found.</li>
    @endforelse
</ul>
