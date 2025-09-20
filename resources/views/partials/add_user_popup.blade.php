<div id="add-user-popup" class="popup d-none position-fixed top-0 start-0 w-100 h-100">
    <div class="popup-overlay position-absolute w-100 h-100 bg-dark opacity-50"></div>

    <div class="popup-content position-absolute top-50 start-50 translate-middle bg-white shadow-lg rounded" style="width: 400px;">
        <div class="popup-header px-4 py-3 border-bottom d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Invite User to Project</h5>
            <button id="close-add-user-popup" class="btn btn-close"></button>
        </div>

        <div class="popup-body p-4">
            <form id="add-user-search-form" class="mb-3">
                <input type="search" id="search-user-input" class="form-control form-control-sm" placeholder="Search users..." autofocus>
            </form>

            <div id="user-list-container" class="users-list">
                <p class="text-muted fst-italic">Start typing to search for users...</p>
            </div>
        </div>
    </div>
</div>
