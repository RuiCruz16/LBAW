@extends('layouts.auth')

@section('content')
    <div class="container d-flex justify-content-center align-items-center" style="height: 100vh;">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white text-center">
                    <h4>Reset Password</h4>
                </div>

                <div class="card-body">
                    @if (session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                    <form method="POST" action="{{ route('password.update') }}">
                        @csrf
                        <input type="hidden" name="token" value="{{ $token }}">

                        <div class="mb-4">
                            <label for="email" class="form-label d-flex align-items-center">
                                Email Address
                                <span class="help-icon ms-2" style="position: relative; cursor: default;">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#007bff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <circle cx="12" cy="12" r="10"></circle>
                                        <line x1="12" y1="16" x2="12" y2="12"></line>
                                        <line x1="12" y1="8" x2="12" y2="8"></line>
                                    </svg>
                                    <span class="help-tooltip">
                                        Use the email address you registered with Planora.
                                    </span>
                                </span>
                            </label>
                            <input id="email" type="email" name="email" class="form-control" required autofocus placeholder="example@email.com">
                        </div>

                        <div class="mb-4">
                            <label for="password" class="form-label d-flex align-items-center">
                                New Password
                                <span class="help-icon ms-2" style="position: relative; cursor: default;">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#007bff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <circle cx="12" cy="12" r="10"></circle>
                                        <line x1="12" y1="16" x2="12" y2="12"></line>
                                        <line x1="12" y1="8" x2="12" y2="8"></line>
                                    </svg>
                                    <span class="help-tooltip">
                                        Your password must be at least 8 characters long.
                                    </span>
                                </span>
                            </label>
                            <input id="password" type="password" name="password" class="form-control" required>
                        </div>

                        <div class="mb-4">
                            <label for="password-confirm" class="form-label">Confirm New Password</label>
                            <input id="password-confirm" type="password" name="password_confirmation" class="form-control" required>
                        </div>

                        <div class="d-flex justify-content-center">
                            <button type="submit" class="btn btn-primary">Reset Password</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection