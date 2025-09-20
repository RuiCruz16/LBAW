<?php

namespace App\Http\Controllers;

use App\Events\All_Notification;
use Illuminate\Http\Request;

class EventNotificationController extends Controller
{
    public function sendNotification(Request $request)
    {
        $message = $request->input('message');
        event(new All_Notification($message));

        return response()->json(['status' => 'Notificação enviada com sucesso.']);
    }
}