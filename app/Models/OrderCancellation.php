<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderCancellation extends Model
{
    protected $table = 'order_cancellations';
    protected $primaryKey = 'id';
    protected $fillable = [
        'order_id',
        'reason_code',
        'reason_text',
        'cancelled_by',
        'refund_amount',
        'canceled_at',
    ];
    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id', 'order_id');
    }

}
