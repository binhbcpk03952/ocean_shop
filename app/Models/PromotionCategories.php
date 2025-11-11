<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PromotionCategories extends Model
{
    protected $table = 'promotion_categories';
    protected $primaryKey = 'promotion_category_id';
    protected $fillable = ['promotion_id', 'category_id'];
    public function promotion()
    {
        return $this->belongsTo(Promotion::class, 'promotion_id', 'promotion_id');
    }
    public function category()
    {
        return $this->belongsTo(Categories::class, 'category_id', 'category_id');
    }
}
