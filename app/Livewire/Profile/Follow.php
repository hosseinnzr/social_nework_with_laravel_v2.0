<?php

namespace App\Livewire\Profile;

use Livewire\Component;
use App\Models\User;
use App\Models\notifications;
use App\Models\followRequest;
use Illuminate\Support\Facades\Auth;
use App\Models\follow as follow_model;

use SimpleSoftwareIO\QrCode\Facades\QrCode as QR;
class Follow extends Component
{
    public $user_id;
    public $state = 0;

    public function follow($user_id){ 
        // follow
        if(!follow_model::where('follower_id',auth::id())->where('following_id', $user_id)->exists())
        {
            follow_model::create([
                'follower_id' => auth::id(),
                'following_id' => $user_id,
            ]);
        }

        // send notifiction
        notifications::create([
            'UID' =>$user_id,
            'body' => Auth::user()->user_name,
            'type'=> 'follow',
            'from' => '',
            'user_profile' => Auth::user()->profile_pic,
        ]);

        $user = User::where('id', $user_id);

        // update follow number
        $user_signin = User::findOrFail(auth::id());
        $user = User::findOrFail($user_id);

        $user_signin->following_number = follow_model::where('follower_id',auth::id())->count();
        $user_signin->save();

        $user->followers_number = follow_model::where('following_id',$user_id)->count();
        $user->save();

        return back();
    }

    public function follow_request($user_id){
        // send request
        if(!followRequest::where('follower_id',auth::id())->where('following_id', $user_id)->exists())
        {
            followRequest::create([
                'follower_id' => auth::id(),
                'following_id' => $user_id,
            ]);
        }

        // send notifiction
        notifications::create([
            'UID' =>$user_id,
            'body' => Auth::user()->user_name,
            'type'=> 'follow_request',
            'from' => Auth::id(),
            'user_profile' => Auth::user()->profile_pic,
        ]);

        $user = User::where('id', $user_id);

        // update request number
        $user = User::findOrFail($user_id);

        $user->request_number = followRequest::where('following_id',$user_id)->count();
        $user->save();

        return back();
    }

    public function delete_follow_request($user_id){ 
        // delete follow request
        $find_follow_user = followRequest::where('follower_id',auth::id())->where('following_id', $user_id);

        $find_follow_user->delete();

        // delete notification request
        $find_notification = notifications::where('UID' ,$user_id)->where('from', Auth::id())->where('type', 'follow_request');

        $find_notification->delete();
    }
    public function unfollow($user_id){ 

        $find_follow_user = follow_model::where('follower_id',auth::id())->where('following_id', $user_id);

        $find_follow_user->delete();

        // update follow number
        $user_signin = User::findOrFail(auth::id());
        $user = User::findOrFail($user_id);

        $user_signin->following_number = follow_model::where('follower_id',auth::id())->count();
        $user_signin->save();

        $user->followers_number = follow_model::where('following_id',$user_id)->count();
        $user->save();

        return back();
    }
    
    public function render()
    {
        if($this->user_id == auth()->id()){
            $this->state = 1;
        }elseif(follow_model::where('follower_id',auth::id())->where('following_id', $this->user_id)->exists()){
            $this->state = 2;
        }elseif(followRequest::where('follower_id',auth::id())->where('following_id', $this->user_id)->exists()){
            $this->state = 3;
        }elseif(User::where('id', $this->user_id)->first()->privacy == 'private'){
            $this->state = 4;
        }else{
            $this->state = 0;
        }

        $qr_code = QR::size(200)->generate('https://social.thezoom.ir/user/'.auth::user()['user_name']);

        // $user = User::where('user_name', $this->user['user_name'])->first();
        return view('livewire.profile.follow', [
            // 'user' => $user,
            'state' => $this->state,
            'qr_code' => $qr_code,
        ]);
    }
}
