<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Project;
use App\Models\Image;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $user = Auth::user();

            if ($user && $user->user_status === 'Blocked') {
                Auth::logout();
                return redirect('/login')->with('error', 'Your account has been blocked.');
            }

            return $next($request);
        });
    }

    /**
     * Display the authenticated user's profile or another user's profile.
     */
    public function show($id)
    {
        try {
            $user = User::findOrFail($id);
            $perPage = 9;

            $projectsQuery = Project::where('creator_id', $id)
                ->orderBy('created_at', 'desc');
            $totalProjects = $projectsQuery->count();
            $projects = $projectsQuery->take($perPage)->get();

            $favoriteProjectsQuery = $user->favoriteProjects()
                ->orderBy('created_at', 'desc');
            $totalFavorites = $favoriteProjectsQuery->count();
            $favoriteProjects = $favoriteProjectsQuery->take($perPage)->get();

            if (request()->ajax()) {
                $page = request()->query('page', 1);
                $section = request()->query('section');

                if ($section === 'projects') {
                    $projects = $projectsQuery->skip(($page - 1) * $perPage)
                        ->take($perPage)
                        ->get();

                    return response()->json([
                        'html' => view('partials.project_cards', ['projects' => $projects])->render(),
                        'hasMore' => ($page * $perPage) < $totalProjects
                    ]);
                } else if ($section === 'favorites') {
                    $favoriteProjects = $favoriteProjectsQuery->skip(($page - 1) * $perPage)
                        ->take($perPage)
                        ->get();

                    return response()->json([
                        'html' => view('partials.project_cards', ['projects' => $favoriteProjects])->render(),
                        'hasMore' => ($page * $perPage) < $totalFavorites
                    ]);
                }
            }

            $hasMoreProjects = $totalProjects > $perPage;
            $hasMoreFavorites = $totalFavorites > $perPage;

            return view('pages.profile', compact(
                'user',
                'projects',
                'favoriteProjects',
                'hasMoreProjects',
                'hasMoreFavorites'
            ));
        } catch (\Exception $e) {
            return view('pages.errors.404');
        }
    }

    /**
     * Show the form for editing the authenticated user's profile.
     */
    public function edit($id)
    {
        try {
            $user = User::findOrFail($id);
            return view('pages.edit_profile', compact('user'));
        } catch (\Exception $e) {
            return view('pages.errors.404');
        }

    }


    /**
     * Update the authenticated user's profile.
     */
    public function update(Request $request)
    {
        $user = Auth::user();
        $userToUpdate = User::find((int)$request->input("id"));

        try {
            $id = (int) $request->input('id');

            $request->validate([
                'id' => 'required|integer|exists:users,id',
                'username' => 'required|string|max:255|unique:users,username,' . $id,
                'email' => 'required|string|email|max:255|unique:users,email,' . $id,
                'biography' => 'nullable|string|max:500',
                'password' => 'nullable|string|min:8|confirmed',
                'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg',
            ]);

            if ($user->id != $userToUpdate->id && !$user->isAdmin()) {
                return redirect()->back()->with('error', 'You do not have permission to edit this user.');
            }

            $userToUpdate->username = $request->input('username');
            $userToUpdate->email = $request->input('email');
            $userToUpdate->biography = $request->input('biography');

            if ($request->filled('password')) {
                $userToUpdate->password = bcrypt($request->input('password'));
            }

            if ($request->hasFile('profile_picture')) {
                $imagePath = $request->file('profile_picture')->store('images', 'public');
                if ($userToUpdate->image) {
                    Storage::disk('public')->delete($userToUpdate->image->image_path);
                    $userToUpdate->image->delete();
                }

                Image::create([
                    'image_path' => $imagePath,
                    'user_id' => $userToUpdate->id,
                ]);
            }

            $userToUpdate->save();

            return redirect()->route('profile.show', ['id' => $userToUpdate->id])
                ->with('success', 'Profile updated successfully!');
        } catch (\Exception $e) {
            return redirect()->route('profile.show', ['id' => $userToUpdate->id])
                ->with('error', 'Failed to update profile. Please try again.');
        }
    }

    public function destroy($userId)
    {
        try {
            $user = User::findOrFail($userId);
            $requestUser = Auth::user();
            $anonymousUserId = 1;

            Comment::where('user_id', $userId)->update(['user_id' => $anonymousUserId]);

            Project::where('creator_id', $userId)->update(['creator_id' => $anonymousUserId]);

            $user->delete();

            if ($userId == $requestUser->id) {
                Auth::logout();
                return redirect('/login')->with('success', 'Your account has been deleted successfully.');
            } else {
                return redirect('/admin')->with('success', "The $user->username account has been deleted successfully.");
            }
        } catch (\Exception $e) {
            Log::error('Error deleting account: ' . $e->getMessage());
            return redirect()->back()->with('error', 'There was an error deleting the user account. Please try again.');
        }
    }

}
