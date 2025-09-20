
@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Assigned Users for Task ID: {{ $id }}</h1>
    <ul class="list-group">
        @foreach($users as $user)
            <li class="list-group-item">{{ $user->username }}</li>
        @endforeach
    </ul>
</div>
@endsection