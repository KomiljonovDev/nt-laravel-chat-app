<?php

namespace App\Http\Controllers;

use App\Models\Room;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    public function index(){
        return [
                [
                'id'=> 1,
                'name'=> 'Arya Wibawa',
                'lastMessage'=> 'Yes, sure! I will fill it out now.',
                'timestamp'=> '10:20',
                'avatar'=> '/images/default-avatar.png',
                'unread'=> false,
                'email'=> 'arya.wibawa@example.com',
                'phone'=> '+62 812-3456-7890',
                'location'=> 'Jakarta, Indonesia',
                'lastSeen'=> 'today'
            ]
        ];
    }
}
