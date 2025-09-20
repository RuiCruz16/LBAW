document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('query');
    const searchForm = document.getElementById('search-form');
    let debounceTimeout;

    searchForm.addEventListener('submit', function (event) {
        const query = searchInput.value.trim();

        if (!query) {
            event.preventDefault();
            window.location.href = '/search';
        }
    });

    searchInput.addEventListener('input', function () {
        clearTimeout(debounceTimeout);

        const query = searchInput.value.trim();

        debounceTimeout = setTimeout(function () {
            window.location.href = `/search?query=${encodeURIComponent(query)}`;
        }, 800);
    });
});