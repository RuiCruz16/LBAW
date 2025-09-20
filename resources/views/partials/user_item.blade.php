@php
    use Illuminate\Support\Facades\Auth;

    $isCoordinator = $project->coordinators->contains(function ($coordinator) use ($member) {
        return $coordinator->pivot->user_id == $member->id;
    });

    $isNotCreator = $member->id != $project->creator_id;
    $isMember = !$isCoordinator && $isNotCreator;

    $isAdmin = Auth::user()->isAdmin();

    $canPromote = $isMember && ($isAdmin || Auth::id() == $project->creator_id);
    $canDemote = $isCoordinator && ($isAdmin || Auth::id() == $project->creator_id);
    $canRemove = ($isMember || $isCoordinator) && $isNotCreator && ($isAdmin || Auth::id() == $project->creator_id);

    $role = $isNotCreator ? ($isCoordinator ? 'Coordinator' : 'Member') : 'Creator';
@endphp

<li class="list-group-item d-flex align-items-center p-2">
    <div class="user-info d-flex align-items-center">
        <a href="{{ route('profile.show', $member->id) }}"
           class="user-link d-flex align-items-center text-decoration-none">
            <div class="user-image me-2">
                @if($member->image)
                    <img src="{{ asset('storage/' . $member->image->image_path) }}" alt="Profile Picture"
                         class="rounded-circle" width="30" height="30">
                @else
                    <img src="{{ asset('storage/images/default-profile-picture.jpg') }}" alt="Profile Picture"
                         class="rounded-circle" width="30" height="30">
                @endif
            </div>
            <div class="user-name-container">
                <p class="user-name mb-0 text-dark fw-bold fs-6"
                   style="max-width: 160px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $member->username }}</p>
                <span class="badge bg-secondary">{{ $role }}</span> <!-- Display the role under the username -->
            </div>
        </a>
    </div>

    @if($canPromote || $canDemote || $canRemove)
        <div class="dropdown ms-auto position-relative">
            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="dropdownMenuButton"
                    data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bi bi-three-dots"></i>
            </button>
            <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">

                @if($canPromote)
                    <li>
                        <form action="/projects/{{ $project->id }}/promote/{{ $member->id }}" method="POST"
                              class="dropdown-item">
                            @csrf
                            @method('PUT')
                            <button type="submit" class="btn btn-link text-dark">
                                <i class="bi bi-arrow-up-circle"></i>
                                Promote to Coordinator
                            </button>
                        </form>
                    </li>
                @endif

                @if($canDemote)
                    <li>
                        <form action="/projects/{{ $project->id }}/demote/{{ $member->id }}" method="POST"
                              class="dropdown-item">
                            @csrf
                            @method('PUT')
                            <button type="submit" class="btn btn-link text-dark">
                                <i class="bi bi-arrow-down-circle"></i>
                                Demote to Member
                            </button>
                        </form>
                    </li>
                @endif

                @if($canRemove)
                    <li>
                        <form action="/projects/{{ $project->id }}/remove-member/{{ $member->id }}" method="POST"
                              class="dropdown-item">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">
                                <i class="bi bi-trash"></i>
                                Remove User from Project
                            </button>
                        </form>
                    </li>
                @endif

            </ul>
        </div>
    @endif
</li>
