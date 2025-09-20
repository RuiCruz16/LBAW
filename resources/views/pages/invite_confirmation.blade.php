@extends('layouts.app')

@section('content')
    <div class="container d-flex justify-content-center align-items-center" style="height: 100vh;">
        <div class="text-center">
            <div class="alert alert-success shadow-sm p-4 rounded">
                <h2 class="fw-bold">Success!</h2>
                <p>The user <strong>{{ $user_name }}</strong> has been successfully added to the project <strong>{{ $project_name }}</strong>.</p>
                <button onclick="window.close()" class="btn btn-primary mt-3">Close Tab</button>
            </div>
        </div>
    </div>
@endsection
