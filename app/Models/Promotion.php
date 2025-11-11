<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Promotion extends Model
{
    protected $table = 'promotions';
    protected $primaryKey = 'promotion_id';
    protected $fillable = ['code', 'description', 'discount_type', 'discount_value', 'min_order_amount', 'max_discount_amount', 'start_date', 'end_date', 'used_count', 'is_active'];
    public function promotionCategories()
    {
        return $this->hasMany(PromotionCategories::class, 'promotion_id', 'promotion_id');
    }
}
