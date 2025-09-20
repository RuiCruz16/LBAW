@extends('layouts.app')

@section('header')
    @include('partials.vertical_side_bar')
@endsection

@section('content')
    <div class="container">
        <div class="edit-profile-container position-relative">
            <h1 class="text-center mb-4">Edit Profile</h1>

            @if($user->id == Auth::user()->id || Auth::user()->isAdmin())
                <div class="text-end mb-3">
                    <form action="{{ route('profile.delete', $user->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete your account? This action cannot be undone.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm">Delete Account</button>
                    </form>
                </div>
            @endif

            <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
                @csrf
                <input type="hidden" id="id" name="id" value="{{ $user->id }}">

                <div class="row g-4">
                    <!-- Profile Picture Section -->
                    <div class="col-12 col-md-4 text-center">
                        <label for="profile_picture" class="form-label">Upload New Profile Picture:</label>
                        <div class="profile-picture-section mb-3">
                            <div class="profile-picture-preview">
                                @if($user->image)
                                    <img id="preview" src="{{ asset('storage/' . $user->image->image_path) }}"
                                         alt="Profile Picture" class="img-fluid rounded-circle" style="width: 150px; height: 150px; object-fit: cover;">
                                @else
                                    <img id="preview" src="{{ asset('storage/images/default-profile-picture.jpg') }}"
                                         alt="Profile Picture" class="img-fluid rounded-circle" style="width: 150px; height: 150px; object-fit: cover;">
                                @endif
                            </div>
                            <input type="file" id="profile_picture" name="profile_picture"
                                   class="form-control @error('profile_picture') is-invalid @enderror" onchange="previewImage(event)">
                            @error('profile_picture')
                            <span class="text-danger mt-2">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <!-- Profile Details Section -->
                    <div class="col-12 col-md-8">
                        <div class="form-group mb-3">
                            <label for="username" class="form-label">Username:</label>
                            <input type="text" id="username" name="username"
                                   class="form-control @error('username') is-invalid @enderror"
                                   value="{{ old('username', $user->username) }}" placeholder="Choose a unique username">
                            @error('username')
                            <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="email" class="form-label">Email:</label>
                            <input type="email" id="email" name="email"
                                   class="form-control @error('email') is-invalid @enderror"
                                   value="{{ old('email', $user->email) }}" placeholder="Enter your email address">
                            @error('email')
                            <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="biography" class="form-label">Biography:</label>
                            <textarea id="biography" name="biography"
                                      class="form-control @error('biography') is-invalid @enderror"
                                      placeholder="Tell us about yourself">{{ old('biography', $user->biography) }}</textarea>
                            @error('biography')
                            <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="password" class="form-label">New Password (optional):</label>
                            <input type="password" id="password" name="password"
                                   class="form-control @error('password') is-invalid @enderror"
                                   placeholder="Enter a new password if you want to change it">
                            @error('password')
                            <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="password_confirmation" class="form-label">Confirm New Password:</label>
                            <input type="password" id="password_confirmation" name="password_confirmation"
                                   class="form-control" placeholder="Confirm your new password">
                        </div>
                    </div>
                </div>

                <!-- Save and Cancel Buttons -->
                <div class="row mt-4">
                    <div class="col-12 d-flex justify-content-center gap-3">
                        <button type="submit" class="btn btn-success px-4 py-2">Save Changes</button>
                        <a href="{{ route('profile.show', ['id' => $user->id]) }}" class="btn btn-secondary px-4 py-2">Cancel</a>
                    </div>
                </div>

            </form>
        </div>
    </div>
@endsection
