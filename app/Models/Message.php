<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model
{
    use HasFactory;

    public $table = 'messages';
    protected $guarded = [];
    
    // Add necessary attributes for API/broadcast responses
    protected $appends = ['time'];
    
    // Define which attributes should be included in arrays/JSON
    protected $visible = ['id', 'user_id', 'room_id', 'text', 'time', 'created_at', 'user'];
    
    // Define relationships to be automatically loaded
    protected $with = ['user'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function getTimeAttribute(): string
    {
        return date(
            "H:i",  // Changed format to match frontend expectations
            strtotime($this->attributes['created_at'])
        );
    }
}
