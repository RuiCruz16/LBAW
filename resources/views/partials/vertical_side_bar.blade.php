<!-- Desktop Sidebar -->
<div class="desktop-sidebar bg-dark text-white p-4 flex-column" style="width: 250px; position: fixed; height: 100vh; box-shadow: 2px 0 15px rgba(0, 0, 0, 0.2);">
    <!-- Logo Section -->
    <div class="mb-4 text-center">
        <img src="{{ asset('planora.ico') }}" alt="Planora's logo" width="70">
    </div>

    <!-- Navigation Links -->
    <div class="d-flex flex-column flex-grow-1">
        <!-- Dashboard -->
        @if(!Auth::user()->isAdmin())
            <a href="{{ url('/projects') }}" class="d-flex align-items-center text-white py-3 px-4 mb-3 rounded hover-bg-primary text-decoration-none {{ request()->is('projects') ? 'activev' : '' }}">
                <i class="bi bi-speedometer2 me-3" style="font-size: 24px;"></i>
                <span class="fs-5">Dashboard</span>
            </a>
        @endif

        <!-- Projects -->
        @if(!Auth::user()->isAdmin())
            <a href="{{ route('view_your_projects') }}" class="d-flex align-items-center text-white py-3 px-4 mb-3 rounded hover-bg-primary text-decoration-none {{ request()->routeIs('view_your_projects') ? 'activev' : '' }}">
                <i class="bi bi-folder2-open me-3" style="font-size: 24px;"></i>
                <span class="fs-5">Projects</span>
            </a>
        @endif

        <!-- Profile -->
        <a href="{{ route('profile.show', Auth::id()) }}" class="d-flex align-items-center text-white py-3 px-4 mb-3 rounded hover-bg-primary text-decoration-none {{ request()->is('profile/*') ? 'activev' : '' }}">
            <i class="bi bi-person-circle me-3" style="font-size: 24px;"></i>
            <span class="fs-5">Profile</span>
        </a>

        <!-- Notifications -->
        <a href="{{ route('all-notifications') }}" class="d-flex align-items-center text-white py-3 px-4 mb-3 rounded hover-bg-primary text-decoration-none {{ request()->is('notifications') ? 'activev' : '' }}">
            <i class="bi bi-bell-fill me-3" style="font-size: 24px;"></i>
            <span class="fs-5">Notifications</span>
            <span id="notification-badge" class="badge bg-danger ms-2" style="display: none;">0</span>
        </a>

        <!-- Admin -->
        @if (Auth::user()->isAdmin())
            <a href="{{ url('/admin') }}" class="d-flex align-items-center text-white py-3 px-4 mb-3 rounded hover-bg-primary text-decoration-none {{ request()->is('admin') ? 'activev' : '' }}">
                <i class="bi bi-gear-fill me-3" style="font-size: 24px;"></i>
                <span class="fs-5">Admin</span>
            </a>
        @endif

        <!-- About Us -->
        <a href="{{ route('about') }}" class="d-flex align-items-center text-white py-3 px-4 mb-3 rounded hover-bg-primary text-decoration-none {{ request()->is('about') ? 'activev' : '' }}">
            <i class="bi bi-info-circle me-3" style="font-size: 24px;"></i>
            <span class="fs-5">About Us</span>
        </a>

        <!-- Contacts -->
        <a href="{{ route('contact') }}" class="d-flex align-items-center text-white py-3 px-4 mb-3 rounded hover-bg-primary text-decoration-none {{ request()->is('contact') ? 'activev' : '' }}">
            <i class="bi bi-telephone-fill me-3" style="font-size: 24px;"></i>
            <span class="fs-5">Contacts</span>
        </a>
        <a href="{{ url('/logout') }}" class="d-flex align-items-center text-white py-3 px-4 rounded hover-bg-primary text-decoration-none">
            <i class="bi bi-box-arrow-right me-3" style="font-size: 24px;"></i>
            <span class="fs-5">Logout</span>
        </a>
    </div>

</div>

<script src="{{ asset('js/notifications.js') }}"></script>

<div class="offcanvas offcanvas-start bg-dark text-white" tabindex="-1" id="responsiveSidebarMobile" aria-labelledby="sidebarLabelMobile" style="width: 60px; height: 100vh; z-index: 1055; position: fixed;" data-bs-backdrop="false">
    <!-- Sidebar Links -->
    <div class="offcanvas-body d-flex flex-column align-items-center" style="overflow-y: auto;">
        <!-- Dashboard -->
        @if(!Auth::user()->isAdmin())
        <a href="{{ url('/projects') }}" class="d-flex flex-column align-items-center text-white py-3 px-2 mb-3 rounded hover-bg-primary text-decoration-none {{ request()->is('projects') ? 'activev' : '' }}">
            <i class="bi bi-speedometer2" style="font-size: 24px;"></i>
        </a>
        @endif

        <!-- Projects -->
        @if(!Auth::user()->isAdmin())
        <a href="{{ route('view_your_projects') }}" class="d-flex flex-column align-items-center text-white py-3 px-2 mb-3 rounded hover-bg-primary text-decoration-none {{ request()->routeIs('view_your_projects') ? 'activev' : '' }}">
            <i class="bi bi-folder2-open" style="font-size: 24px;"></i>
        </a>
        @endif

        <!-- Profile -->
        <a href="{{ route('profile.show', Auth::id()) }}" class="d-flex flex-column align-items-center text-white py-3 px-2 mb-3 rounded hover-bg-primary text-decoration-none {{ request()->is('profile/*') ? 'activev' : '' }}">
            <i class="bi bi-person-circle" style="font-size: 24px;"></i>
        </a>

        <!-- Notifications -->
        <a href="{{ route('all-notifications') }}" class="d-flex flex-column align-items-center text-white py-3 px-2 mb-3 rounded hover-bg-primary text-decoration-none {{ request()->is('notifications') ? 'activev' : '' }}">
            <i class="bi bi-bell-fill" style="font-size: 24px;"></i>
            <span id="notification-badge" class="badge bg-danger small mt-1">0</span>
        </a>

        <!-- Admin -->
        @if (Auth::user()->isAdmin())
            <a href="{{ url('/admin') }}" class="d-flex flex-column align-items-center text-white py-3 px-2 mb-3 rounded hover-bg-primary text-decoration-none {{ request()->is('admin') ? 'activev' : '' }}">
                <i class="bi bi-gear-fill" style="font-size: 24px;"></i>
            </a>
        @endif

        <!-- About Us -->
        <a href="{{ route('about') }}" class="d-flex flex-column align-items-center text-white py-3 px-2 mb-3 rounded hover-bg-primary text-decoration-none {{ request()->is('about') ? 'activev' : '' }}">
            <i class="bi bi-info-circle" style="font-size: 24px;"></i>
        </a>

        <!-- Contacts -->
        <a href="{{ route('contact') }}" class="d-flex flex-column align-items-center text-white py-3 px-2 mb-3 rounded hover-bg-primary text-decoration-none {{ request()->is('contact') ? 'activev' : '' }}">
            <i class="bi bi-telephone-fill" style="font-size: 24px;"></i>
        </a>

        <!-- Logout -->
        <a href="{{ url('/logout') }}" class="d-flex flex-column align-items-center text-white py-3 px-2 mt-auto rounded hover-bg-primary text-decoration-none">
            <i class="bi bi-box-arrow-right" style="font-size: 24px;"></i>
        </a>
    </div>
</div>

<!-- Mobile Toggle Button -->
<button class="btn btn-dark d-lg-none position-fixed" style="top: 10px; left: 10px; z-index: 1060;" data-bs-toggle="offcanvas" data-bs-target="#responsiveSidebarMobile" aria-controls="responsiveSidebarMobile">
    ☰
</button>
