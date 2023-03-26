<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class FollowNotification extends Notification implements ShouldQueue, ShouldBroadcast
{
    use Queueable;



    public function __construct()
    {
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

        ];
    }
    public function toDatabase($notifiable)
    {

        return [
            'message' => $this->message(),
            'user_id' => auth()->user()->id,
            'user_photo' => auth()->user()->photo,

        ];
    }


    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([

            'message' => $this->message(),
            'user_id' => auth()->user()->id,
            'user_photo' => auth()->user()->photo,

        ]);
    }
    public function message()
    {
        return sprintf('%s followed you.', auth()->user()->name);
    }
}