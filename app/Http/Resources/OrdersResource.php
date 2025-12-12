<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrdersResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'shipping_address_id' => $this->shipping_address?->id ?? $this->shipping_address_id,
            'billing_address_id' => $this->billing_address?->id ?? $this->billing_address_id,
            'shipping_address' => $this->shipping_address ? [
                'id' => $this->shipping_address->id,
                'street_address' => $this->shipping_address->street_address,
                'city' => $this->shipping_address->city,
                'state' => $this->shipping_address->state,
                'postal_code' => $this->shipping_address->postal_code,
                'country' => $this->shipping_address->country,
            ] : null,
            'billing_address' => $this->billing_address ? [
                'id' => $this->billing_address->id,
                'street_address' => $this->billing_address->street_address,
                'city' => $this->billing_address->city,
                'state' => $this->billing_address->state,
                'postal_code' => $this->billing_address->postal_code,
                'country' => $this->billing_address->country,
            ] : null,
            'status' => $this->status,
            'subtotal' => $this->subtotal,
            'tax' => $this->tax,
            'shipping_cost' => $this->shipping,
            'total_amount' => $this->total_amount,
            'payment_method' => $this->payment_method,
            'notes' => $this->notes,
            'items' => $this->whenLoaded('items', function () {
                return $this->items->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'product_id' => $item->product_id,
                        'product_name' => $item->product?->name ?? 'Unknown Product',
                        'quantity' => $item->quantity,
                        'price' => $item->price,
                        'subtotal' => $item->quantity * $item->price,
                    ];
                });
            }),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}
