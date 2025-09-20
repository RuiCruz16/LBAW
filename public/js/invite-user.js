document.addEventListener('DOMContentLoaded', function () {
    const userSearchInput = document.getElementById('user-search');
    const userSearchResults = document.getElementById('user-search-results');
    const projectIdInput = document.querySelector('input[name="project_id"]');
    const receiverIdInput = document.getElementById('receiver_id');

    userSearchInput.addEventListener('input', function () {
        const query = userSearchInput.value.trim();
        const projectId = projectIdInput.value;

        if (query.length < 2) {
            userSearchResults.style.display = 'none';
            return;
        }

        fetch(`/users/search?user=${encodeURIComponent(query)}&project_id=${encodeURIComponent(projectId)}`, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
            },
        })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data.users.length === 0) {
                    userSearchResults.innerHTML = '<a class="dropdown-item disabled">Users not found.</a>';
                    userSearchResults.style.display = 'block';
                    return;
                }

                const results = data.users.map(user => `
                    <a href="#" class="dropdown-item user-result" data-id="${user.id}" data-email="${user.email}">
                        <div class="d-flex align-items-center">
                            <div class="me-2">
                                <i class="fas fa-user-circle fa-lg"></i>
                            </div>
                            <div>
                                <div class="fw-bold">${user.username}</div>
                                <div class="text-muted small">${user.email}</div>
                            </div>
                        </div>
                    </a>
                `).join('');

                userSearchResults.innerHTML = results;
                userSearchResults.style.display = 'block';
            })
            .catch(error => {
                console.error('Error finding users:', error);
                userSearchResults.innerHTML = '<a class="dropdown-item disabled">Error loading users.</a>';
                userSearchResults.style.display = 'block';
            });
    });

    userSearchResults.addEventListener('click', function (e) {
        const target = e.target.closest('.user-result');
        if (target) {
            e.preventDefault();
            const userId = target.getAttribute('data-id');
            const userName = target.querySelector('.fw-bold').textContent;
            const userEmail = target.getAttribute('data-email');

            userSearchInput.value = `${userName} (${userEmail})`;
            receiverIdInput.value = userId;
            userSearchResults.style.display = 'none';
        }
    });

    document.addEventListener('click', function (e) {
        if (!userSearchInput.contains(e.target) && !userSearchResults.contains(e.target)) {
            userSearchResults.style.display = 'none';
        }
    });
});