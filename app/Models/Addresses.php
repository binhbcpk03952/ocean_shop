<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Addresses extends Model
{
    protected $table = 'addresses';
    protected $primaryKey = 'address_id';
    protected $fillable = ['user_id' ,'recipient_name', 'recipient_phone', 'street_address', 'ward', 'district', 'province','type', 'is_default'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }
    public function order()
    {
        return $this->hasMany(Order::class, 'address_id', 'address_id');
    }
}
