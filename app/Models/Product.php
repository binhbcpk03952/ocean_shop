<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $table = 'products';
    protected $primaryKey = 'product_id';
    protected $fillable = ['name', 'description', 'price', 'category_id'];

    public function categories()
    {
        return $this->belongsTo(Categories::class, 'category_id', 'category_id');
    }

    public function image()
    {
        return $this->hasMany(ProductImage::class, 'product_id', 'product_id');
    }
    public function variant()
    {
        return $this->hasMany(ProductVariant::class, 'product_id', 'product_id');
    }
    public function favorite()
    {
        return $this->hasMany(Favorite::class, 'product_id', 'product_id');
    }
}