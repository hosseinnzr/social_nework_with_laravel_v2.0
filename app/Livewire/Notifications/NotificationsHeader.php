<?php

namespace App\Livewire\Notifications;

use App\Models\User;
use Livewire\Component;
use App\Models\notifications;
use Illuminate\Support\Facades\Auth;
use App\Services\RequestServices;

class NotificationsHeader extends Component
{
    public $user_notifications;
    public $notificationid;
    public $userName;

    public function delete($notificationid)
    {
        $notification = notifications::findOrFail($notificationid);
        $notification->seen = 1;
        $notification->save();
    }

    public function acceptRequest($notificationid, $userName)
    {
        $authService = new RequestServices();
        $authService->acceptRequest($notificationid, $userName);
    }
    

    public function deleteRequest($notificationid, $userName)
    {
        $authService = new RequestServices();
        $authService->deleteRequest($notificationid, $userName);
    }

    public function render()
    {

        $this->user_notifications = notifications::latest()
            ->where('UID', Auth::id())
            ->where('seen', 0)
            ->where('delete', '0')
            // ->limit(10)
            ->get();

        return view('livewire.notifications.notifications-header');
    }
}
