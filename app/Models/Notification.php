<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'author_id',
        'user_id',
        'post_id',
        'type',
        'message',
        'is_read',
        'read_at',
    ];

    // protected $casts = [
    //     'is_read' => 'boolean',
    //     'read_at' => 'datetime',
    //     'created_at' => 'datetime',
    //     'updated_at' => 'datetime',
    // ];

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function post()
    {
        return $this->belongsTo(Post::class);
    }
}
