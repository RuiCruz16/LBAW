@extends('layouts.auth')

@section('header')
    @include('partials.auth_header')
@endsection

@section('content')
    @include('partials.flash_messages')
    <div class="container d-flex justify-content-center align-items-center" style="height: 100vh;">
        <div class="row justify-content-center w-100">
            <div class="col-md-6">
                <form method="POST" action="{{ route('login') }}">
                    @csrf
                    <h2 class="text-center mb-4">Log in to your account</h2>

                    <div class="mb-3">
                        <label for="email" class="form-label">Enter Your Email</label>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                               class="form-control @error('email') is-invalid @enderror" placeholder="example@email.com">
                        @error('email')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Enter Your Password</label>
                        <input id="password" type="password" name="password" required class="form-control @error('password') is-invalid @enderror">
                        @error('password')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                        @enderror
                    </div>

                    <div class="form-check mb-4">
                        <input type="checkbox" name="remember" class="form-check-input" {{ old('remember') ? 'checked' : '' }}>
                        <label class="form-check-label" for="remember">Remember Me</label>
                    </div>

                    <div class="d-flex justify-content-center mb-4">
                        <button type="submit" class="btn btn-primary" style="width: auto; padding-left: 20px; padding-right: 20px;">Login</button>
                    </div>

                    <div class="text-center mt-3">
                        <p class="mb-2">
                            Forgot your password?
                            <a href="{{ route('password.recover') }}" class="text-primary">Send a recovery email</a>
                        </p>
                        <p class="mb-0">Don't have an account? <a href="{{ route('register') }}" class="text-primary">Register</a></p>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection