<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Broadcast;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\StoryController;
use App\Http\Controllers\UserFollowController;
use App\Http\Controllers\VotesController;

use App\Models\User;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});







/* Route Sing (in / up) */
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/checkemail', [AuthController::class, 'checkemail']);
Route::post('/chkcode', [AuthController::class, 'chkcode']);
Route::post('/resetpassword', [AuthController::class, 'resetpassword']);

Route::post('mtn/code', [AuthController::class, 'mtn']);

Route::prefix('myprofile')->group(
    function () {
        Route::group(['middleware' => ['auth:sanctum']], function () {
            Route::post('/makefollowpage', [AuthController::class, 'makefollowpage']);
            Route::post('/updateinterest', [AuthController::class, 'updateinterest']);
            Route::post('/photo', [AuthController::class, 'photo']);
            Route::post('uploadImage', [AuthController::class, 'uploadImage']);
            Route::post('/promotion', [AuthController::class, 'promotion']);
            Route::put('updateinfo', [AuthController::class, 'update']);
            Route::put('/changepassword', [AuthController::class, 'changePassword']);
            Route::get('/{id}', [AuthController::class, 'myprofile']);
            Route::post('/logout', [AuthController::class, 'logout']);
            Route::delete('/delete', [AuthController::class, 'destroy']);
        });
    }
);
//Broadcast::routes(['middleware' => ['auth:api']]);

Broadcast::routes(['middleware' => ['auth:sanctum']]);

Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::get('/notifications', [AuthController::class, 'notifications']);
    Route::get('/notifications/markAsRead', [AuthController::class, 'notificationsMakeAsRead']);
    Route::post('/notifications/{id}', [AuthController::class, 'notificationAsread']);
});


Route::group(
    ['middleware' => ['auth:sanctum']],
    function () {
        Route::post('/follow/{id}', [UserFollowController::class, 'follow']);

        Route::get('/followers/{id}', function ($id) {
            $followers_id = (new UserFollowController)->follower($id);
            $Users[] = null;
            if ($followers_id === null)
                return [];

            for ($i = 0; $i < count($followers_id); $i++) {
                $user = User::find($followers_id[$i]);
                $Users[$i]['id'] = $followers_id[$i];
                $Users[$i]['Name'] = $user->name;
                $Users[$i]['Photo'] = $user->photo;
            }
            return $Users;
        });


        Route::get('/following/{id}', function ($id) {
            $following_id = (new UserFollowController)->following($id);
            $Users[] = null;
            if ($following_id === null)
                return [];

            for ($i = 0; $i < count($following_id); $i++) {
                $user = User::find($following_id[$i]);
                $Users[$i]['id'] = $following_id[$i];
                $Users[$i]['Name'] = $user->name;
                $Users[$i]['Photo'] = $user->photo;
            }
            return $Users;
        });

        Route::post('/block/{id}', [UserFollowController::class, 'block']);

        Route::get('/blocking', function () {
            $user_page = (new UserFollowController)->getUser(auth()->id());
            $Users[] = null;
            if ($user_page->blocking_id === null) {
                return [];
            }
            for ($i = 0; $i < count($user_page->blocking_id); $i++) {
                $user = User::find($user_page->blocking_id[$i]);

                $Users[$i]['id'] = $user_page->blocking_id[$i];
                $Users[$i]['Name'] = $user->name;
                $Users[$i]['Photo'] = $user->photo;
            }
            return $Users;
        });
    }
);

//For stories
Route::prefix('story')->group(
    function () {
        Route::group(['middleware' => ['auth:sanctum']], function () {

            Route::post('/create', [StoryController::class, 'createStory']);
            Route::delete('/delete/{id}', [StoryController::class, 'delete']);
        });
    }
);
//For Search
Route::prefix('search')->group(
    function () {
        Route::group(
            ['middleware' => ['auth:sanctum']],
            function () {
                Route::get('/interest/{id}', [PostController::class, 'search']);
                Route::get('/user/{user_name}', [AuthController::class, 'search']);
            }
        );
    }
);

Route::prefix('post')->group(
    function () {
        Route::group(['middleware' => ['auth:sanctum']], function () {
            //For Posts
            Route::post('/creat', [PostController::class, 'store']);
            Route::post('/{id}/updatephoto', [PostController::class, 'updatephoto']);
            Route::get('/home', [PostController::class, 'home']);
            Route::get('/show/{id}', [PostController::class, 'show']);
            Route::put('/{id}', [PostController::class, 'update']);
            Route::get('/explore', [PostController::class, 'explore']);
            Route::delete('/{id}', [PostController::class, 'destroy']);

            //For Comments
            Route::prefix("/{post_id}")->group(function () {
                Route::post('/comment', [CommentController::class, 'createComment']);
                Route::delete('/comment/{id}/delete', [CommentController::class, 'delete']);
                Route::put('/comment/{id}/update', [CommentController::class, 'update']);
            });

            //For Votes
            Route::prefix("/{post_id}")->group(function () {
                Route::post('/upvote', [VotesController::class, 'upvote']);
                Route::post('/downvote', [VotesController::class, 'downvote']);
            });
        });
    }
);