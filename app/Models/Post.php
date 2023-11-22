<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'sender_id',
        'author_id',
        'content',
        'location',
        'like_count',
        // 'image_url',
        // 'created_at',
        // 'updated_at',
    ];
    
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function images()
    {
        return $this->hasMany(Image::class);
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function likes()
    {
        return $this->hasMany(Like::class);
    }

    // public function likesWithUsers()
    // {
    //     return $this->hasMany(Like::class)->with('user');
    // }

    public function likesWithUsers()
    {
        return $this->hasMany(Like::class)->with('user:id,name,avatar_url,cover_image_url,is_active');
    }
}
