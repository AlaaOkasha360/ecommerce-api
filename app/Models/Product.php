<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{

    public function category(){
        return $this->belongsTo(Category::class);
    }
    public function orderItems(){
        return $this->hasMany(Order_item::class);
    }
    public function cartItems(){
        return $this->hasMany(Cart_item::class);
    }
    public function product_image(){
        return $this->hasMany(ProductImage::class);
    }
}
