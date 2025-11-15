<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{

    protected function user(){
        return $this->belongsTo(User::class);
    }
    protected function cartItems(){
        return $this->hasMany(Cart_item::class);
    }
}
