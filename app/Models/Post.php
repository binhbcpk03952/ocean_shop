<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;


class Post extends Model
{
    protected $table = 'posts';
    protected $primaryKey = 'post_id';
    protected $fillable = ['user_id', 'title', 'slug', 'content', 'thumbnail_path'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }
};
