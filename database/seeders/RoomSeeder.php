<?php

namespace Database\Seeders;

use App\Models\Room;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoomSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $room = Room::factory()->create();
        $room->users()->attach(
            User::query()->inRandomOrder()->pluck('id')->toArray() // [1,2]
        );
    }
}
