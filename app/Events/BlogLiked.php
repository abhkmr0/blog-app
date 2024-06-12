<?php
namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BlogLiked implements ShouldBroadcast
{
    use Dispatchable, SerializesModels;

    public $blog;

    public function __construct($blog)
    {
        $this->blog = $blog;
    }

    public function broadcastOn()
    {
        return new Channel('blog.' . $this->blog->id);
    }
}
