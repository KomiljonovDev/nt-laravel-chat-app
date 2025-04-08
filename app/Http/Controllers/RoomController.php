<?php

namespace App\Http\Controllers;

use App\Models\Room;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    public function index()
    {
        $roomUser = Room::with('users')->get();

        $rooms = [];
        foreach ($roomUser as $room) {
            foreach ($room->users as $user) {
                $rooms[] = [
                    'id' => $user->id,
                    'name' => $user->name,
                    'lastMessage' => 'Last Message RoomController',
                    'timestamp' => $user->updated_at->format('H:i'),
                    'avatar' => '/images/default-avatar.png',
                    'unread' => false,
                    'email' => $user->email,
                    'phone' => $user->phone_number,
                    'location' => $user->location,
                    'lastSeen' => $user->updated_at->format('H:i'),
                ];
            }
        }

        return $rooms;
    }
}
