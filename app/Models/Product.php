<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{

    protected function category(){
        return $this->belongsTo(Category::class);
    }
    protected function orderItems(){
        return $this->hasMany(Order_item::class);
    }
    protected function cartItems(){
        return $this->hasMany(Cart_item::class);
    }
}
