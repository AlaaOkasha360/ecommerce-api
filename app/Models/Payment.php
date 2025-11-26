<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'order_id',
        'stripe_payment_id',
        'stripe_payment_intent',
        'amount',
        'currency',
        'status',
        'payment_method_type'
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
