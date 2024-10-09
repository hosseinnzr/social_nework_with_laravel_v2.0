<?php

namespace App\Livewire\Settings;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\notifications;

class PrivacySettings extends Component
{
    public $isPrivate = false;

    public function togglePrivateAccount()
    {
        if(auth::user()->privacy == 'private'){
            $user_signin = User::where('id', auth::id())->first();
            $user_signin->update(['privacy' => 'public']);

            $user_signin_requests = explode(",", $user_signin->request_list);

            for( $i = 1; $i < count($user_signin_requests); $i++ ){
                $user_signin_request = $user_signin_requests[$i];
                $followers = $user_signin->followers . ',' . $user_signin_request;

                $user = User::where('id',  $user_signin_request)->first();
                $user->following = $user->following . "," . $user_signin->id;

                // update request list
                $new_request_list = array_diff($user_signin_requests, array($user_signin_request));

                // update following number
                if ($user->following == "0"){
                    $following_number = 1;
                }else{
                    $following_number = count(explode(",", $user->following));
                }

                // delete request notifiction
                $post = notifications::where('UID', $user_signin->id)->where('type', 'follow_request')->where('body', $user->user_name);
                $post->update(['delete' => 1]);

                $user->following_number = $following_number - 1;
                $user->save();
            }

            // update sign in user followers
            $user_signin->followers = $followers;

            // update sign in user follower number
            if ($user_signin->followers == "0"){
                $followers_number = 1;
            }else{
                $followers_number = count(explode(",", $user_signin->followers));
            }

            $user_signin->followers_number = $followers_number - 1;

            $user_signin->request_list = implode(",", $new_request_list);

            $user_signin->save();

        }else{
            $user_signin = User::where('id', auth::id());
            $user_signin->update(['privacy' => 'private']);
        }
    }

    public function render()
    {
        if(auth::user()->privacy == 'private'){
            $this->isPrivate = true;
        }
        return view('livewire.settings.privacy-settings');
    }
}
