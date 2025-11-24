<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'category_id',
        'name',
        'description',
        'price',
        'compare_price',
        'cost_per_item',
        'sku',
        'barcode',
        'quantity',
        'weight',
        'dimensions',
        'is_active'
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
    public function orderItems()
    {
        return $this->hasMany(Order_item::class);
    }
    public function cartItems()
    {
        return $this->hasMany(Cart_item::class);
    }
    public function product_image()
    {
        return $this->hasMany(ProductImage::class);
    }
    public function reviews()
    {
        return $this->hasMany(Review::class);
    }
}
