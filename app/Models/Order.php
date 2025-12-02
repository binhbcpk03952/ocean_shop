<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $table = 'orders';
    protected $primaryKey = 'order_id';
    protected $fillable = ['user_id', 'final_amount', 'shipping_fee', 'payment_method', 'promotion_id', 'discount_amount', 'status'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }
    public function addresses()
    {
        return $this->belongsTo(Addresses::class, 'address_id', 'address_id');
    }
    public function orderItem()
    {
        return $this->hasMany(OrderItem::class, 'order_id', 'order_id');
    }
}
