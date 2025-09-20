@extends('layouts.app')

@section('header')
    @include('partials.vertical_side_bar')
@endsection

@section('content')
<div class="container my-5">
    <div class="row">
        <div class="col-12 text-center mb-4">
            <h2 class="display-4">Assigned Task Notifications</h2>
            <p class="text-muted">Keep track of your task assignments</p>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-12 col-md-10 col-lg-8">
            @if($tasknotifications->isEmpty())
                <p class="text-muted">You have no assigned task notifications yet.</p>
            @else
                @include('partials.assigned_task_list')
            @endif
        </div>
    </div>
</div>

@endsection
