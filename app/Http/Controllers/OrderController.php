<?php

namespace App\Http\Controllers;

use App\Http\Requests\CancelOrderRequest;
use App\Http\Requests\CreateOrderRequest;
use App\Http\Requests\UpdateOrderStatusRequest;
use App\HttpResponses;
use App\Models\Address;
use App\Models\Cart;
use App\Models\Cart_item;
use App\Models\Order;
use App\Models\Order_item;
use Auth;
use DB;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    use HttpResponses;

    /**
     * Create order from cart
     */
    public function createOrder(CreateOrderRequest $request)
    {
        $validated = $request->validated();
        $user = Auth::user();

        // Verify addresses belong to user
        $shippingAddress = Address::where('id', $validated['shipping_address_id'])
            ->where('user_id', $user->id)
            ->firstOrFail();

        $billingAddress = Address::where('id', $validated['billing_address_id'])
            ->where('user_id', $user->id)
            ->firstOrFail();

        // Get cart items
        $cart = Cart::where('user_id', $user->id)->with('cartItems.product')->first();

        if (!$cart || $cart->cartItems->isEmpty()) {
            return $this->error([], 'Your cart is empty', 400);
        }

        // Calculate totals
        $subtotal = $cart->cartItems->sum(function ($item) {
            return $item->quantity * $item->price_at_addition;
        });
        $tax = round($subtotal * 0.08, 2); // 8% tax
        $shippingCost = 10.00;
        $total = $subtotal + $tax + $shippingCost;

        // Create order in transaction
        DB::beginTransaction();
        try {
            $order = Order::create([
                'user_id' => $user->id,
                'order_number' => Order::generateOrderNumber(),
                'status' => 'pending',
                'payment_status' => 'pending',
                'shipping_address_id' => $shippingAddress->id,
                'billing_address_id' => $billingAddress->id,
                'subtotal' => $subtotal,
                'tax' => $tax,
                'shipping_cost' => $shippingCost,
                'total_amount' => $total,
                'payment_method' => $validated['payment_method'],
                'notes' => $validated['notes'] ?? null,
            ]);

            // Create order items
            foreach ($cart->cartItems as $item) {
                Order_item::create([
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'product_name' => $item->product->name,
                    'product_sku' => $item->product->sku,
                    'quantity' => $item->quantity,
                    'price' => $item->price_at_addition,
                    'subtotal' => $item->quantity * $item->price_at_addition,
                ]);
            }

            // Clear cart
            $cart->cartItems()->delete();

            DB::commit();

            // Load relationships
            $order->load(['items', 'shippingAddress', 'billingAddress']);

            return $this->success([
                'order' => [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'user_id' => $order->user_id,
                    'status' => $order->status,
                    'payment_status' => $order->payment_status,
                    'shipping_address' => [
                        'street_address' => $order->shippingAddress->street_address,
                        'city' => $order->shippingAddress->city,
                        'state' => $order->shippingAddress->state,
                        'postal_code' => $order->shippingAddress->postal_code,
                        'country' => $order->shippingAddress->country,
                    ],
                    'billing_address' => [
                        'street_address' => $order->billingAddress->street_address,
                        'city' => $order->billingAddress->city,
                        'state' => $order->billingAddress->state,
                        'postal_code' => $order->billingAddress->postal_code,
                        'country' => $order->billingAddress->country,
                    ],
                    'items' => $order->items->map(function ($item) {
                        return [
                            'id' => $item->id,
                            'product_id' => $item->product_id,
                            'product_name' => $item->product_name,
                            'product_sku' => $item->product_sku,
                            'quantity' => $item->quantity,
                            'price' => $item->price,
                            'subtotal' => $item->subtotal,
                        ];
                    }),
                    'subtotal' => $order->subtotal,
                    'tax' => $order->tax,
                    'shipping_cost' => $order->shipping_cost,
                    'total_amount' => $order->total_amount,
                    'payment_method' => $order->payment_method,
                    'notes' => $order->notes,
                    'created_at' => $order->created_at,
                ],
                'next_step' => [
                    'action' => 'create_checkout_session',
                    'message' => 'Use POST /api/payments/create-checkout with order_id to initiate payment'
                ]
            ], 'Order created successfully', 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error([], 'Failed to create order: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get order details
     */
    public function show(Order $order)
    {
        $user = Auth::user();

        if ($order->user_id !== $user->id) {
            return $this->error([], 'Unauthorized', 403);
        }

        $order->load(['user', 'items', 'shippingAddress', 'billingAddress', 'payment']);

        return $this->success([
            'id' => $order->id,
            'order_number' => $order->order_number,
            'user' => [
                'id' => $order->user->id,
                'first_name' => $order->user->first_name,
                'last_name' => $order->user->last_name,
                'email' => $order->user->email,
            ],
            'status' => $order->status,
            'payment_status' => $order->payment_status,
            'shipping_address' => [
                'street_address' => $order->shippingAddress->street_address,
                'city' => $order->shippingAddress->city,
                'state' => $order->shippingAddress->state,
                'postal_code' => $order->shippingAddress->postal_code,
                'country' => $order->shippingAddress->country,
            ],
            'billing_address' => [
                'street_address' => $order->billingAddress->street_address,
                'city' => $order->billingAddress->city,
                'state' => $order->billingAddress->state,
                'postal_code' => $order->billingAddress->postal_code,
                'country' => $order->billingAddress->country,
            ],
            'items' => $order->items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'product_id' => $item->product_id,
                    'product_name' => $item->product_name,
                    'product_sku' => $item->product_sku,
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                    'subtotal' => $item->subtotal,
                ];
            }),
            'subtotal' => $order->subtotal,
            'tax' => $order->tax,
            'shipping_cost' => $order->shipping_cost,
            'total_amount' => $order->total_amount,
            'payment_method' => $order->payment_method,
            'payment' => $order->payment ? [
                'id' => $order->payment->id,
                'stripe_payment_id' => $order->payment->stripe_payment_id,
                'amount' => $order->payment->amount,
                'currency' => $order->payment->currency,
                'status' => $order->payment->status,
            ] : null,
            'notes' => $order->notes,
            'created_at' => $order->created_at,
            'updated_at' => $order->updated_at,
        ]);
    }

    /**
     * Get user's orders
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Order::where('user_id', $user->id)->with('items');

        if ($request->status && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $orders = $query->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 20);

        return $this->success([
            'orders' => $orders->map(function ($order) {
                return [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'status' => $order->status,
                    'payment_status' => $order->payment_status,
                    'total_amount' => $order->total_amount,
                    'items_count' => $order->items->count(),
                    'created_at' => $order->created_at,
                    'delivered_at' => null, // Add delivered_at timestamp to orders table if needed
                ];
            }),
            'pagination' => [
                'current_page' => $orders->currentPage(),
                'per_page' => $orders->perPage(),
                'total' => $orders->total(),
                'total_pages' => $orders->lastPage(),
            ]
        ]);
    }

    /**
     * Cancel order
     */
    public function cancel(CancelOrderRequest $request, Order $order)
    {
        $user = Auth::user();

        if ($order->user_id !== $user->id) {
            return $this->error([], 'Unauthorized', 403);
        }

        if (!in_array($order->status, ['pending', 'processing'])) {
            return $this->error([], 'Cannot cancel order in current status', 400);
        }

        $order->status = 'cancelled';
        $order->save();

        return $this->success([
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'status' => $order->status,
            'refund_status' => 'pending',
            'refund_amount' => $order->total_amount,
            'cancelled_at' => now(),
        ], 'Order cancelled successfully');
    }

    /**
     * Update order status (Admin only)
     */
    public function updateStatus(UpdateOrderStatusRequest $request, Order $order)
    {
        $validated = $request->validated();

        $order->status = $validated['status'];
        $order->save();

        $trackingInfo = null;
        if (isset($validated['tracking_number'])) {
            $trackingInfo = [
                'carrier' => $validated['carrier'] ?? null,
                'tracking_number' => $validated['tracking_number'],
                'tracking_url' => $this->getTrackingUrl($validated['carrier'] ?? '', $validated['tracking_number']),
            ];
        }

        return $this->success([
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'status' => $order->status,
            'tracking_info' => $trackingInfo,
            'updated_at' => $order->updated_at,
        ], 'Order status updated');
    }

    private function getTrackingUrl($carrier, $trackingNumber)
    {
        $urls = [
            'UPS' => "https://www.ups.com/track?tracknum={$trackingNumber}",
            'FedEx' => "https://www.fedex.com/fedextrack/?trknbr={$trackingNumber}",
            'USPS' => "https://tools.usps.com/go/TrackConfirmAction?tLabels={$trackingNumber}",
        ];

        return $urls[$carrier] ?? null;
    }
}
