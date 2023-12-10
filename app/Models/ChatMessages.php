<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatMessages extends Model
{
    use HasFactory;

    protected $fillable = ['message', 'chat_id','user_id','type','data'];

    // If we thought about the relationship of the chat message,
    // we will find that the message belongs to one chat,
    // so we created chat() function 
    // and belongs to the user who sent it, so we made sender().
    // public function chat(): BelongsTo
    // {
    //     return $this->belongsTo(Chat::class);
    // }
    // public function sender(): BelongsTo
    // {
    //     return $this->belongsTo(User::class, 'user_id');
    // }

    public function chat(): BelongsTo
    {
        return $this->belongsTo(Chat::class)->with('participants');
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id')->select('id', 'name');
    }
}
