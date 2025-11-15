<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order_item extends Model
{


    protected function order(){
        return $this->belongsTo(Order::class);
    }
    protected function product(){
        return $this->belongsTo(Product::class);
    }
}
