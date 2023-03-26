<?php

namespace App\Http\Controllers;

use App\Models\UserFollow;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Notifications\FollowNotification;
use App\Traits\TraitNotify;

class UserFollowController extends Controller
{
    use TraitNotify;
    public function getuser($id)
    {
        $users = UserFollow::all();

        for ($i = 0; $i < count($users); $i++) {
            if ($users[$i]['user_id'] == $id) {

                return  $users[$i];
            }
        }
    }
    public function follower($id)
    {

        $user = $this->getUser($id);
        if ($user === null) {
            return [];
        }
        return $user->followers_id;
    }


    public function following($id)
    {

        $user = $this->getUser($id);
        if ($user === null) {
            return [];
        }
        return $user->following_id;
    }


    public function follow($id)
    {
        $user = auth()->id();
        $me = $this->getUser($user);
        $you = $this->getUser($id);

        $following_id = $me->following_id;
        $followers_id = $you->followers_id;

        if ($following_id === null)
            $following_id = [];
        if ($followers_id === null)
            $followers_id = [];

        if (in_array($user, $followers_id)) {

            $indexme = array_search($user, $followers_id);
            $indexyou = array_search($id, $following_id);

            unset($followers_id[$indexme]);
            unset($following_id[$indexyou]);

            $following_id = array_merge($following_id);
            $followers_id = array_merge($followers_id);

            $me->following_id = $following_id;
            $you->followers_id = $followers_id;

            $me->update();
            $you->update();
            $user_name = User::find($id)->name;
            return response()->json("You unfollowed {$user_name}");
        } else {

            array_push($followers_id, $user);
            array_push($following_id, $id);

            $following_id = array_merge($following_id);
            $followers_id = array_merge($followers_id);

            $me->following_id = $following_id;
            $you->followers_id = $followers_id;

            $me->update();
            $you->update();
            $user_name = User::find($id)->name;


            $user_not = User::find($id);
            $this->Notifications($user_not->FCM, "{$user_name} started following you .!", null, null);

            //For Notifications API
            $user_noty = User::find($id);
            $user_noty->notify(new FollowNotification());

            return response()->json("You follow {$user_name}");
        }
    }

    public function block($id)
    {
        $me_id = Auth::id();
        $me = $this->getuser($me_id);
        $blocking_id = $me->blocking_id;
        $user_name =   User::find($id)->name;
        if ($blocking_id == null)
            $blocking_id = [];

        if (in_array($id, $blocking_id)) {
            $index = array_search($id, $blocking_id);
            unset($blocking_id[$index]);

            $blocking_id = array_merge($blocking_id);
            $me->blocking_id = $blocking_id;
            $me->update();

            return response()->json("You unblocked {$user_name} ");
        } else {
            $following_ids = $me->following_id;
            if ($following_ids != null)
                if (in_array($id, $following_ids)) {

                    $index_user = array_search($id, $following_ids);
                    unset($following_ids[$index_user]);
                    $following_ids = array_merge($following_ids);

                    $me->following_id = $following_ids;
                    $me->update();


                    $you = $this->getuser($id);
                    $followers_id = $you->followers_id;
                    $index_user = array_search($me_id, $followers_id);
                    unset($followers_id[$index_user]);
                    $followers_id = array_merge($followers_id);
                    $you->followers_id = $followers_id;
                    $you->update();
                }

            array_push($blocking_id, $id);
            $me->blocking_id = $blocking_id;
            $me->update();

            return response()->json("You blocked {$user_name}");
        }
    }
}
