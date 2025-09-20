document.addEventListener('DOMContentLoaded', function() {
    const loadMoreBtn = document.getElementById('load-more');
    if (loadMoreBtn) {
        loadMoreBtn.addEventListener('click', function() {
            const page = parseInt(this.dataset.page);
            const filter = this.dataset.filter;

            fetch(`${window.projectsUrl}?filter=${filter}&page=${page}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        throw new Error(data.error);
                    }

                    document.getElementById('projects-container').insertAdjacentHTML('beforeend', data.html);

                    if (!data.hasMore) {
                        loadMoreBtn.remove();
                    } else {
                        loadMoreBtn.dataset.page = page + 1;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to load more projects. Please try again.');
                });
        });
    }
});