document.addEventListener('DOMContentLoaded', () => {
    const updateNotificationCount = async () => {
        try {
            const response = await fetch('/notifications/count', {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (!response.ok) {
                throw new Error(`Server response error: ${response.status}`);
            }

            const data = await response.json();

            const badge = document.getElementById('notification-badge');
            if (badge) {
                if (data.count > 0) {
                    badge.textContent = data.count;
                    badge.style.display = 'inline-block';
                } else {
                    badge.textContent = '';
                    badge.style.display = 'none';
                }
            }
        } catch (error) {
            console.error('Error fetching notification count:', error);
        }
    };

    updateNotificationCount();

    setInterval(updateNotificationCount, 1000);
});

// Count invitations
document.addEventListener('DOMContentLoaded', () => {
    const updateInvitationsCount = async () => {
        try {
            const response = await fetch('/notifications/countInvitations', {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });

            if (!response.ok) throw new Error(`Server error: ${response.status}`);
            
            const data = await response.json();
            const countElement = document.getElementById('invitation-count');
            
            if (countElement) {
                if (data.countInvitations > 0) {
                  countElement.textContent = data.countInvitations;
                  countElement.style.display = 'inline-block';
                } else {
                  countElement.textContent = '';
                  countElement.style.display = 'none';
                }
              }
        } catch (error) {
            console.error('Error fetching invitations count:', error);
        }
    };

    updateInvitationsCount();
    setInterval(updateInvitationsCount, 1000); 
});

// Count role changes
document.addEventListener('DOMContentLoaded', () => {
    const updateRoleChangeCount = async () => {
        try {
            const response = await fetch('/notifications/countChangeRole', {
                headers: {'X-Requested-With': 'XMLHttpRequest'}
            });

            if (!response.ok) throw new Error(`Server error: ${response.status}`);
            
            const data = await response.json();
            const countElement = document.getElementById('role-change-count');
            
            if (countElement) {
                if (data.countChangeRole > 0) {
                  countElement.textContent = data.countChangeRole;
                  countElement.style.display = 'inline-block';
                } else {
                  countElement.textContent = '';
                  countElement.style.display = 'none';
                }
              }
        } catch (error) {
            console.error('Error fetching role change count:', error);
        }
    };

    updateRoleChangeCount();
    setInterval(updateRoleChangeCount, 1000);
});

// Count assigned tasks
document.addEventListener('DOMContentLoaded', () => {
    const updateAssignedTasksCount = async () => {
        try {
            const response = await fetch('/notifications/countAssignedTasks', {
                headers: {'X-Requested-With': 'XMLHttpRequest'}
            });

            if (!response.ok) throw new Error(`Server error: ${response.status}`);
            
            const data = await response.json();
            const countElement = document.getElementById('assigned-tasks-count');
            
            if (countElement) {
                if (data.countAssignedTasks > 0) {
                  countElement.textContent = data.countAssignedTasks;
                  countElement.style.display = 'inline-block';
                } else {
                  countElement.textContent = '';
                  countElement.style.display = 'none';
                }
              }
        } catch (error) {
            console.error('Error fetching assigned tasks count:', error);
        }
    };

    updateAssignedTasksCount();
    setInterval(updateAssignedTasksCount, 1000);
});

// Count completed tasks
document.addEventListener('DOMContentLoaded', () => {
    const updateCompletedTasksCount = async () => {
        try {
            const response = await fetch('/notifications/countTaskCompleted', {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });

            if (!response.ok) throw new Error(`Server error: ${response.status}`);

            const data = await response.json();
            const countElement = document.getElementById('completed-tasks-count');

            if (countElement) {
                if (data.completedTaskCount > 0) {
                    countElement.textContent = data.completedTaskCount;
                    countElement.style.display = 'inline-block';
                } else {
                    countElement.textContent = '';
                    countElement.style.display = 'none';
                }
            }
        } catch (error) {
            console.error('Error fetching completed tasks count:', error);
        }
    };

    updateCompletedTasksCount();
    setInterval(updateCompletedTasksCount, 1000);
});

// Mark all notifications as read
document.addEventListener('DOMContentLoaded', () => {
    const markAllReadButton = document.getElementById('mark-all-read');
    const confirmYesButton = document.getElementById('confirm-yes');
    const confirmMarkAllReadModalElement = document.getElementById('confirmMarkAllReadModal');

    const confirmMarkAllReadModal = new bootstrap.Modal(confirmMarkAllReadModalElement);

    if (markAllReadButton) {
        markAllReadButton.addEventListener('click', function() {
            confirmMarkAllReadModal.show();
        });

        confirmYesButton.addEventListener('click', () => {
            fetch('/notifications/mark-all-read', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('All notifications marked as read.', 'success');
                    location.reload();
                } else {
                    showToast('Failed to mark notifications as read.', 'danger');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('An error occurred. Please try again.', 'danger');
            })
            .finally(() => {
                confirmMarkAllReadModal.hide();
            });
        });
    }

});