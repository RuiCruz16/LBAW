document.addEventListener('DOMContentLoaded', () => {
    const container = document.querySelector('.col-12');

    container.addEventListener('click', function (e) {
        if (e.target.tagName === 'A' && e.target.closest('#pagination-assigned-task')) {
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
                document.querySelector('#assigned-task-list').innerHTML = html;
            })
            .catch(error => console.error('Error fetching assigned task notifications:', error));
    }
});

document.addEventListener('DOMContentLoaded', () => {
    const container = document.querySelector('.col-12');

    // Handle delete notification
    container.addEventListener('submit', function (e) {
        if (e.target.classList.contains('delete-notification-form')) {
            e.preventDefault();
            const form = e.target;
            const url = form.action;

            fetch(url, {
                method: 'DELETE',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                },
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Replace the assigned task list with the updated content
                        document.querySelector('#assigned-task-list').innerHTML = data.html;
                    } else {
                        alert(data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to delete the notification. Please try again.');
                });
        }
    });

    // Handle pagination links
    container.addEventListener('click', function (e) {
        if (e.target.tagName === 'A' && e.target.closest('#pagination-assigned-task')) {
            e.preventDefault();
            const url = e.target.getAttribute('href');
            fetchPaginatedData(url);
        }
    });

    function fetchPaginatedData(url) {
        fetch(url, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
        })
            .then(response => response.text())
            .then(html => {
                document.querySelector('#assigned-task-list').innerHTML = html;
            })
            .catch(error => console.error('Error fetching paginated data:', error));
    }
});


