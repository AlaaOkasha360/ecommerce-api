<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{


    protected function user(){
        return $this->belongsTo(User::class);
    }
    protected function orderItems(){
        return $this->hasMany(Order_item::class);
    }
}
