<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Models\User;
use App\Mail\RecoverPasswordMail;

class MailController extends Controller
{
    public function showRecoverForm()
    {
        return view('auth.send_recover_email');
    }

    public function sendRecoveryEmail(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email|exists:users,email',
            ]);

            $token = Str::random(60);

            DB::table('password_resets')->updateOrInsert(
                ['email' => $request->email],
                ['token' => $token, 'created_at' => now()]
            );

            $user = User::where('email', $request->email)->first();

            if (!$user) {
                return back()->with('error', 'User not found. Please try again.');
            }

            $mailData = [
                'name' => $user->username,
                'token' => $token,
            ];

            Mail::to($request->email)->send(new RecoverPasswordMail($mailData));

            return back()->with('success', 'Password recovery email sent successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Something went wrong. Please try again later.');
        }
    }

}