document.addEventListener('DOMContentLoaded', function() {
    const sendEmailBtn = document.getElementById('send-email-btn');
    const sendEmailForm = document.getElementById('send-email-form');
    const userSearchInput = document.getElementById('user-search');
    const receiverIdInput = document.getElementById('receiver_id');
    const invitationMessageTextarea = document.getElementById('invitation_message');
    const hiddenEmailInput = document.getElementById('hidden-email');
    const hiddenMessageInput = document.getElementById('hidden-message');
    const flashMessageContainer = document.getElementById('flash-message-container');

    function showFlashMessage(message, type = 'success') {
        const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
        const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';

        const alertHtml = `
            <div class="alert ${alertClass} alert-dismissible fade show d-flex align-items-center" role="alert">
                <i class="fas ${icon} me-2"></i>
                <div>${message}</div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;

        flashMessageContainer.innerHTML = alertHtml;

        setTimeout(() => {
            const alert = flashMessageContainer.querySelector('.alert');
            if (alert) {
                alert.remove();
            }
        }, 3000);
    }

    if (sendEmailBtn && sendEmailForm) {
        sendEmailBtn.addEventListener('click', async function(event) {
            event.preventDefault();

            if (!receiverIdInput.value) {
                showFlashMessage('Please select a user before sending the email.', 'error');
                return;
            }

            const userInputValue = userSearchInput.value;
            const emailMatch = userInputValue.match(/\(([^)]+)\)/);

            if (!emailMatch || !emailMatch[1]) {
                showFlashMessage('Could not extract the email. Please select the user again.', 'error');
                return;
            }

            const email = emailMatch[1].trim();
            const message = invitationMessageTextarea.value;

            hiddenEmailInput.value = email;
            hiddenMessageInput.value = message;

            try {
                sendEmailBtn.disabled = true;
                sendEmailBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Sending...';

                const formData = new FormData(sendEmailForm);
                const response = await fetch(sendEmailForm.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                const result = await response.json();

                if (response.ok) {
                    showFlashMessage(result.message || 'Invitation sent successfully!');
                    userSearchInput.value = '';
                    receiverIdInput.value = '';
                    invitationMessageTextarea.value = '';
                } else {
                    showFlashMessage(result.message || 'Failed to send invitation.', 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                showFlashMessage('An error occurred while sending the invitation.', 'error');
            } finally {
                sendEmailBtn.disabled = false;
                sendEmailBtn.innerHTML = 'Send Email';
            }
        });
    } else {
        console.error("Send Email button or form not found.");
    }
});