<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Categories extends Model
{
    protected $table = 'categories';
    protected $primaryKey = 'category_id';
    protected $fillable = ['name', 'parent_id', 'image'];
    public function product()
    {
        return $this->hasMany(Product::class, 'category_id', 'category_id');
    }
    public function promotionCategories()
    {
        return $this->hasMany(PromotionCategories::class, 'category_id', 'category_id');
    }
    public function children()
    {
        return $this->hasMany(Categories::class, 'parent_id', 'category_id');
    }
    public function parent()
    {
        return $this->belongsTo(Categories::class, 'parent_id', 'category_id');
    }
}
