<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Story;
use App\Models\User;
use Auth;

class StoryController extends Controller
{
    public function createStory(Request $request)
    {
        $request->validate([
            'photo' => "required |mimes:mp4,jpg|max:9000000"
        ]);

        if ($request->hasFile('photo')) {

            $photo = $request->file('photo');
            $filename = $photo->getClientOriginalName();
            $extension = pathinfo($filename, PATHINFO_EXTENSION);

            $user = User::find(auth()->id());
            if ($extension === 'mp4') {
                $path = public_path() . '/images/Story/videos/';
                $photo->move($path, $filename);
                $story = Story::create([
                    'photo' => $filename,
                    'user_id' => auth()->id(),
                    'user_name' => $user->name,
                    'user_photo' => $user->photo,
                    'date_type' => $request->date_type

                ]);
                return response()->json($extension);
            } else {
                $path = public_path() . '/images/Story/images/';
                $photo->move($path, $filename);

                $story = Story::create([
                    'photo' => $filename,
                    'user_id' => auth()->id(),
                    'user_name' => $user->name,
                    'user_photo' => $user->photo,
                    'date_type' => $request->date_type
                ]);
                return response()->json('Your story has been published');
            }
        }
    }

    public function delete($id)
    {
        $photo = Story::find($id)->photo;
        $ext =  pathinfo($photo, PATHINFO_EXTENSION);

        if ($ext === 'mp4') {
            unlink(public_path('/images/Story/videos/' . $photo));
            Story::destroy($id);
            return response()->json('Story deleted');
        }
        //
        else {
            unlink(public_path('/images/Story/images/' . $photo));
            Story::destroy($id);
            return response()->json('Story deleted');
        }
    }
}
