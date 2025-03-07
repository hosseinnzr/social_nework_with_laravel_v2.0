<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Auth;
use App\Models\Post;
use App\Models\likePost as like_post;
use Livewire\Component;

class LikePost extends Component
{
    public $post;

    public $liked;
    public function like($post)
    {
        if(!like_post::where('UID',auth::id())->where('post_id', $post['id'])->exists())
        {
            like_post::create([
                'UID' => auth::id(),
                'post_id' => $post['id'],
                // 'type'=> 'like',
                'user_post_id' => $post['UID'],
            ]);
        }
    }


    public function dislike($post){

        $find_like_post = like_post::where('UID',auth::id())->where('post_id', $post['id']);

        $find_like_post->delete();
    }


    public function render()
    {        
        if(like_post::where('UID',auth::id())->where('post_id', $this->post['id'])->exists()){
            $this->liked = 1;
        }else{
            $this->liked = 0;
        }

        $post = Post::findOrFail($this->post['id']);

        // update like number
        $post->like_number = like_post::where('UID',auth::id())->where('post_id', $this->post['id'])->count();
        $post->save();

        return view('livewire.like-post');
    }
}
