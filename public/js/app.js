// Utility Functions
const AjaxUtils = {
  encodeForAjax: (data) => {
    if (!data) return null;
    return Object.keys(data)
        .map((key) => `${encodeURIComponent(key)}=${encodeURIComponent(data[key])}`)
        .join("&");
  },

  sendRequest: (method, url, data, handler) => {
    const request = new XMLHttpRequest();
    request.open(method, url, true);
    request.setRequestHeader("X-CSRF-TOKEN", document.querySelector('meta[name="csrf-token"]').content);
    request.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    request.addEventListener("load", handler);
    request.send(AjaxUtils.encodeForAjax(data));
  }
};

// Visibility Helper
const toggleVisibility = (element, isVisible) => {
  element.style.display = isVisible ? "block" : "none";
};

// Flash Message
const FlashMessageHandler = {
  init: () => {
    const flashMessage = document.getElementById("flash-message");
    if (flashMessage) {
      setTimeout(() => {
        flashMessage.style.transition = "opacity 0.5s";
        flashMessage.style.opacity = "0";
        setTimeout(() => flashMessage.remove(), 500);
      }, 3000);
    }
  }
};

// Image Preview
function previewImage(event) {
  const file = event.target.files[0];

  if (file) {
    const reader = new FileReader();

    reader.onload = function(e) {
      const preview = document.getElementById('preview');

      if (preview) {
        preview.src = e.target.result;
      }
    };

    reader.readAsDataURL(file);
  }
}

// Task Popup
const TaskPopup = {
  init: () => {
    const taskBoard = document.querySelector(".task-board");
    const closeBtn = document.getElementById("close-popup");
    const overlay = document.querySelector(".popup-overlay");
    const taskMenu = document.getElementById("task-menu");
    const menuButton = document.getElementById("menu-button");

    if (taskBoard) {
      taskBoard.addEventListener("click", TaskPopup.handleTaskClick);
    }
    closeBtn.addEventListener("click", (e) => {
      e.preventDefault();
      TaskPopup.close();
    });
    overlay.addEventListener("click", TaskPopup.close);
    menuButton.addEventListener("click", (e) => {
      e.stopPropagation();
      taskMenu.classList.toggle("d-none");
    });

    document.addEventListener("click", (e) => {
      if (!taskMenu.contains(e.target) && !menuButton.contains(e.target)) {
        taskMenu.classList.add("d-none");
      }
    });

    document.getElementById("edit-task").addEventListener("click", TaskPopup.openEditMode);
    document.getElementById("cancel-edit").addEventListener("click", TaskPopup.closeEditMode);

    document.getElementById("task-edit-form").addEventListener("submit", TaskPopup.handleSubmit);
  },

  handleTaskClick: (e) => {
    const target = e.target.closest(".task-link");
    if (target) {
      e.preventDefault();
      const taskId = target.dataset.taskId;
      TaskPopup.fetchDetails(taskId);
    }
  },

  fetchDetails: (taskId) => {
    fetch(`/tasks/${taskId}`, {
      headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
      },
    })
    .then((response) => {
      if (!response.ok) {
        return response.text().then(text => {
          console.error("Error response:", text);
          throw new Error(`HTTP error! status: ${response.status}`);
        });
      }
      return response.json();
    })
    .then((data) => {
      if (data.error) {
        throw new Error(data.error);
      }
      TaskPopup.populateDetails(data);
      TaskPopup.populateAssignedUsers(data.task.users, data.task); // Pass both users and task

      const popup = document.getElementById("offcanvasExample");
      popup.dataset.taskId = data.task.id;
      popup.classList.add("show");
      document.querySelector(".popup-overlay").classList.add("show");
    })
    .catch((error) => console.error("Error:", error));
  },

  populateDetails: (data) => {
    const { task, statuses } = data;

    if (!task) {
      console.error("Task data is missing");
      return;
    }

    document.getElementById("view-task-title").innerHTML = task.task_title || "<em>No Title</em>";
    document.getElementById("view-task-description").innerHTML = task.task_description || "<em>No Description Available</em>";
    document.getElementById("view-task-created-at").innerHTML = task.created_at ? new Date(task.created_at).toLocaleDateString() : "<em>Not Defined</em>";
    const deadline = task.deadline ? new Date(task.deadline) : null;
    document.getElementById("view-task-deadline").innerHTML = deadline ? deadline.toLocaleDateString() : "<em>Not Defined</em>";
    document.getElementById("view-task-priority").innerHTML = task.task_priority || "<em>Not Defined</em>";
    document.getElementById("view-task-status").innerHTML = task.status?.task_status_name || "<em>Not Defined</em>";

    const statusSelect = document.getElementById("edit-task-status");
    statusSelect.innerHTML = statuses
      .map(status => `<option value="${status.id}">${status.task_status_name}</option>`)
      .join("");
    if (task.status) statusSelect.value = task.status.id;

    const editForm = document.getElementById("task-edit-form");
    editForm.action = `/tasks/${task.id}`;
    document.getElementById("edit-task-title").value = task.task_title || "";
    document.getElementById("edit-task-description").value = task.task_description || "";
    const editDeadline = deadline ? deadline.toISOString().split('T')[0] : '';
    document.getElementById("edit-task-deadline").value = deadline ? deadline.toISOString().split('T')[0] : "";
    document.getElementById("edit-task-priority").value = task.task_priority || "Low";
  },

  populateAssignedUsers: (users, task) => { // Accept both users and task
    const assignedUsersList = document.getElementById('assigned-users-list');
    assignedUsersList.innerHTML = '';

    if (users && users.length > 0) {
      users.forEach(user => {
        const li = document.createElement('li');
        li.className = 'list-group-item d-flex justify-content-between align-items-center';
        // Display username, Task ID, and Assigned Task ID
        li.innerHTML = `
        ${user.username}
        <form method="POST" action="/assigned-task/${user.id}/${task.id}" class="remove-assigned-user-form">
          <input type="hidden" name="_token" value="${document.querySelector('meta[name="csrf-token"]').content}">
          <input type="hidden" name="_method" value="DELETE">
          <button type="submit" class="btn btn-danger btn-sm remove-assign-user"
            data-user-id="${user.id}"
            data-task-id="${task.id}"
          >Remove</button>
        </form>
        `;
        assignedUsersList.appendChild(li);
      });
    } else {
      assignedUsersList.innerHTML = '<li class="list-group-item">No users assigned.</li>';
    }
    TaskPopup.initRemoveAssignUser();
  },

  initRemoveAssignUser: () => {
    document.querySelectorAll('.remove-assign-user').forEach(button => {
      button.addEventListener('click', function(event) {
        event.preventDefault();
        const form = this.closest('.remove-assigned-user-form');
        const assignedTaskId = this.getAttribute('data-user-id');
  
        fetch(form.action, {
          method: 'DELETE',
          headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
          },
          body: JSON.stringify({ assigned_task_id: assignedTaskId })
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            form.closest('li').remove();
            console.log(`Assigned Task ID ${assignedTaskId} removed successfully.`);
            TaskPopup.displayFlashMessage('User removed successfully.', 'success');
            TaskPopup.close(); 
          } 
        })
        .catch(error => {
          console.error('Error:', error);
          TaskPopup.displayFlashMessage('An unexpected error occurred.', 'danger');
        });
      });
    });
  },

  displayFlashMessage: (message, type) => {
    const flashMessageContainer = document.getElementById('flash-message-container');
    if (!flashMessageContainer) return; // Ensure container exists

    const flashMessage = document.createElement('div');
    flashMessage.className = `alert alert-${type}`;
    flashMessage.id = "flash-message";
    flashMessage.textContent = message;

    flashMessageContainer.appendChild(flashMessage);

    // Automatically remove the flash message after 3 seconds
    setTimeout(() => {
      flashMessage.remove();
    }, 3000);
  },

  openEditMode: () => {
    const editForm = document.getElementById("task-edit-form");
    const viewSection = document.getElementById("view-task-section");
    const taskMenu = document.getElementById("task-menu");
    const menuButton = document.getElementById("menu-button");
    const commentsSection = document.getElementById("comments-section");
    const addCommentArea = document.querySelector(".add_comment_area");

    menuButton.classList.add("d-none");
    editForm.classList.remove("d-none");
    viewSection.classList.add("d-none");

    taskMenu.classList.add("d-none");
    commentsSection.classList.add("d-none");
    if (addCommentArea) addCommentArea.classList.add("d-none");
  },

  closeEditMode: () => {
    const editForm = document.getElementById("task-edit-form");
    const viewSection = document.getElementById("view-task-section");
    const taskMenu = document.getElementById("task-menu");
    const menuButton = document.getElementById("menu-button");
    const commentsSection = document.getElementById("comments-section");
    const addCommentArea = document.querySelector(".add_comment_area");

    menuButton.classList.remove("d-none");

    editForm.classList.add("d-none");
    viewSection.classList.remove("d-none");

    taskMenu.classList.remove("d-none");
    commentsSection.classList.remove("d-none");
    if (addCommentArea) addCommentArea.classList.remove("d-none");
  },

  close: () => {
    const popup = document.getElementById("offcanvasExample");
    const overlay = document.querySelector(".popup-overlay");
    const commentsList = document.getElementById('comments-list');
    const noCommentsMessage = document.getElementById('no-comments-message');

    console.log(commentsList);
    commentsList.innerHTML = '';
    noCommentsMessage.style.display = 'block';
    popup.classList.remove("show");
    overlay.classList.remove("show");

    TaskPopup.closeEditMode();
  },

  handleSubmit: (event) => {
    event.preventDefault();
    const form = event.target;
    const formData = new FormData(form);

    fetch(form.action, {
      method: 'POST',
      body: formData,
      headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
      }
    })
    .then(response => {
      if (!response.ok) throw new Error('Failed to update task.');
      return response.json();
    })
    .then(data => {
      TaskPopup.close();
      TaskPopup.updateTaskList(data.task);
      // Optionally, refresh assigned users without reloading the page
      TaskPopup.populateAssignedUsers(data.task.users, data.task);
      TaskPopup.displayFlashMessage('Task updated successfully.', 'success');
    })
    .catch(error => {
      console.error('Error:', error);
      TaskPopup.close();
      TaskPopup.displayFlashMessage('Failed to update task.', 'danger');
    });
  },

  updateTaskList: (task) => {
    const taskItem = document.querySelector(`.task-item[data-task-id="${task.id}"]`);
    if (taskItem) {
      const titleElem = taskItem.querySelector('.task-title');
      if (titleElem) titleElem.innerText = task.task_title;
      const descElem = taskItem.querySelector('.task-description');
      if (descElem) descElem.innerText = task.task_description;
      const priorityElem = taskItem.querySelector('.task-priority');
      if (priorityElem) priorityElem.innerText = task.task_priority;
      const statusElem = taskItem.querySelector('.task-status');
      if (statusElem && task.status) statusElem.innerText = task.status.task_status_name;
    }
  }
};

// Initialize TaskPopup when the DOM is fully loaded
document.addEventListener('DOMContentLoaded', function() {
  document.querySelectorAll('.remove-assign-user').forEach(button => {
    button.addEventListener('click', function(event) {
      event.preventDefault();
      const form = this.closest('.remove-assigned-user-form');
      const userId = this.getAttribute('data-user-id');
      const taskId = this.getAttribute('data-task-id');

      fetch(form.action, {
        method: 'DELETE',
        headers: {
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        },
        body: JSON.stringify({ user_id: userId, task_id: taskId })
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          form.closest('li').remove();
          console.log(`User ID ${userId} removed successfully from Task ID ${taskId}.`);
        } else {
          console.error(`Failed to remove user ID ${userId}:`, data.message);
        }
      })
      .catch(error => {
        console.error('Error:', error);
      });
    });
  });
});


// Remove redundant fetch on each .task-link click (it's already done in TaskPopup.fetchDetails)
document.querySelectorAll('.task-link').forEach(taskLink => {
  taskLink.addEventListener('click', function (e) {
    e.preventDefault();
    const taskId = this.dataset.taskId;
    TaskPopup.fetchDetails(taskId);
  });
});

document.getElementById("task-edit-form").addEventListener("submit", TaskPopup.handleSubmit);

document.querySelector(".popup-overlay").addEventListener("click", function() {
  const taskPopup = document.getElementById("offcanvasExample");
  taskPopup.classList.remove("show");
  this.classList.remove("show");
});

// Delete Task
document.addEventListener("DOMContentLoaded", function() {
  const deleteTaskButton = document.getElementById("delete-task");

  function showFlashMessage(message, type = 'success') {
    const flashContainer = document.createElement('div');
    flashContainer.id = 'flash-message';
    flashContainer.className = `alert alert-${type} alert-dismissible fade show`;
    flashContainer.style.position = 'fixed';
    flashContainer.style.top = '1.5rem';
    flashContainer.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;

    document.body.insertBefore(flashContainer, document.body.firstChild);
    FlashMessageHandler.init();
  }

  if (deleteTaskButton) {
    deleteTaskButton.addEventListener("click", async function(e) {
      e.preventDefault();

      const offcanvas = document.getElementById("offcanvasExample");
      const taskId = offcanvas.getAttribute("data-task-id");

      if (!taskId) {
        showFlashMessage('Task ID not found.', 'danger');
        return;
      }

      if (!confirm("Are you sure you want to delete this task?")) {
        return;
      }

      try {
        deleteTaskButton.disabled = true;
        deleteTaskButton.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Deleting...';

        const response = await fetch(`/tasks/${taskId}`, {
          method: 'DELETE',
          headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
          }
        });

        const data = await response.json();

        if (!response.ok) {
          throw new Error(data.message || 'Failed to delete task.');
        }

        const bsOffcanvas = bootstrap.Offcanvas.getInstance(offcanvas);
        if (bsOffcanvas) {
          bsOffcanvas.hide();
        }

        const taskLink = document.querySelector(`a[data-task-id="${taskId}"]`);
        if (taskLink) {
          const taskItem = taskLink.closest('.task-item');
          if (taskItem) {
            const taskList = taskItem.closest('.task-list');
            taskItem.remove();

            if (taskList) {
              const remainingTasks = taskList.querySelectorAll('.task-item');
              if (remainingTasks.length === 0) {
                taskList.innerHTML = '<p class="text-muted fst-italic">No tasks available.</p>';
              }
            }
          }
        }

        showFlashMessage(data.message || 'Task deleted successfully.', 'success');

      } catch (error) {
        console.error('Error:', error);
        showFlashMessage(error.message || 'Failed to delete task.', 'danger');
      } finally {
        deleteTaskButton.disabled = false;
        deleteTaskButton.innerHTML = '<i class="bi bi-trash"></i> Delete Task';
      }
    });
  }
});

document.addEventListener('DOMContentLoaded', function() {
  const commentForm = document.getElementById('comment-form');
  const commentContent = document.getElementById('comment-content');
  const commentsList = document.getElementById('comments-list');
  const noCommentsMessage = document.getElementById('no-comments-message');
  const offcanvasElement = document.getElementById('offcanvasExample');

  let taskId = null;

  function escapeHtml(text) {
    const map = {
      '&': '&amp;',
      '<': '&lt;',
      '>': '&gt;',
      '"': '&quot;',
      "'": '&#039;',
    };
    return text.replace(/[&<>"']/g, function(m) { return map[m]; });
  }

  function loadComments() {
    if (!taskId) {
      console.error('Task ID is not available');
      return;
    }
    fetch(`/tasks/${taskId}/comments`)
        .then((response) => response.json())
        .then((data) => {
          if (!commentsList || !noCommentsMessage) {
            console.error("Comments list or noCommentsMessage element not found.");
            return;
          }

          if (data.length === 0) {
            noCommentsMessage.style.display = "block";
            commentsList.innerHTML = "";
          } else {
            noCommentsMessage.style.display = "none";
            commentsList.innerHTML = "";
            data.forEach((comment) => {
              const commentElement = document.createElement("div");
              commentElement.classList.add("card", "mb-2", "p-3", "shadow-sm", "border-0");
              commentElement.innerHTML = `
              <div class="d-flex justify-content-between align-items-center">
                <strong>${escapeHtml(comment.user.username)}</strong>
                <div class="d-flex align-items-center gap-2">
                    <small class="text-muted">${new Date(comment.created_at).toLocaleString()}</small>
                    <button class="btn btn-icon delete-comment" data-comment-id="${comment.id}">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M4 6.17647H20M9 3H15M10 16.7647V10.4118M14 16.7647V10.4118M15.5 21H8.5C7.39543 21 6.5 20.0519 6.5 18.8824L6.0434 7.27937C6.01973 6.67783 6.47392 6.17647 7.04253 6.17647H16.9575C17.5261 6.17647 17.9803 6.67783 17.9566 7.27937L17.5 18.8824C17.5 20.0519 16.6046 21 15.5 21Z" 
                                  stroke="black" 
                                  stroke-width="2" 
                                  stroke-linecap="round" 
                                  stroke-linejoin="round"
                            />
                        </svg>
                    </button>
                </div>
              </div>
              <p class="fs-6 mb-1">${escapeHtml(comment.comment_content)}</p>
        `;
              commentsList.appendChild(commentElement);
            });

            document.querySelectorAll(".delete-comment").forEach((button) => {
              button.addEventListener("click", (e) => {
                const commentId = e.currentTarget.getAttribute("data-comment-id");
                deleteComment(commentId);
              });
            });
          }
        })
        .catch((error) => {
          console.error("Error loading comments:", error);
        });

    function deleteComment(commentId) {
      if (!confirm("Are you sure you want to delete this comment?")) return;

      fetch(`/comments/${commentId}`, {
        method: "DELETE",
        headers: {
          "Content-Type": "application/json",
          "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content"),
        },
      })
          .then((response) => {
            if (response.ok) {
              document.querySelector(`.delete-comment[data-comment-id="${commentId}"]`).closest(".card").remove();
            } else {
              console.error("Failed to delete comment");
            }
          })
          .catch((error) => {
            console.error("Error deleting comment:", error);
          });
    }

  }

  offcanvasElement.addEventListener('show.bs.offcanvas', function(event) {
    const triggerElement = event.relatedTarget;
    taskId = triggerElement.getAttribute('data-task-id');
    loadComments();
  });

  commentForm.addEventListener('submit', function(event) {
    event.preventDefault();

    if (!taskId) {
      console.error('Task ID is not available');
      return;
    }

    const submitButton = document.getElementById('submit-comment');
    const originalButtonHTML = submitButton.innerHTML;
    submitButton.disabled = true;
    submitButton.innerHTML = '<i class="bi bi-arrow-repeat me-2"></i> Submitting...';

    fetch(`/tasks/${taskId}/comments`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
      },
      body: JSON.stringify({
        comment_content: commentContent.value.trim()
      })
    })
        .then(response => {
          if (!response.ok) {
            return response.json().then(data => {
              const errorMsg = data.errors ? Object.values(data.errors).flat().join(' ') : data.error || 'Failed to add comment.';
              throw new Error(errorMsg);
            });
          }
          return response.json();
        })
        .then(data => {
          commentContent.value = '';
          submitButton.disabled = false;
          submitButton.innerHTML = originalButtonHTML;
          loadComments();
        })
        .catch(error => {
          console.error('Error:', error);
          alert(error.message);
          submitButton.disabled = false;
          submitButton.innerHTML = originalButtonHTML;
        });
  });
});

// Add Task Popup
const AddTaskPopup = {
  init: () => {
    const popup = document.getElementById("add-task-popup");
    const overlay = document.querySelector(".popup-overlay");
    const openPopupButtons = document.querySelectorAll(".add-task-popup-trigger");
    const closePopupButton = document.getElementById("close-add-task-popup");
    const statusInput = document.getElementById("popup-task-status-id");

    // Show popup
    openPopupButtons.forEach((button) => {
      button.addEventListener("click", () => {
        const statusId = button.getAttribute("data-status-id");
        statusInput.value = statusId;
        popup.classList.remove("d-none");
      });
    });

    // Hide popup
    const closePopup = () => {
      popup.classList.add("d-none");
      statusInput.value = "";
    };

    closePopupButton.addEventListener("click", closePopup);
    overlay.addEventListener("click", closePopup);
  }
};

// Project Panel
const ProjectPanel = {
  init: () => {
    const addButton = document.querySelector(".button-add-project");
    const closeButton = document.querySelector("#project-close-button");
    const panel = document.querySelector("#project-panel-container");
    const overlay = document.querySelector("#project-panel-overlay");

    addButton.addEventListener("click", (e) => {
      e.preventDefault();
      panel.classList.add("active");
      overlay.classList.add("active");
      overlay.style.display = "block";
    });

    const closePanel = () => {
      panel.classList.remove("active");
      overlay.classList.remove("active");
    };

    closeButton.addEventListener("click", closePanel);
    overlay.addEventListener("click", (e) => {
      if (e.target === overlay) closePanel();
    });
  }
};

// Profile Modal
const ProfileModal = {
  init: () => {
    const editProfileLink = document.getElementById("editProfileLink");
    if (editProfileLink) {
      editProfileLink.addEventListener("click", (event) => {
        event.preventDefault();
        const modal = new bootstrap.Modal(document.getElementById("editProfileModal"));
        modal.show();

        const editUrl = editProfileLink.getAttribute("data-edit-url");
        fetch(editUrl)
            .then((response) => response.text())
            .then((html) => {
              document.getElementById("editProfileContent").innerHTML = html;
            })
            .catch((error) => {
              console.error("Error loading profile edit form:", error);
            });
      });
    }
  }
};

document.addEventListener("DOMContentLoaded", function () {

  const userListContainer = document.getElementById("user-list-container");

  if (userListContainer) {
    userListContainer.addEventListener('submit', function (e) {
      if (e.target.classList.contains('add-user-form')) {
        e.preventDefault();

        const form = e.target;
        const button = form.querySelector('.add-button');
        if (!button) {
          return;
        }

        const originalText = button.textContent;

        button.disabled = true;
        button.textContent = 'Adding...';

        setTimeout(function () {
          fetch(form.action, {
            method: 'POST',
            headers: {
              'Content-Type': 'application/x-www-form-urlencoded',
              'X-CSRF-TOKEN': form.querySelector('input[name="_token"]').value
            },
            body: new URLSearchParams(new FormData(form)).toString()
          })
              .then(response => response.json())
              .then(data => {
                if (data.message === 'User added successfully.') {
                  button.textContent = 'Added';
                } else {
                  button.textContent = originalText;
                  alert(data.message);
                }

                button.disabled = false;
              })
              .catch(error => {
                button.textContent = originalText;
                button.disabled = false;
              });
        }, 1000);
      }
    });
  }

  const closeButtons = document.querySelectorAll('.btn-close');
  closeButtons.forEach(function (button) {
    button.addEventListener('click', function () {
      const alertBox = button.closest('.alert');
      if (alertBox) {
        alertBox.style.display = 'none';
      }
    });
  });
});

document.addEventListener("DOMContentLoaded", function () {
  let currentPage = 1;
  const userList = document.getElementById('userList');
  const showMoreBtn = document.getElementById('showMoreBtn');
  const searchInput = document.getElementById('userSearch');

  const projectId = document.getElementById('projectId').getAttribute('data-project-id');

  function loadUsers() {
    const query = searchInput.value.trim();

    fetch(`/projects/${projectId}/contributors?page=${currentPage}&search=${encodeURIComponent(query)}`)
        .then(response => response.json())
        .then(data => {
          if (data.html) {
            if (currentPage === 1) {
              userList.innerHTML = data.html;
            } else {
              userList.innerHTML += data.html;
            }
          }
          if (!data.hasMore) {
            showMoreBtn.style.display = 'none';
          }
        })
        .catch(err => {
          console.error('Error loading users:', err);
        });
  }

  if (showMoreBtn) {
    showMoreBtn.addEventListener('click', function () {
      currentPage++;
      loadUsers();
    });
  }

  searchInput.addEventListener('input', function () {
    currentPage = 1;
    userList.innerHTML = '';
    loadUsers();
  });

  loadUsers();
});

document.addEventListener("DOMContentLoaded", function () {
  const searchForm = document.getElementById('search-form');
  const searchResultsDiv = document.getElementById('search-results');

  searchForm.addEventListener('submit', function (e) {
    e.preventDefault();

    const query = document.getElementById('query').value.trim();

    if (!query) {
      return;
    }
    const url = `/search?query=${encodeURIComponent(query)}`;

    fetch(url, {
      method: 'GET',
    })
        .then(response => response.json())
        .then(data => {
          searchResultsDiv.innerHTML = '';

          const appendSection = (title, items, itemTemplate) => {
            if (items.length) {
              searchResultsDiv.innerHTML += `<h4>${title}</h4>`;
              items.forEach(item => {
                searchResultsDiv.innerHTML += itemTemplate(item);
              });
            } else {
              searchResultsDiv.innerHTML += `<p>No ${title.toLowerCase()} found.</p>`;
            }
          };

          appendSection('Users', data.users, (user) => {
            return `<p>${user.username} (${user.email})</p>`;
          });

          appendSection('Tasks', data.tasks, (task) => {
            return `<p><a href="${task.project_url}">${task.task_title}</a> - ${task.task_description}</p>`;
          });

          appendSection('Projects', data.projects, (project) => {
            return `<p><a href="${project.url}">${project.project_name}</a> - ${project.project_description}</p>`;
          });
        })
        .catch(error => {
          searchResultsDiv.innerHTML = '<p>An error occurred. Please try again later.</p>';
        });
  });
});

document.addEventListener("DOMContentLoaded", () => {
  FlashMessageHandler.init();
  TaskPopup.init();
  AddTaskPopup.init();
  ProjectPanel.init();
  ProfileModal.init();
});

document.addEventListener('DOMContentLoaded', function () {
  const confirmLeaveButton = document.getElementById('confirmLeave');
  const leaveProjectForm = document.getElementById('leaveProjectForm');

  if (confirmLeaveButton && leaveProjectForm) {
    confirmLeaveButton.addEventListener('click', function () {
      leaveProjectForm.submit();
    });
  }
});

// Assign task to user
document.addEventListener('DOMContentLoaded', function() {
  const taskLinks = document.querySelectorAll('.task-link');
  const assignTaskForm = document.getElementById('assign-task-form');
  const taskIdInput = document.getElementById('assign-task-id');

  taskLinks.forEach(taskLink => {
    taskLink.addEventListener('click', function() {
      const taskId = this.getAttribute('data-task-id');
      // Update the hidden task_id field
      taskIdInput.value = taskId;
    });
  });
});

document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('.btn-favorite').forEach(button => {
    button.addEventListener('click', async function (event) {
      event.preventDefault();

      const isFavorited = this.dataset.favorited === 'true';
      const projectId = this.dataset.projectId;
      const url = isFavorited
          ? `/projects/${projectId}/unfavorite`
          : `/projects/${projectId}/favorite`;

      try {
        const response = await fetch(url, {
          method: isFavorited ? 'DELETE' : 'POST',
          headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json',
          },
        });

        const result = await response.json();

        if (response.ok) {
          this.dataset.favorited = !isFavorited;

          const icon = this.querySelector('i');
          const text = this.querySelector('span');
          icon.className = !isFavorited ? 'bi bi-star-fill' : 'bi bi-star';
          icon.style.color = !isFavorited ? '#c9a227' : 'black';
          text.textContent = !isFavorited ? 'Unfavorite' : 'Favorite';

          displayFlashMessage(result.message, 'success');
        } else {
          displayFlashMessage(result.message, 'danger');
        }
      } catch (error) {
        console.error('An error occurred:', error);
        displayFlashMessage('An unexpected error occurred.', 'danger');
      }
    });
  });

  function displayFlashMessage(message, type) {
    const flashMessageContainer = document.getElementById('flash-message-container');
    const flashMessage = document.createElement('div');
    flashMessage.className = `alert alert-${type}`;
    flashMessage.id = "flash-message";
    flashMessage.textContent = message;

    flashMessageContainer.appendChild(flashMessage);

    FlashMessageHandler.init();
  }
});

document.addEventListener('DOMContentLoaded', function() {
  document.querySelectorAll('.remove-assign-user').forEach(button => {
      button.addEventListener('click', function() {
          const userId = this.getAttribute('data-user-id');
          const form = this.closest('.remove-assigned-user-form');
          form.submit();
      });
  });
});
