<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Promotion extends Model
{
    protected $table = 'promotions';
    protected $primaryKey = 'promotion_id';
    protected $fillable = [
        'code',
        'description',
        'discount_type',       // 'percentage' hoặc 'fixed'
        'discount_value',
        'min_order_amount',
        'max_discount_amount',
        'start_date',          // Lưu int timestamp
        'end_date',            // Lưu int timestamp
        'used_count',
        'is_active',
    ];
    protected $attributes = [
        'used_count' => 0,
        'is_active' => 1
    ];
    public function promotionCategories()
    {
        return $this->hasMany(PromotionCategories::class, 'promotion_id', 'promotion_id');
    }
}
