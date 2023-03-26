<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Post;
use App\Models\User;
use App\Events\CommentEvent;
use App\Traits\TraitNotify;
use App\Notifications\CommentsNotification;
use Str;

class CommentController extends Controller
{
    use TraitNotify;
    public function createComment(Request $request, $postid)
    {


        $user = auth()->id();
        $user_name = auth()->user()->name;
        $postt =  Post::find($postid);
        $user_post_id = $postt->user_id;
        $user_post = User::find($user_post_id);
        $UserInfo['Name'] = $user_name;
        $UserInfo['Photo'] = User::find($user)->photo;

        $arrcomments = Post::find($postid)->comments;
        if ($arrcomments === null)
            $arrcomments = [];
        $comment_id = \random_int(1, 10000000000);

        array_push($arrcomments, [
            "id" => $comment_id,
            "content" => $request->input('comment'),
            "user_id" => $user,
            "created_at" => now()->format('Y-m-d H:i:s')
        ]);

        $post =  Post::find($postid)->update([
            'comments' => $arrcomments
        ]);
        $comments = Post::find($postid)->comments;

        for ($i = 0; $i < count($comments); $i++) {
            if ($comments[$i]['id'] === $comment_id) {
                $comment = $comments[$i];
            }
        }
        $user_comment['User'] = $UserInfo;
        $user_comment['User']['Comment'] = $comment;

        $this->Notifications($user_post->FCM, "{ $user_name } comment on your post.", null, $postid);
        $user_post->notify(new CommentsNotification($postt));
        event(new CommentEvent($user_comment, $postid));

        return  $user_comment;
    }


    public function delete($postid, $id)
    {
        $comments = Post::find($postid)->comments;
        for ($i = 0; $i < count($comments); $i++) {
            if ($comments[$i]['id'] == $id) {
                unset($comments[$i]);
                $comments = array_merge($comments);
            }
        }

        Post::find($postid)->update([
            'comments' => $comments
        ]);

        return $comments;
    }


    public function update(Request $request)
    {
        $post = Post::find($request->post_id);
        $all_comment = $post['comments'];
        for ($i = 0; $i < count($post['comments']); $i++) {
            if ($post['comments'][$i]['id'] == $request->id) {
                $comment = $post['comments'][$i];
                $comment['content'] = $request->content;
                $all_comment[$i] = $comment;
                $post['comments'] = $all_comment;
                $post->update();
                return $post['comments'][$i];
            }
        }
    }
}