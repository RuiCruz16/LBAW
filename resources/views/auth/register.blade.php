@extends('layouts.auth')

@section('header')
    @include('partials.auth_header')
@endsection

@section('content')
    <div class="container d-flex justify-content-center align-items-center" style="height: 100vh;">
        <div class="row justify-content-center w-100">
            <div class="col-md-6 col-lg-4">
                <form method="POST" action="{{ route('register') }}" class="bg-light p-4 rounded shadow-sm">
                    @csrf

                    <div class="text-center mb-4">
                        <h2 class="mb-2">Welcome to Planora</h2>
                        <h4 class="text-muted">Get Started!</h4>
                    </div>

                    <div class="mb-3">
                        <label for="username" class="form-label d-flex align-items-center">
                            Name
                            <span class="help-icon ms-2" style="position: relative; cursor: default;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#007bff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <line x1="12" y1="16" x2="12" y2="12"></line>
                                    <line x1="12" y1="8" x2="12" y2="8"></line>
                                </svg>
                                <span class="help-tooltip">
                                    Choose a unique username. You can change it later in your profile settings.
                                </span>
                            </span>
                        </label>
                        <input id="username" type="text" name="username" class="form-control @error('username') is-invalid @enderror" value="{{ old('username') }}" required autofocus placeholder="Username">
                        @error('username')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">E-Mail Address</label>
                        <input id="email" type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required placeholder="example@email.com">
                        @error('email')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label d-flex align-items-center">
                            Password
                            <span class="help-icon ms-2" style="position: relative; cursor: default;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#007bff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <line x1="12" y1="16" x2="12" y2="12"></line>
                                    <line x1="12" y1="8" x2="12" y2="8"></line>
                                </svg>
                                <span class="help-tooltip">
                                    Minimum 8 characters.
                                </span>
                            </span>
                        </label>
                        <input id="password" type="password" name="password" class="form-control @error('password') is-invalid @enderror" required>
                        @error('password')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="password-confirm" class="form-label">Confirm Password</label>
                        <input id="password-confirm" type="password" name="password_confirmation" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <button type="submit" class="btn btn-primary w-100">Register</button>
                    </div>

                    <div class="mt-3 text-center">
                        <p class="mb-0">Already have an account? <a href="{{ route('login') }}" class="text-primary">Login</a></p>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection