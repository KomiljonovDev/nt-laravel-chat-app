<?php

namespace App\Jobs;

use App\Events\GotMessage;
use App\Models\Message;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendMessage implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public Message $message)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // Load the user relationship if not already loaded
            if (!$this->message->relationLoaded('user')) {
                $this->message->load('user');
            }

            // Create message data including all necessary fields
            $messageData = [
                'id' => $this->message->id,
                'room_id' => $this->message->room_id, // Ensure room_id is included
                'text' => $this->message->text,
                'time' => $this->message->created_at->format('H:i'),
                'user' => [
                    'id' => $this->message->user->id,
                    'name' => $this->message->user->name
                ]
            ];

            \Log::info('SendMessage job dispatching message:', $messageData);

            // Dispatch the message data using event helper instead of dispatch
            event(new GotMessage($messageData));
        } catch (\Exception $e) {
            \Log::error('Error in SendMessage job: ' . $e->getMessage());
        }
    }
}
