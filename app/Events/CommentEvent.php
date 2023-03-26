<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Http\Request;
use App\Models\Post;
use App\Models\User;

class CommentEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    public $comment;
    public $post_id;
    public $user_comment;
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($user_comment, $id)
    {
        //  $this->comment = $comment;
        $this->user_comment['User'] = $user_comment['User'];
        $this->user_comment['User']['Comment'] = $user_comment['User']['Comment'];

        $this->post_id = $id;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        $post_id = $this->post_id;
        return new Channel('Post' . $post_id);
    }
    public function broadcastWith()
    {

        return [
            "user_id" => $this->user_comment['User']['Comment']['user_id'],
            "user_name" => $this->user_comment['User']['Name'],
            "user_photo" => $this->user_comment['User']['Photo'],
            "id" => $this->user_comment['User']['Comment']['id'],
            "content" => $this->user_comment['User']['Comment']['content'],
            "created_at" => $this->user_comment['User']['Comment']['created_at']
        ];
    }
}