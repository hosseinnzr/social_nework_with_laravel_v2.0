<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Models\notifications;
use App\Models\follow;
use App\Models\followRequest;

class RequestServices
{
    function acceptRequest($notificationid, $userName){
        $user_signin = User::findOrFail(auth::id());
        $user = User::where('user_name', $userName)->first();

        // delete follow request
        $find_follow_user = followRequest::where('follower_id',$user['id'])->where('following_id', auth::id());
        $find_follow_user->delete();

        // add user to followr
        if(!follow::where('follower_id',$user['id'])->where('following_id', auth::id())->exists())
        {
            follow::create([
                'follower_id' => $user['id'],
                'following_id' => auth::id(),
            ]);
        }
        
        // send notifiction
        notifications::create([
            'UID' => $user_signin->id,
            'body' => $user->user_name,
            'type'=> 'accept Request',
            'url' => '',
            'user_profile' => Auth::user()->profile_pic,
        ]);

        // delete request notifiction
        $post = notifications::where('id', $notificationid);
        $post->update(['delete' => 1]);

        // update follow number
        $user_signin->followers_number = follow::where('following_id',auth::id())->count();
        $user_signin->save();       

        $user->following_number = follow::where('follower_id',$user['id'])->count();
        $user->save();
    }

    function deleteRequest($notificationid, $userName){
        $user = User::where('user_name', $userName)->first();

        // delete follow request
        $find_follow_user = followRequest::where('follower_id',$user['id'])->where('following_id', auth::id());

        $find_follow_user->delete();

        // delete request notifiction
        $post = notifications::where('id', $notificationid);
        $post->update(['delete' => 1]);
    }
}
