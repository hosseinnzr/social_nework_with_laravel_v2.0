<?php

namespace App\Livewire\Notifications;

use App\Models\User;
use Livewire\Component;
use App\Models\notifications;
use Illuminate\Support\Facades\Auth;

class NotificationsHeader extends Component
{

    public $user_notifications;

    public $thiuserName;

    public function delete($notification_id){
        
        $notification = notifications::findOrFail($notification_id);
        $notification->seen = 1;
        $notification->save();

    }

    public function acceptRequest($notification_id){

        $user_signin = User::findOrFail(auth::id());
        $notification = notifications::where('id', $notification_id)->first();

        $userName = $notification->body;
        $user = User::where('user_name', $userName)->first();

        $followers = $user_signin->followers . ',' . $user->id;
        $followings = $user->following . ',' . $user_signin->id;
        $user_request = $user->request_list;

        $user_request_lists = explode(",", $user_request);

        foreach($user_request_lists as $user_request_list){
            if ($user_request_list == $userName){
                
                $new_request_lists = array_diff($user_request_lists, array($userName));

                $user_request = implode(",", $new_request_lists);

                break;
            }
        }

        $user_signin->request_list = $user_request;
        
        // send notifiction
        notifications::create([
            'UID' => $user_signin->id,
            'body' => $user->user_name,
            'type'=> 'accept Request',
            'url' => '',
            'user_profile' => Auth::user()->profile_pic,
        ]);

        // delete request notifiction
        $notification->update(['delete' => 1]);

        $followers = array_unique(explode(",", $followers));
        $followings = array_unique(explode(",", $followings));

        $followers = implode(",", $followers);
        $followings = implode(",", $followings);


        // save follow
        $user_signin->followers = $followers;
        $user->following = $followings;

        if ($user_signin->followers == "0"){
            $followers_number = 1;
        }else{
            $followers_number = count(explode(",", $user_signin->followers));
        }

        if ($user->following == "0"){
            $following_number = 1;
        }else{
            $following_number = count(explode(",", $user->following));
        }

        $user_signin->followers_number = $followers_number -1;
        $user->following_number = $following_number -1;

        $user_signin->save();
        $user->save();

        // notify()->success('accept follow request from '.$userName);
        // return redirect('notifications');
    }

    public function deleteRequest($notification_id){
        $user_signin = User::findOrFail(auth::id());
        $notification = notifications::where('id', $notification_id)->first();

        $userName = $notification->body;
        $user = User::where('user_name', $userName)->first();

        $user_request = $user->request_list;

        $user_request_lists = explode(",", $user_request);

        foreach($user_request_lists as $user_request_list){
            if ($user_request_list == $userName){
                
                $new_request_lists = array_diff($user_request_lists, array($userName));

                $user_request = implode(",", $new_request_lists);

                break;
            }
        }

        // delete request notifiction
        $notification->update(['delete' => 1]);

        $user_signin->request_list = $user_request;

        $user_signin->save();
        $user->save();

        // notify()->success('delete follow request from '.$userName);
        // return redirect('notifications');
    }

    public function render()
    {
        $this->user_notifications = notifications::latest()->where('UID', Auth::id())->where('seen', 0)->where('delete', '0')->get();

        return view('livewire.notifications.notifications-header');
    }
}
