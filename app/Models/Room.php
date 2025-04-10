<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
    ];

    public function users(){
        return $this->belongsToMany(User::class);
    }
    public function lastMessage(){
        return $this->hasOne(Message::class)
            ->orderBy('id', 'desc')
                ->limit(1);
    }
}
