<?php

namespace App\Http\Controllers;

use App\Jobs\SendMessage;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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

    public function messages(Request $request): JsonResponse
    {
        $roomId = $request->route('roomId'); // Get the room_id from route parameter
        
        $messages = Message::with('user')
            ->where('room_id', $roomId)
            ->orderBy('created_at', 'asc')
            ->get()
            ->append('time');
        
        return response()->json($messages);
    }

    public function message(Request $request): JsonResponse
    {
        try {
            // Validate the request
            $validated = $request->validate([
                'text' => 'required|string',
                'room_id' => 'required'
            ]);

            $message = Message::create([
                'user_id' => auth()->id(),
                'text' => $validated['text'],
                'room_id' => $validated['room_id'],
            ]);

            // Load the user relationship
            $message->load('user');
            
            // Convert to array to ensure proper serialization
            $messageArray = $message->toArray();
            
            // Add time using the accessor if not included
            if (!isset($messageArray['time'])) {
                $messageArray['time'] = $message->time;
            }

            // Dispatch the job with the complete message
            SendMessage::dispatch($message);

            return response()->json([
                'success' => true,
                'message' => $messageArray
            ]);
        } catch (\Exception $e) {
            \Log::error('Error creating message: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to create message'
            ], 500);
        }
    }
}
