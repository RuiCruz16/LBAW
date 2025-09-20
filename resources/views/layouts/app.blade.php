<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" href="{{ asset('planora.ico') }}">

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', config('app.name', 'Laravel'))</title>

    <!-- CSS Libraries -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

    <!-- Custom CSS -->
    <link href="{{ url('css/app.css') }}" rel="stylesheet">
    <link href="{{ url('css/bootstrap.min.css') }}" rel="stylesheet">

    <!-- JavaScript Libraries -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js" defer></script>

    <!-- Custom JavaScript -->
    <script type="text/javascript" src="{{ url('js/app.js') }}" defer></script>
    <script type="text/javascript" src="{{ url('js/search.js') }}" defer></script>
    <script type="text/javascript" src="{{ url('js/mail_invite.js') }}" defer></script>
    <script type="text/javascript" src="{{ url('js/bootstrap.bundle.min.js') }}" defer></script>
    <script type="text/javascript" src="{{ url('js/invite-user.js') }}" defer></script>
    <script type="text/javascript" src="{{ url('js/notifications.js') }}" defer></script>
    <script type="text/javascript" src="{{ url('js/pagination-assigned-task-notifications.js') }}" defer></script>
    <script type="text/javascript" src="{{ url('js/pagination-notifications.js') }}" defer></script>
    <script type="text/javascript" src="{{ url('js/pagination-task-notifications.js') }}" defer></script>
    <script type="text/javascript" src="{{ url('js/pagination-view-projects.js') }}" defer></script>
    <script type="text/javascript" src="{{ url('js/profile-pagination.js') }}" defer></script>

    @stack('scripts')
</head>
<body class="bg-light">
<main class="d-flex min-vh-100">
    <div class="sidebar-wrapper" style="flex-shrink: 0;">
        @yield('header')
    </div>

    <section class="flex-grow-1 p-4 content">
        @yield('content')
    </section>
</main>
</body>
</html>
