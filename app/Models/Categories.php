<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Categories extends Model
{
    protected $table = 'categories';
    protected $primaryKey = 'category_id';
    protected $fillable = ['name', 'parent_id', 'image'];
    public function products()
{
    return $this->belongsToMany(Product::class, 'product_categories', 'category_id', 'product_id');
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
    public static function getAllChildIds($categoryId)
    {
        $ids = [];

        $children = self::where('parent_id', $categoryId)->get();

        foreach ($children as $child) {
            $ids[] = $child->category_id;
            $ids = array_merge($ids, self::getAllChildIds($child->category_id));
        }

        return $ids;
    }
}
