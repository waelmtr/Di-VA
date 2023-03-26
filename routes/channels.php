<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\UserFollow;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});



Broadcast::channel('App.Models.User.{id}.Post', function ($user, $id) {

    $users = UserFollow::all();
    for ($i = 0; $i < count($users); $i++) {
        if ($users[$i]->user_id == $id)
            $guest = $users[$i];
    }
    return in_array($user->id, $guest->followers_id);
});

/*  function ($user) {

        $users = UserFollow::all();
        for ($i = 0; $i < count($users); $i++) {
            if ($users[$i]->user_id == auth()->id())
                $usernow = $users[$i];
        }
        return in_array($user->id, $usernow->followers_id);
    }*/
Broadcast::channel('Post.{id}', function ($user) {
});