<?php

namespace App\Livewire\Notifications;

use Livewire\Component;
use App\Models\notifications;
use Illuminate\Support\Facades\Auth;

class NotificationsHeader extends Component
{

    public $user_notifications;

    public function delete($notification_id){
        
        $notification = notifications::findOrFail($notification_id);
        // dd($notification);
        $notification->seen = 1;
        $notification->save();
        // dd($notification);

    }

    public function render()
    {
        $this->user_notifications = notifications::latest()->where('UID', Auth::id())->where('seen', 0)->where('delete', '0')->get();

        return view('livewire.notifications.notifications-header');
    }
}
