<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

use Illuminate\View\View;

class LoginController extends Controller
{

    /**
     * Display a login form.
     */
    public function showLoginForm()
    {
        if (Auth::check()) {
            if(Auth::user()->isAdmin()) {
                return redirect('/admin');
            }
            else return redirect('/projects');
        } else {
            return view('auth.login');
        }
    }

    /**
     * Handle an authentication attempt.
     */
    public function authenticate(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);
        if (Auth::attempt($credentials, $request->filled('remember'))) {
            $user = Auth::user();
            if ($user->user_status === 'Blocked') {
                Auth::logout();

                return back()->withErrors([
                    'email' => 'Your account has been blocked.',
                ])->onlyInput('email');
            }

            $request->session()->regenerate();
            if($user->isAdmin()) {
                return redirect('/admin')
                    ->with('success', 'You have logged in successfully!');
            }
            return redirect()->intended('/projects')
                ->with('success', 'You have logged in successfully!');
        }
        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    /**
     * Log out the user from application.
     */
    public function logout(Request $request)
    {
        try {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect()->route('login')
                ->with('success', 'You have logged out successfully!');
        } catch (\Exception $e) {
            return redirect()->route('projects')
                ->with('error', 'Failed to logout. Please try again.');
        }
    }
}
