<?php


namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class GotMessage implements ShouldBroadcast {
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;

    public function __construct($message)
    {
        \Log::info('GotMessage constructor received:', ['message' => $message]);

        // Store the message data directly without restructuring
        $this->message = $message;
    }

    public function broadcastOn()
    {
        return new PrivateChannel("channel_for_everyone");
    }

    public function broadcastWith()
    {
        // Send the message data directly without additional wrapping
        \Log::info('GotMessage broadcasting:', ['message' => $this->message]);
        
        return $this->message;
    }
}
