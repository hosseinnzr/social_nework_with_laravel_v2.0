<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\notifications;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index(){
        $notifications = notifications::latest()->where('UID', Auth::id())->get();
        return view('pages.notifications',[
            'user_notifications' => $notifications,
        ]);
    }

    public function destroy(){

    }
}
