<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Traits\TraitPhoto;
use App\Models\User;
use App\Models\UserFollow;
use App\Models\Post;
use App\Models\UserInterestId;
use App\Models\ResetPassword;
use App\Models\Story;
use App\Mail\Reset;
use Carbon\Carbon;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class AuthController extends Controller
{

    public function register(Request $request)
    {
        $fields = $request->validate([

            'name' => 'required|string',
            'email' => 'required|string|unique:users,email',
            'password' => 'required|string|confirmed',
            'gender' => 'required',
            'birthday' => 'required',

        ]);
        $user = User::create([
            'name' => $fields['name'],
            'email' => $fields['email'],
            'password' => bcrypt($fields['password']),
            'gender' => $fields['gender'],
            'birthday' => $fields['birthday'],
            'FCM' => $request->FCM,

        ]);
        if ($request->has('number_of_posts'))
            $user->create([
                'number_of_posts' => $request->number_of_posts,
            ]);
        $token = $user->createToken('Joudy-H-Taleb')->plainTextToken;
        $user->remember_token = $token;
        $user->save();
        $response = [
            'user' => $user,
            'token' => $token
        ];

        return response($response, 201);
    }

    public function emailpromot(Request $request){
       $email = $request->email;
       $mtnemail = User::where('email' , $email)->first();
       if($mtnemail){
        return view('payment');
       }
       else {
        return "the email is invalid";
       }
    }

    public function mtn(Request $request)
    {
      $feild = $request->validate([
            'email'=>'required' ,
            'pay'=>'required'
      ]);
       $user = User::where('email' , $feild['email'])->first();
       if($user){
        $code = \random_int(10000 , 99999);
        $user->code = $code;
        $user->pay = $feild['pay'];
        $user->save();
        return view('code' , ['code'=>$code]);
       }
       else {
        return "this email is not exists";
       }
    }

    public function checkcode(Request $request)
    {
        $user = User::find(auth()->id());
        if ($user->code == $request->code)
            return true;
    }

    public function promotion(Request $request)
    {
        if ($this->checkcode($request)) {
            $user = User::find(auth()->id());
            if(($request->number_of_posts*5)==$user->pay){
                $user->number_of_posts = $request->number_of_posts;
                $user->is_promtion = 1;
                $user->update();
                return response()->json('You are now a promoter!, Welcome'); //for Customer
            }
            else{
                return response()->json('your pay does not allow you to choos this number of posts');
            }
          
        }
         else
            return response()->json('Your code is invalid , Please check your code');
    }

    use TraitPhoto;
    public function photo(Request $request)
    {
        $file_name = $this->saveImage($request->photo, 'images/UsersPhoto');

        $user = User::find(auth()->id());
        $user->photo = $file_name;
        $user->save();
        return response()->json('Your photo has been added');
    }

    public function uploadImage(Request $request)
    {

        $file_name = $this->saveImage($request->file('photo'), 'images/UsersPhoto');
        $user = User::find(auth()->id());
        $user->photo = $file_name;
        $user->update();
        return response()->json('Your photo has been uploaded ');
    }

    public function changePassword(Request $request)
    {

        $user = User::find(auth()->id());
        $request->validate([
            'old_password' => 'required',
            'new_password' => 'required',
        ]);

        if (!Hash::check($request->old_password, $user->password)) {
            return response()->json('Your passowrd is not match.');
        } else {

            $user->password = bcrypt($request->new_password);
            $user->save();
            return response()->json('Your passowrd has been changed.');
        }
    }

    public function update(Request $request)
    {

        $user = User::find(auth()->id());
        $user->name = $request->name;
        $user->birthday = $request->birthday;
        $user->gender = $request->gender;
        $user->bio = $request->bio;
        $user->save();
        return response()->json('Your Info has been updated.');
    }

    public function login(Request $request)
    {
        $fields = $request->validate([
            'email' => 'required|string',
            'password' => 'required|string'
        ]);

        // Check email
        $user = User::where('email', $fields['email'])->first();

        // Check password
        if (!$user || !Hash::check($fields['password'], $user->password)) {
            return response([
                'message' => 'Your Email or Password is incorrect'
            ], 401);
        }

        $token = $user->createToken('Joudy-H-Taleb')->plainTextToken;
        $user->remember_token = $token;
        $user->FCM = $request->FCM;
        $user->update();
        $response = [
            'user' => $user,
            'token' => $token
        ];

        return response($response, 201);
    }
    public function updateinterest(Request $request)
    {
        $user_intreset = UserInterestId::all();
        $user_in=[];
for($u=0;$u<count($user_intreset);$u++){
    if($user_intreset[$u]['user_id']==auth()->id())
    $user_in[$u]=$user_intreset[$u];
}


for ($i = 0; $i < count($user_in); $i++) {
            UserInterestId::destroy( $user_in[$i]['id']);
        
        }

        for ($i = 0; $i < Str::length($request->interest_id); $i++) {
            if ($request->interest_id[$i] === "[" || $request->interest_id[$i] === "]"  || $request->interest_id[$i] === "{" || $request->interest_id[$i] === "}" || $request->interest_id[$i] === ",")
                continue;
            UserInterestId::create([
                'interest_id' => $request->interest_id[$i],
                'user_id' => Auth::id()
            ]);
        }
        return response()->json('Your Interest have been updated');
    }
    public function makefollowpage(Request $request)
    {
        $user = UserFollow::create([
            'user_id' => auth()->id(),
            'following_id' => null,
            'followers_id' => null,
            'blocking_id' => null

        ]);

        for ($i = 0; $i < Str::length($request->interest_id); $i++) {
            if (
                $request->interest_id[$i] === "[" || $request->interest_id[$i] === "]" ||
                $request->interest_id[$i] === "{" || $request->interest_id[$i] === "}" ||
                $request->interest_id[$i] === "," || $request->interest_id[$i] === " "
            )
                continue;
            UserInterestId::create([
                'interest_id' => $request->interest_id[$i],
                'user_id' => Auth::id()
            ]);
        }

        return response()->json('Welcome in Di-Va');
    }
    public function search($user_name)
    {
        $user_search = [];
        $id = auth()->id();
        $search = $user_name;
        $users = User::where('name', 'like', '%' . $search . '%')->get();
        $me = (new UserFollowController)->getuser($id);


        if ($me->blocking_id != null)
            $blocking = $me->blocking_id;
        else
            $blocking = [];



        foreach ($users as $user) {
            $you = (new UserFollowController)->getuser($user->id);
            if ($you->blocking_id != null)
                $blocked_me =  $you->blocking_id;
            else
                $blocked_me = [];

            if (in_array($user->id, $blocking) || in_array($id, $blocked_me))
                continue;

            else {
                $User_Info["Id"] = $user->id;
                $User_Info["Name"] = $user->name;
                $User_Info["Photo"] = $user->photo;
                array_push($user_search, $User_Info);
            }
        }
        return $user_search;
    }
    public  function myprofile($id)
    {

        $following_id = (new UserFollowController)->following($id);
        $followers_id = (new UserFollowController)->follower($id);
        $story = [];
        $all_story = Story::all();
        if ($all_story != "[]") {

            for ($st = 0; $st < count($all_story); $st++) {

                if ($all_story[$st]->user_id == $id)
                    array_push($story, $all_story[$st]);
            }
        } else
            $data['My Info']['Story'] = [];



        $data['My Info']['Personal'] = User::find($id);
        $Allinterest = UserInterestId::all(); //->where('user_id', $id);
        $r = 0;
        for ($y = 0; $y < count($Allinterest); $y++) {
            if ($Allinterest[$y]->user_id == $id) {
                $data['My Info']['Interest'][$r] = $Allinterest[$y];
                $r++;
            }
        }
        if ($followers_id === null) {
            $data['My Info']['Followers'] = 0;
            $followers_id = [];
        } else {
            $data['My Info']['Followers'] = count($followers_id);
        }
        if ($following_id === null)
            $data['My Info']['Following'] = 0;
        else
            $data['My Info']['Following'] = count($following_id);
        $data['My Posts'] = $this->myposts($id);
        $data['My Info']['Stores'] = $story;

        if ((in_array(auth()->id(), $followers_id)) && (auth()->id() != $id))
            $data['My Info']['State'] = "Follow";
        else
            $data['My Info']['State'] = "UnFollow";

        return response()->json($data);
    }

    public function myposts($id)
    {
        $posts = Post::orderBy('is_prometed', 'created_at')->get();

        $myposts['Post'] = [];
        $j = 0;
        for ($i = 0; $i < count($posts); $i++) {
            if ($posts[$i]['user_id'] == $id) {
                $myposts['Post'][$j] = $posts[$i];


                if ($posts[$i]->upvotes_user_id == null) {
                    $posts[$i]->upvotes_user_id = [];
                    $posts[$i]->save();
                }
                if ($posts[$i]->downvotes_user_id == null) {
                    $posts[$i]->downvotes_user_id = [];
                    $posts[$i]->save();
                }

                if (in_array(auth()->id(), $posts[$i]->upvotes_user_id)) {
                    $react  = "Upvoted";
                } else
                if (in_array(auth()->id(), $posts[$i]->downvotes_user_id)) {
                    $react = "Downvoted";
                } else
                    $react = "No React";

                $myposts['Post'][$j]['Comments_Number'] = (new PostController)->allcomments($posts[$i]['id']);
                $myposts['Post'][$j]['UpVotes_Number'] = (new VotesController)->allupvotes($posts[$i]['id']);
                $myposts['Post'][$j]['DownVotes_Number'] = (new VotesController)->alldownvotes($posts[$i]['id']);
                $myposts['Post'][$j]['React'] = $react;
                $j++;
            }
        }
        return response()->json($myposts);
    }

    public function notifications()
    {

        $data['Notifications'] = auth()->user()->notifications;
        $data['UnRead'] = count(auth()->user()->unreadnotifications);
        return response()->json($data);
    }


    public function notificationsMakeAsRead()
    {
        return auth()->user()->notifications->markAsRead();
    }

    public function notificationAsread(Request $request, $id)
    {
        auth()->user()->notifications->where('id', $id)->markAsRead();

        if ($request->has('user'))
            return $this->myprofile($request->user_id);
        else
            if ($request->has('post'))
            return (new PostController)->show($request->post_id);
    }

    public function logout()
    {
        return [
            'message' => 'Logged out'
        ];
    }

    public function destroy(Request $request)
    {
        $user = User::find(auth()->id());
        if (!Hash::check($request->password, $user->password)) {
            return response()->json('Your passowrd is not match.');
        } else {
            $user->delete();
            $all_User = UserFollow::all();

            for ($t = 0; $t < count($all_User); $t++) {
                if ($all_User[$t]['user_id'] == auth()->id()) {
                    $all_User[$t]->delete();
                    continue;
                }
                if ($all_User[$t]['followers_id'] != null) {

                    $index1 = array_search(auth()->id(), $all_User[$t]['followers_id']);
                    $element = $all_User[$t]['followers_id'];

                    unset($element[$index1]);

                    $element = array_merge($element);

                    $user_page =   UserFollow::find($all_User[$t]['id']);
                    $user_page['followers_id']  = $element;
                    $user_page->save();
                }

                if ($all_User[$t]['following_id'] != null) {

                    $index2 = array_search(auth()->id(), $all_User[$t]['following_id']);
                    $element = $all_User[$t]['following_id'];
                    unset($element[$index2]);
                    $element = array_merge($element);

                    $user_page =   UserFollow::find($all_User[$t]['id']);
                    $user_page['following_id'] = $element;
                    $user_page->save();
                }

                if ($all_User[$t]['blocking_id'] != null) {

                    $index3 = array_search(auth()->id(), $all_User[$t]['blocking_id']);
                    $element = $all_User[$t]['blocking_id'];

                    unset($element[$index3]);
                    $element = array_merge($element);

                    $user_page =   UserFollow::find($all_User[$t]['id']);
                    $user_page['blocking_id']  = $element;
                    $user_page->save();
                }
            }


            $all_post = Post::all();
            for ($i = 0; $i < count($all_post); $i++) {
                if ($all_post[$i]['user_id'] == auth()->id()) {
                    $all_post[$i]->delete();
                    continue;
                }
                // $post =  Post::find($all_post[$i]['id']);
                // $post['comments'] =  null;
                // $post->save();
                if ($all_post[$i]['comments'] != null) {
                    $number = count($all_post[$i]['comments']);

                    for ($j = 0; $j < $number; $j++) {

                        if ($all_post[$i]['comments'][$j]['user_id'] == auth()->id()) {

                            $element[$i] = $all_post[$i]['comments'];


                            unset($element[$i][$j]);

                            $element_ex[$i] = array_merge($element[$i]);
                            $all_post[$i]['comments'] = $element[$i];
                        }

                        $post =  Post::find($all_post[$i]['id']);
                        $post['comments'] =  $element_ex[$i];
                        $post->save();
                    }
                }
            }
            return response()->json('Di-Va is sorry to lose you , hopes you enjoyed ');
        }
    }

    public function checkemail(Request $request)
    {
        $request->validate([
            'email' => 'required|email'
        ]);
        $em = ResetPassword::Where('email', $request->email)->first();
        if (!$em) {
            $email = User::where('email', $request->email)->first();

            if ($email) {
                $code = \random_int(10000, 99999);
                Mail::to($request->email)->send(new Reset($code));

                $reset = ResetPassword::create([
                    'email' => $email->email,
                    'code' => $code
                ]);

                return response()->json("We send code to your Email, check it !");
            } else {
                return "Your email is uncorrect";
            }
        } else {
            $code = \random_int(10000, 99999);
            Mail::to($request->email)->send(new Reset($code));
            $em->code = $code;
            $em->save();
            return response()->json("Code has been sent back to your email , check it back");
        }
    }

    public function chkcode(Request $request)
    {
        $request->validate([
            'code' => 'required'
        ]);

        $code = $request->input('code');
        $email = $request->input('email');

        $user = ResetPassword::where('code', $code)->first();

        if ($user->email === $email) {
            ResetPassword::destroy($user->id);
            return response()->json('Code is correct , you can change password');
        } else {
            return response()->json('Code is uncorrect');
        }
    }

    public function resetpassword(Request $request)
    {

        $request->validate([
            'newpassword' => 'required|string|confirmed'
        ]);

        $email = $request->input('email');
        $user = User::where('email', $email)->first();

        $user->update([
            'password' => bcrypt($request->input('newpassword'))
        ]);

        return response()->json('Your password has been changed');
    }
}