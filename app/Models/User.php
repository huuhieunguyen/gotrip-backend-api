<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'email_verification_token',
        'email_verified_at',
        'phone_number',
        'tag_name',
        'avatar_url',
        'cover_image_url',
        'is_active',
        'last_active_time',
        'inactice_duration',
        'intro',
        'portfolio_url',
        'count_followees',
        'count_followers',
        // 'receiver_id',
        // 'sender_id',
        // 'followees',
        // 'followers',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function followers()
    {
        return $this->belongsToMany(User::class, 'user_relationships', 'followee_id', 'follower_id');
    }

    public function followees()
    {
        return $this->belongsToMany(User::class, 'user_relationships', 'follower_id', 'followee_id');
    }

    public function getFollowers()
    {
        return $this->followers()->get();
    }

    public function getFollowees()
    {
        return $this->followees()->get();
    }
}
