<?php

use App\Http\Controllers\AboutUsController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\MailController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProjectInvitationController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AssignedTaskController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ChangeRoleController;
use App\Http\Controllers\NumberofNotificationsController;

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\ProfileController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Home
Route::redirect('/', '/login');

// Projects
Route::controller(ProjectController::class)->group(function () {
    Route::get('/projects', [ProjectController::class, 'filter'])->name('projects');
    Route::get('/projects/create', 'showCreateForm')->name('projects.create.view');
    Route::post(uri:'/projects/create', action: 'create')->name('projects.create');
    Route::get('/projects/{id}', [ProjectController::class, 'show'])->name('project.show');
    Route::post('/projects/{projectId}/add-member', [ProjectController::class, 'addMember'])->name('projects.addMember');
    Route::delete('/projects/{projectId}/remove-member/{userId}', [ProjectController::class, 'removeMember'])->name('projects.remove-member');
    Route::get('/projects/{projectId}/contributors', [ProjectController::class, 'getContributors'])->name('projects.getContributors');
    Route::post('/projects/{projectId}/archive', [ProjectController::class, 'archiveProject'])->name('projects.archive');
    Route::post('/projects/{projectId}/unarchive', [ProjectController::class, 'unarchiveProject'])->name('projects.unarchive');
    Route::put('/projects/{project}/promote/{member}', [ProjectController::class, 'promote'])->name('projects.promote');
    Route::put('/projects/{project}/demote/{member}', [ProjectController::class, 'demote'])->name('projects.demote');
    Route::post('/projects/{project}/leave', [ProjectController::class, 'leave'])->name('projects.leave');
    Route::get('/projects/{project}/edit', [ProjectController::class, 'edit'])->name('projects.edit');
    Route::put('/projects/{project}', [ProjectController::class, 'update'])->name('project.update');
    Route::get('/projects/search', [ProjectController::class,'index'])->name('users.search');
    Route::get('/admin', [ProjectController::class, 'index'])->name('admin.projects');
    Route::post('/projects/{project}/favorite', [ProjectController::class, 'favorite'])->name('project.favorite');
    Route::delete('/projects/{project}/unfavorite', [ProjectController::class, 'unfavorite'])->name('project.unfavorite');
    Route::get('/view-your-projects', [ProjectController::class, 'viewYourProjects'])->name('view_your_projects');
    Route::get('/projects', [ProjectController::class, 'filterNotifications'])->name('projects');
});


// Tasks
Route::controller(TaskController::class)->group(function () {
    Route::post('/tasks/{project}', [TaskController::class, 'store'])->name('tasks.store');
    Route::get('/tasks/{id}', [TaskController::class, 'show'])->name('tasks.show');
    Route::put('/tasks/{id}', [TaskController::class, 'update'])->name('tasks.update');
    Route::delete('/tasks/{id}', [TaskController::class, 'destroy'])->name('tasks.destroy');
});

// Comments
Route::controller(CommentController::class)->group(function () {
    Route::get('/tasks/{taskId}/comments', [CommentController::class, 'index'])->name('comments.index');
    Route::post('/tasks/{taskId}/comments', [CommentController::class, 'store'])->name('comments.store');
    Route::delete('/comments/{commentId}', [CommentController::class, 'destroy'])->name('comments.destroy');
});

// Profile
Route::controller(ProfileController::class)->group(function () {
    Route::get('/profile/{id}', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/{id}/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/profile', 'update')->name('profile.update');
    Route::delete('/profile/{userId}', [ProfileController::class, 'destroy'])->name('profile.delete');
});

// User
Route::controller(UserController::class)->group(function () {
    Route::get('/admin', 'index')->name('admin.users');
    Route::patch('/users/{userId}/block', 'blockUser')->name('users.block');
    Route::patch('/users/{userId}/unblock', 'unblockUser')->name('users.unblock');
    Route::get('/users/search', [UserController::class, 'searchUsers'])->name('users.invite'); // used in invites
});

// Search
Route::get('/search', [SearchController::class, 'search'])->name('search');

// API

// Login
Route::controller(LoginController::class)->group(function () {
    Route::get('/login', 'showLoginForm')->name('login');
    Route::post('/login', 'authenticate');
    Route::get('/logout', 'logout')->name('logout');
});

// Register
Route::controller(RegisterController::class)->group(function () {
    Route::get('/register', 'showRegistrationForm')->name('register');
    Route::post('/register', 'register');
});

// About Us
Route::controller(AboutUsController::class)->group(function () {
    Route::get('/about', 'show')->name('about');
});

// Contact
Route::controller(ContactController::class)->group(function () {
    Route::get('/contact', 'show')->name('contact');
});

// Notifications
Route::get('/notifications', [NotificationController::class, 'index'])->name('all-notifications');
Route::get('/project/notifications', [ProjectInvitationController::class, 'index'])->name('project.notifications');
Route::get('/assigned-task/notifications', [AssignedTaskController::class, 'showNotifications'])->name('assigned-task.notifications');
Route::get('/task-completed/notifications', [NotificationController::class, 'showTaskCompletedNotifications'])->name('task-completed.notifications');
Route::get('/change-role', [ChangeRoleController::class, 'index'])->name('change-role.index')->middleware('auth');
Route::delete('/change-role/{id}', [ChangeRoleController::class, 'delete'])->name('change-role.delete')->middleware('auth');
Route::delete('/notifications/task-completed/{id}', [NotificationController::class, 'destroyTaskCompletedNotification'])->name('task-completed-notifications.destroy');
Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.markAllAsRead');

Route::controller(AssignedTaskController::class)->group(function () {
    Route::post('/assigned-task', 'create')->name('assigned-task.create');
    Route::get('/assigned-task/notifications', [AssignedTaskController::class, 'showNotifications'])->name('assigned-task.notifications');
    Route::delete('/assigned-task/{user_id}/{task_id}', [AssignedTaskController::class, 'destroy'])->name('assigned-task.destroy');
    Route::delete('/notifications/{id}', [AssignedTaskController::class, 'destroyNotification'])->name('notifications.destroy');
});

Route::controller(AdminController::class)->group(function () {
    Route::get('/admin/projects', 'indexProjects')->name('admin.projects');
    Route::get('/admin/users/search', 'searchUsers')->name('admin.users.search');
    Route::get('/admin/projects/search', 'searchProjects')->name('projects.search');
});

Route::post('/projects/invite/email', [ProjectInvitationController::class, 'createByEmail'])->name('projects.invite.email');
Route::get('/projects/invitations/accept/{token}', [ProjectInvitationController::class, 'acceptByEmail'])->name('projects.invitations.acceptEmail');

// Mail
Route::controller(MailController::class)->group(function () {
    Route::get('/password/recover', 'showRecoverForm')->name('password.recover');
    Route::post('/password/recover/send', 'sendRecoveryEmail')->name('password.recover.send');
});

// Recover Password
Route::controller(PasswordResetController::class)->group(function () {
    Route::get('/password/reset/{token}', 'showResetForm')->name('password.reset');
    Route::post('/password/reset', 'resetPassword')->name('password.update');
});

Route::controller(ProjectInvitationController::class)->group(function () {
    Route::get('/projects/{projectId}/invite', [ProjectInvitationController::class, 'show'])->name('projects.invite');
    Route::post('/projects/invite', [ProjectInvitationController::class, 'create'])->name('projects.invite.create');
    Route::get('/projects/{projectId}/invitations', [ProjectInvitationController::class, 'index'])->name('projects.invitations');
    Route::post('/project/{invitation}/accept', [ProjectInvitationController::class, 'accept'])->name('project.invitation.accept');
    Route::post('/project/{invitation}/reject', [ProjectInvitationController::class, 'reject'])->name('project.invitation.reject');
    Route::delete('/project/{invitation}/delete', [ProjectInvitationController::class, 'deleteResponse'])->name('project.invitation.delete');
    Route::delete('/project/invite/{invitation}/delete', [ProjectInvitationController::class, 'deleteInvitation'])->name('project.myinvitation.delete');
});

Route::get('/notifications/count', [NumberofNotificationsController::class, 'count'])->name('notifications.count');
Route::get('/notifications/countInvitations', [NumberofNotificationsController::class, 'countInvitations'])->name('notifications.countInvitations');
Route::get('/notifications/countChangeRole', [NumberofNotificationsController::class, 'countChangeRole'])->name('notifications.countChangeRole');
Route::get('/notifications/countAssignedTasks', [NumberofNotificationsController::class, 'countAssignedTasks'])->name('notifications.countAssignedTasks');
Route::get('/notifications/countTaskCompleted', [NumberofNotificationsController::class, 'countCompletedTasks'])->name('notifications.countTaskCompleted');

