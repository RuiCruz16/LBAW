document.addEventListener('DOMContentLoaded', function() {
    const loadMoreButtons = document.querySelectorAll('.load-more');

    loadMoreButtons.forEach(button => {
        button.addEventListener('click', function() {
            const page = parseInt(this.dataset.page);
            const section = this.dataset.section;
            const container = document.getElementById(`${section}-container`);

            fetch(`${window.profileUrl}?page=${page}&section=${section}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        throw new Error(data.error);
                    }

                    container.insertAdjacentHTML('beforeend', data.html);

                    if (!data.hasMore) {
                        this.remove();
                    } else {
                        this.dataset.page = page + 1;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to load more projects. Please try again.');
                });
        });
    });
});