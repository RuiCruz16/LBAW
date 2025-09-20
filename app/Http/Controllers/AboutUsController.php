<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AboutUsController extends Controller
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

    public function show()
    {
        return view('pages.about');
    }
}
