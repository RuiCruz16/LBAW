document.addEventListener('DOMContentLoaded', () => {
    const container = document.querySelector('.col-12.col-md-10.col-lg-8');

    container.addEventListener('click', function (e) {
        if (e.target.tagName === 'A' && e.target.closest('#pagination-task-completed')) {
            e.preventDefault();
            const url = e.target.getAttribute('href');
            fetchPaginatedData(url);
        }
    });

    function fetchPaginatedData(url) {
        fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            },
        })
            .then(response => response.text())
            .then(html => {
                document.querySelector('#task-completed-list').innerHTML = html;
            })
            .catch(error => console.error('Error fetching task completed notifications:', error));
    }
});