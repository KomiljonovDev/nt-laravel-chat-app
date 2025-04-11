<?php

namespace App\Http\Controllers;

use App\Jobs\SendMessage;
use App\Models\Message;
use App\Models\Room;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Exception;

class HomeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $user = User::where('id', auth()->id())->select([
            'id',
            'name',
            'email',
        ])->first();

        return view('home', [
            'user' => $user,
        ]);
    }

    public function messages(string|int $roomId): JsonResponse
    {
        // Convert and validate roomId
        $roomId = is_string($roomId) ? (int) $roomId : $roomId;

        if ($roomId <= 0) {
            return response()->json([
                'error' => 'Invalid room ID. Room ID must be a positive integer.'
            ], 400);
        }

        // Check if room exists
        try {
            $room = Room::findOrFail($roomId);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error' => 'Room not found'
            ], 404);
        }

        // Get all messages for the room
        $messages = Message::with('users')
            ->where('room_id', $roomId)
            ->get();

        // Handle empty messages case
        if ($messages->isEmpty()) {
            return response()->json([]);
        }

        // Append time to each message
        $messages->each(function ($message) {
            $message->append('time');
        });

        dd($messages);
        return response()->json($messages);
    }

    public function message(Request $request): JsonResponse
    {
        $message = Message::create([
            'user_id' => auth()->id(),
            'text' => $request->get('text'),
        ]);
        SendMessage::dispatch($message);

        return response()->json([
            'success' => true,
            'message' => "Message created and job dispatched.",
        ]);
    }
}
