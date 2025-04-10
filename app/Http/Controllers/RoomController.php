<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\User;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    public function index()
    {
        $currentUserId = auth()->user()->id;

        $roomsWithRelations = Room::with([
            'lastMessage.user',
            'users'=>fn ($query) => $query->where('user_id', '!=', $currentUserId)
        ])->get();

        $rooms = [];
        foreach ($roomsWithRelations as $room) {
            foreach ($room->users as $user) {
                $lastMessageText = 'No messages yet';
                $lastMessageTime = $room->updated_at->format('H:i');

                if ($room->lastMessage) {
                    $lastMessageText = $room->lastMessage->text;
                    $lastMessageTime = $room->lastMessage->created_at->format('H:i');
                }

                $rooms[] = [
                    'id' => $user->id,
                    'name' => $user->name,
                    'lastMessage' => $lastMessageText,
                    'timestamp' => $lastMessageTime,
                    'avatar' => $user->avatar ?? '/images/default-avatar.png',
                    'unread' => false, // You may want to implement logic for unread messages
                    'email' => $user->email,
                    'phone' => $user->phone_number,
                    'location' => $user->location,
                    'lastSeen' => $user->updated_at->format('H:i'),
                ];
            }
        }

        return response()->json($rooms);
    }
    public function getRoomByUser($selectedUserId){
        $userId = auth()->user()->id;

        $room = Room::whereHas('users', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        })->whereHas('users', function ($q) use ($selectedUserId) {
            $q->where('user_id', $selectedUserId);
        })->first();

        if (!$room) {
            $room = Room::create(['type' => 'private']);
            $room->users()->attach($userId);
            $room->users()->attach($selectedUserId);
        }

        return response()->json(['roomId' => $room->id]);
    }
}
