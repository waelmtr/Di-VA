<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PostNotification extends Notification implements ShouldQueue, ShouldBroadcast
{
    use Queueable;

    protected $post;
    public function __construct($post)
    {
        $this->post = $post;
    }


    public function via($notifiable)
    {
        return ['database', 'broadcast'];
    }




    public function toArray($notifiable)
    {
        return [
            'message' => $this->message(),
            'user_id' => auth()->user()->id,
            'user_photo' => auth()->user()->photo,
            'post_id' => $this->post->id
        ];
    }


    public function toDatabase($notifiable)
    {

        return [
            'message' => $this->message(),
            'user_id' => auth()->user()->id,
            'user_photo' => auth()->user()->photo,
            'post_id' => $this->post->id

        ];
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([

            'message' => $this->message(),
            'user_id' => auth()->user()->id,
            'user_photo' => auth()->user()->photo,
            'post_id' => $this->post->id



        ]);
    }
    public function message()
    {

        return sprintf('%s Publish a post, let\' see .!', auth()->user()->name);
    }
}
