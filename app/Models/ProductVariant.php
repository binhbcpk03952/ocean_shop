<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{
    protected $table = 'product_variants';
    protected $primaryKey = 'variant_id';
    protected $fillable = ['color', 'size', 'price'];
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'product_id');
    }
    public function image()
    {
        return $this->hasMany(ProductImage::class, 'variant_id', 'variant_id');
    }
    public function cartItem()
    {
        return $this->hasMany(CartItem::class, 'variant_id', 'variant_id');
    }
    public function orderItem()
    {
        return $this->hasMany(OrderItem::class, 'variant_id', 'variant_id');
    }


}
