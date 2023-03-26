<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\User;
use App\Models\Story;
use App\Models\UserInterestId;
use App\Models\UserFollow;
use App\Traits\TraitPhoto;
use App\Traits\TraitNotify;
use App\Events\PostEvent;
use App\Notifications\PostNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Route;
use Carbon\Carbon;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function explore()
    {
        return response()->json(Post::orderBy('is_prometed', 'created_at')->get());
    }

    public function updatephoto(Request $request)
    {
        $post = Post::find($request->id);
        $file_name = $this->saveImage($request->photo, 'images/PostsPhoto');
        $post->photo = $file_name;
        $post->save();
        return response()->json($post);
    }

    public function show($id)
    {
        $post = Post::find($id);
        $user_id = $post['user_id'];

        $UserInfo['Name'] = User::find($user_id)->name;
        $UserInfo['Photo'] = User::find($user_id)->photo;

        if ($post['comments'] != null)

            for ($p = 0; $p < count(($post['comments'])); $p++) {

                $CommentInfo[$p]['Comment'] = $post['comments'][$p];
                $UserCommentInfo['Name'] = User::find($post['comments'][$p]['user_id'])->name;
                $UserCommentInfo['Photo'] = User::find($post['comments'][$p]['user_id'])->photo;
                $CommentInfo[$p]['Comment']['User'] = $UserCommentInfo;
            }
        else
            $CommentInfo = [];

        if ($post->upvotes_user_id === null) {
            $post->upvotes_user_id = [];
            $post->save();
        }
        if ($post->downvotes_user_id === null) {
            $post->downvotes_user_id = [];
            $post->save();
        }

        if (in_array(auth()->id(), $post->upvotes_user_id)) {
            // $In_Post['Post']['Upvoted'] = "Yes";
            $react = "Upvoted";
        } else
            if (in_array(auth()->id(), $post->downvotes_user_id)) {
            // $In_Post['Post']['Downvoted'] = "Yes";
            $react = "Downvoted";
        } else
            $react = "No React";


        $post['comments'] = $CommentInfo;
        $In_Post['Post'] = $post;
        $In_Post['Post']['Comments_Number'] = $this->allcomments($id);
        $In_Post['Post']['UpVotes_Number'] = (new VotesController)->allupvotes($id);
        $In_Post['Post']['DownVotes_Number'] = (new VotesController)->alldownvotes($id);
        $In_Post['Post']['React'] = $react;
        $In_Post['Post']['User'] = $UserInfo;

        return response()->json($In_Post);
    }

    public function allcomments($id)
    {
        $post = Post::find($id);
        if ($post['comments'] != null)
            $number_comments = count($post['comments']);
        else
            $number_comments = 0;

        return response()->json($number_comments);
    }


    use TraitPhoto;
    use TraitNotify;
    public function store(Request $request)
    {

        $file_name = $this->saveImage($request->photo, 'images/PostsPhoto');
        $user = auth()->id();
        $us = User::find($user);
        if ($request->is_prometed != 0) {

            if ($us->number_of_posts > 0)
                $us->update([
                    'number_of_posts' => ($us->number_of_posts - 1)
                ]);
            else
                return response()->json('You no longer have permission to promote, your number of posts has expired .');
        }
        $interest_id[] = [];
        $j = 0;
        for ($i = 0; $i < Str::length($request->interest_id); $i++) {
            if ($request->interest_id[$i] === "," || $request->interest_id[$i] === "[" || $request->interest_id[$i] === "]")
                continue;
            $interest_id[$j] = $request->interest_id[$i];
            $j++;
        }

        $post = Post::create([

            'photo' => $file_name,
            'user_id' => $user,
            'interest_id' => $interest_id,

        ]);

        if ($request->has("content")) {
            $post->content = $request->content;
            $post->save();
        } else {
            $post->content = null;
            $post->save();
        }
        if ($request->has("is_prometed")) {
            $post->is_prometed = $request->is_prometed;
            $post->save();
        }
        //    event(new PostEvent($post));
        broadcast(new PostEvent($post))->toOthers();
        $AllUsers = UserFollow::all();
        for ($p = 0; $p < count($AllUsers); $p++) {
            if ($AllUsers[$p]->user_id == auth()->id()) {
                $MyPage = $AllUsers[$p];
            }
        }

        //For Puch Notification
        if ($MyPage->followers_id != null) {
            for ($z = 0; $z < count($MyPage->followers_id); $z++) {

                $user_Gu = User::find($MyPage['followers_id'][$z]);
                $Tokens[$z] = $user_Gu->FCM;
                $this->Notifications(
                    $Tokens[$z],
                    "Publish a post, let\' see .!",
                    "/images/PostPhoto/{$file_name}",
                    $post->id
                );

                $user_Gu->notify(new PostNotification($post));
            }
        }

        return response()->json([
            'post' => $post,
            'message' => 'Post added successfully'
        ]);
    }


    public function home()
    {

        //for checking story
        $stories = Story::all();
        $currentdate = Carbon::now();

        foreach ($stories as $story) {
            $created_at = Carbon::parse($story->created_at);
            // $created_at = $created_at->toDateTimeString();
            $ended_at = $created_at->addHours(24);
            if ($currentdate == $ended_at) {
                Story::destroy($story->id);
            }
        }
        $user = auth()->id();
        $allinterested = UserInterestId::all(); //gives you all user's interested
        $m = 0;
        $UserInterest = [];
        for ($y = 0; $y < count($allinterested); $y++) {
            if ($allinterested[$y]->user_id == auth()->id()) {
                $UserInterest[$m] = $allinterested[$y];
                $m++;
            }
        }
        $following_id = (new UserFollowController)->following($user);

        $post = Post::orderBy('is_prometed', 'created_at')->get();

        $getpost1[] = null; //interst
        $getpost2[] = null; //following
        $getinterest[] = null;
        $all_stories[] = null;
        $test = [];
        $tr = 0;
        for ($i = 0; $i < count($post); $i++) {
            $tw = $post[$i]['interest_id'];
            $r = 0;

            for ($l = 0; $l < count($tw); $l++) {
                if ($tw[$l] === "[" || $tw[$l] === "]" || $tw[$l] === "," || $tw[$l] === null)
                    continue;

                else {

                    $getinterest[$r] = $tw[$l];


                    //   $arrayInterest = array_keys($allinterested->toArray());
                    for ($j = 0; $j < count($UserInterest); $j++) {
                        if ($getinterest[$r] == $UserInterest[$j]->interest_id) {

                            $user_id = $post[$i]['user_id'];
                            $user = User::find($user_id);

                            $userpage = (new UserFollowController)->getuser($user_id);
                            $mypage = (new UserFollowController)->getuser(auth()->id());
                            if ($mypage['blocking_id'] != null)
                                if (in_array($user_id, $mypage['blocking_id']) || in_array(auth()->id(), $userpage['blocking_id']))
                                    continue;

                            $UserInfo['Name'] = $user->name;
                            $UserInfo['Photo'] = $user->photo;

                            // $UserInfo['Rest_Number_of_post'] = User::find($user_id)->number_of_posts;


                            if ($post[$i]->upvotes_user_id == null) {
                                $post[$i]->upvotes_user_id = [];
                                $post[$i]->save();
                            }
                            if ($post[$i]->downvotes_user_id == null) {
                                $post[$i]->downvotes_user_id = [];
                                $post[$i]->save();
                            }

                            if (in_array(auth()->id(), $post[$i]->upvotes_user_id)) {
                                $react = "Upvoted";
                            } else
                            if (in_array(auth()->id(), $post[$i]->downvotes_user_id)) {
                                $react = "Downvoted";
                            } else
                                $react = "No React";

                            $getpost1[$i]['Post'] = $post[$i];
                            $getpost1[$i]['Post']['User'] = $UserInfo;
                            $getpost1[$i]['Post']['Comments_Number'] = $this->allcomments($post[$i]['id']);
                            $getpost1[$i]['Post']['UpVotes_Number'] = (new VotesController)->allupvotes($post[$i]['id']);
                            $getpost1[$i]['Post']['DownVotes_Number'] = (new VotesController)->alldownvotes($post[$i]['id']);
                            $getpost1[$i]['Post']['React'] = $react;



                            $test[$tr] = $getpost1[$i];
                            $tr++;
                        }
                    }
                }
            }
        }


        if ($following_id === null)
            $following_id = [];
        for ($k = 0; $k < count($following_id); $k++) {

            $user_id = $following_id[$k];

            $UserInfo['Name'] = User::find($user_id)->name;
            $UserInfo['Photo'] = User::find($user_id)->photo;
            //$UserInfo['Rest_Number_of_post'] = User::find($user_id)->number_of_posts;

            $getpost2[$k]['User'] = $UserInfo;
            $getpost2[$k]['User']['Post'] = (new AuthController)->myposts($following_id[$k]);
        }

        $all_user = User::all();
        $stories = Story::all();
        if ($stories != "[]") {
            for ($s = 0; $s < count($all_user); $s++) {
                $user_story = [];

                for ($st = 0; $st < count($stories); $st++) {

                    if ($stories[$st]->user_id == $all_user[$s]->id)
                        array_push($user_story, $stories[$st]);
                }
                $UserInfo['Name'] = $all_user[$s]->name;
                $UserInfo['Photo'] = $all_user[$s]->photo;

                if ($s == 0)
                    $all_stories[0]['User']['Story'] = [];
                else {
                    $all_stories[$s]['User'] = $UserInfo;
                    $all_stories[$s]['User']['Story'] = $user_story;
                }
            }
        }
        $home['Rest_Number_of_post'] = User::find(auth()->id())->number_of_posts;
        $home['Posts']['Interest'] = $test; //$getpost1;
        $home['Posts']['Following'] = $getpost2;
        $home['Stories'] = $all_stories;
        return response()->json($home);
    }



    public function update(Request $request, $id)
    {
        $post = Post::find($id);
        if (($post->is_prometed === true) && ($request->is_prometed === false)) {
            return response()->json('You can\'t update this');
        }
        if (Auth::id() != $post->user_id) {
            return response()->json('Not allowed to update post');
        }
        if ($request->has('content')) {
            $post->content = $request->content;
        }
        if ($request->has('interest_id')) {
            $post->interest_id = $request->interest_id;
        }
        $post->update();

        return response()->json([
            'post' => $post,
            'message' => 'Your post has been updated'
        ]);
    }


    public function search($id)
    {
        $search = $id;
        $arrsearch = [];
        $posts = Post::all();
        foreach ($posts as $post) {

            if (in_array($search, $post->interest_id)) {
                $post_sh = $this->show($post->id);
                array_push($arrsearch, $post_sh);
            }
        }
        return response()->json($arrsearch);
    }


    public function destroy($id)
    {
        $post = (Post::find($id));

        if (auth()->id() === $post->user_id) { {
                Post::find($id)->delete();
                return response()->json('Post deleted successfully');
            }
        } else
            return response()->json('You can\'t delete this post');
    }
}
