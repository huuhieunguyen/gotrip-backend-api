<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Chat extends Model
{
    use HasFactory;
    protected $fillable = ['data', 'direct_message'];
    protected $casts = [
        'data'           => 'array',
        'direct_message' => 'boolean',
        'private'        => 'boolean',
    ];

    // defined the relationship with users
    public function participants(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    // get all conversation participants for a specific chat.
    public function messages(): HasMany
    {
        return $this->hasMany(ChatMessages::class);
    }

    // check if the user is a participant in a chat
    public function isParticipant($user_id)
    {
        $data = $this->participants->where('id', $user_id)->first();
        if(!empty($data) ){
         return true;
        }
        return false;
    }

    // make the chat private or public.
    public function makePrivate($isPrivate = true)
    {
        $this->private = $isPrivate;
        $this->save();

        return $this;
    }
}
