<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\comments;
use App\Models\likeComment;
use Illuminate\Support\Facades\Auth;

class AddComments extends Component
{

    public $comment;

    public $postId;

    public $post;

    public $post_comments;

    public $single_comment;

    public $error;

    public $show_load_more = true;

    public $amount = 5;

    public $comment_number = 0;

    public function save($postId){
        $input = [
            'UID' => Auth::id(),
            'post_id' => $postId,
            'comment_value' => $this->comment,
            'like' => '0',
            'like_number' => '0',
            'user_profile' => Auth::user()->profile_pic ,
            'user_name' => Auth::user()->user_name 
        ];

        if($input['comment_value'] != null){
            Comments::create($input);
        }

        $this->comment = '';
    }

    public function loadMore(){
        
        $this->amount += 5;

    }

    public function like($single_comment)
    {
        if(!likeComment::where('UID',auth::id())->where('comment_id', $single_comment['id'])->exists())
        {
            $check = likeComment::create([
                'UID' => auth::id(),
                'comment_id' => $single_comment['id'],
                'user_comment_id' => $single_comment['UID']
                // 'type'=> 'like',
            ]);

            if(!$check){
                // error
            }
        }

    }

    public function dislike($single_comment)
    {
        $id = $single_comment['id'];

        $find_like_post = likeComment::where('UID',auth::id())->where('comment_id', $id);

        $check = $find_like_post->delete();

        if(!$check){
            // error
        }
    }
    public function render()
    {
        // load more
        $this->comment_number = count(comments::latest()->where('post_id', $this->postId)->get());

        if($this->amount >= $this->comment_number){
            $this->show_load_more = false;
        }

        $this->post_comments = comments::latest()->where('post_id', $this->postId)->limit($this->amount)->get();

        // check liked
        foreach($this->post_comments as $single_comment){
            if(likeComment::where('UID',auth::id())->where('comment_id', $single_comment['id'])->exists()){
                $single_comment['liked'] = true;
            }else{
                $single_comment['liked'] = false;
            }

            $single_comment['like_number'] = likeComment::where('comment_id', $single_comment['id'])->count();
        }

        return view('livewire.add-comments',[
            'post_comments' => $this->post_comments,
        ]);
    }
}
