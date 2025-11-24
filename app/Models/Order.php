<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{


    public function user(){
        return $this->belongsTo(User::class);
    }
    public function orderItems(){
        return $this->hasMany(Order_item::class);
    }

    public function payment(){
        return $this->belongsTo(Payment::class);
    }
    public function shipping_address(){
        return $this->belongsTo(Address::class);
    }

    public function billing_address(){
        return $this->belongsTo(Address::class);
    }
}
