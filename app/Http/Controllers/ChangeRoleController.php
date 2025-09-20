<?php

namespace App\Http\Controllers;

use App\Models\ChangeRoleNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ChangeRoleController extends Controller
{

    public function index(Request $request)
    {
        try {
            $user = Auth::user();


            $notifications = ChangeRoleNotification::where('receiver_id', $user->id)
                ->orderBy('sent_at', 'desc')
                ->paginate(3); 

            return view('pages.changerole_notification', compact('notifications'));
        } catch (\Exception $e) {
            Log::error('Erro ao buscar notificações de mudança de papel: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error loading notifications.');
        }
    }

    public function delete($id)
    {
        try {
            $notification = ChangeRoleNotification::findOrFail($id);

            if ($notification->receiver_id !== Auth::id()) {
                return redirect()->back()->with('error', 'You do not have the permission.');
            }

            $notification->delete();

            return redirect()->back()->with('sucess', 'Notification deleted');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error deleting notification.');
        }
    }
}