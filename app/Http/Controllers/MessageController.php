<?php

namespace App\Http\Controllers;

use App\Models\Message;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function markAsDelivered($id)
    {
        $message = Message::findOrFail($id);
        if (auth()->id() === $message->receiver_id) {
            $message->delivered = true;
            $message->save();
        }

        return response()->json(['status' => 'delivered']);
    }

    public function markAsRead($id)
    {
        $message = Message::findOrFail($id);
        if (auth()->id() === $message->receiver_id) {
            $message->read = true;
            $message->save();
        }

        return response()->json(['status' => 'read']);
    }

}
