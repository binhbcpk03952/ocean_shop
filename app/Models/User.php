<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    // ✅ QUAN TRỌNG: vì DB của bạn dùng user_id làm khóa chính
    protected $primaryKey = 'user_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'sex',
        'is_locked',
        'email_verified_at' // ✅ cho social login
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function addresses()
    {
        return $this->hasMany(Addresses::class, 'user_id', 'user_id');
    }

    public function cart()
    {
        return $this->hasMany(Cart::class, 'user_id', 'user_id');
    }

    public function favorite()
    {
        return $this->hasMany(Favorite::class, 'user_id', 'user_id');
    }

    public function order()
    {
        return $this->hasMany(Order::class, 'user_id', 'user_id');
    }

    public function post()
    {
        return $this->hasMany(Post::class, 'user_id', 'user_id');
    }

    public function banner()
    {
        return $this->hasMany(Banner::class, 'user_id', 'user_id');
    }
}
