<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cart_item extends Model
{


    protected function cart(){
        return $this->belongsTo(Cart::class);
    }
    protected function product(){
        return $this->belongsTo(Product::class);
    }
}
