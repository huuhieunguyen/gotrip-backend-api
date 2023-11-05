<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Relationship extends Model
{
    use HasFactory;

    protected $table = 'user_relationships';

    protected $fillable = [
        'follower_id',
        'followee_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
