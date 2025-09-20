@extends('layouts.auth')

@section('content')
    <div class="d-flex justify-content-center align-items-center vh-100 recover-password-body">
        <div class="col-md-6 col-lg-5 custom-box-width">
            <div class="card shadow-lg border-0 rounded-3">
                <div class="card-header bg-primary text-white text-center py-4">
                    <h3 class="mb-0 fw-bold">Recover Password</h3>
                </div>

                <div class="card-body p-4">
                    @if (session('success'))
                        <div class="alert alert-success text-center mb-4" role="alert">
                            {{ session('success') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('password.recover.send') }}" class="needs-validation" novalidate>
                        @csrf
                        <div class="form-group mb-4">
                            <label for="recover-password-email" class="form-label fw-bold">Enter Your Email</label>
                            <div class="input-group recover-password-input-group">
                                <span class="input-group-text recover-password-input-group-text">
                                    <i class="bi bi-envelope"></i>
                                </span>
                                <input id="recover-password-email" type="email" name="email" value="{{ old('email') }}" required autofocus
                                       class="form-control recover-password-form-control @error('email') is-invalid @enderror"
                                       placeholder="example@email.com">
                                @error('email')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="d-grid gap-2 mb-3">
                            <button type="submit" class="btn btn-primary btn-lg fw-bold">Send Recovery Email</button>
                        </div>

                        <div class="text-center">
                            <a href="{{ route('login') }}" class="btn btn-link text-decoration-none fw-bold">
                                <i class="bi bi-arrow-left"></i> Back to Login
                            </a>
                        </div>
                    </form>
                </div>

                <div class="card-footer bg-light text-center py-3">
                    <small class="text-muted">Planora &copy; {{ date('Y') }}. All rights reserved.</small>
                </div>
            </div>
        </div>
    </div>
@endsection