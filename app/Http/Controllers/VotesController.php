<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;
use App\Models\User;
use App\Notifications\VotesNotification;
use Illuminate\Support\Facades\Auth;
use App\Traits\TraitNotify;

class VotesController extends Controller
{
    use TraitNotify;
    public function upvote($post_id)
    {


        $id = auth()->id();
        $post = Post::find($post_id);
        $user_post_id = $post->user_id;
        $user_post = User::find($user_post_id);
        $upvotes = Post::find($post_id)->upvotes_user_id;
        $downvotes = Post::find($post_id)->downvotes_user_id;

        if ($upvotes == null)
            $upvotes = [];
        if ($downvotes == null)
            $downvotes = [];

        if (in_array($id, $upvotes)) {


            $index = array_search($id, $upvotes);
            unset($upvotes[$index]);
            $upvotes = array_merge($upvotes);

            $post  = Post::find($post_id)->update([
                'upvotes_user_id' => $upvotes
            ]);

            return response()->json(0);
        } else {
            if (in_array($id, $downvotes)) {
                $in = array_search($id, $downvotes);
                unset($downvotes[$in]);
                $downvotes = array_merge($downvotes);
                $post  = Post::find($post_id)->update([
                    'downvotes_user_id' => $downvotes
                ]);
            }


            array_push($upvotes, $id);
            $post  = Post::find($post_id)->update([
                'upvotes_user_id' => $upvotes
            ]);

            $this->Notifications($user_post->FCM, "react on your post", null, $post_id);
            $user_post->notfy(new VotesNotification($post));
            return response()->json(1);
        }
    }

    public function downvote($post_id)
    {

        $id = auth()->id();
        $post = Post::find($post_id);
        $user_post_id = $post->user_id;
        $user_post = User::find($user_post_id);
        $downvotes = Post::find($post_id)->downvotes_user_id;
        $upvotes = Post::find($post_id)->upvotes_user_id;

        if ($downvotes == null)
            $downvotes = [];
        if ($upvotes == null)
            $upvotes = [];

        if (in_array($id, $downvotes)) {


            $index = array_search($id, $downvotes);
            unset($downvotes[$index]);
            $downvotes = array_merge($downvotes);

            $post  = Post::find($post_id)->update([
                'downvotes_user_id' => $downvotes
            ]);
            return response()->json(0);
        } else {

            if (in_array($id, $upvotes)) {
                $in = array_search($id, $upvotes);
                unset($upvotes[$in]);
                $upvotes = array_merge($upvotes);
                $post  = Post::find($post_id)->update([
                    'upvotes_user_id' => $upvotes
                ]);
            }

            array_push($downvotes, $id);

            $post  = Post::find($post_id)->update([
                'downvotes_user_id' => $downvotes
            ]);
            $user_post->notfy(new VotesNotification($post));
            $this->Notifications($user_post->FCM, "react on your post", null, $post_id);
            return response()->json(1);
        }
    }

    public function allupvotes($id)
    {
        $post = Post::find($id);
        $upvotes = $post['upvotes_user_id'];
        if ($upvotes != null)
            $upvotes_number = count($upvotes);
        else
            $upvotes_number = 0;

        return $upvotes_number;
    }

    public function alldownvotes($id)
    {
        $post = Post::find($id);
        $downvotes = $post['downvotes_user_id'];
        if ($downvotes != null)
            $downvotes_number = count($downvotes);
        else
            $downvotes_number = 0;

        return $downvotes_number;
    }
}