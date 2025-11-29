<?php

namespace App\Http\Controllers;

use App\HttpResponses;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\Request;
use Stripe\StripeClient;
use Stripe\Webhook;

class PaymentController extends Controller
{
    use HttpResponses;

    /**
     * Create checkout session
     */
    public function createCheckoutSession(Request $request)
    {
        $validated = $request->validate([
            'order_id' => ['required', 'integer', 'exists:orders,id']
        ]);

        $order = Order::with('items')->findOrFail($validated['order_id']);
        $user = auth()->user();

        if ($order->user_id !== $user->id) {
            return $this->error([], 'Unauthorized', 403);
        }

        try {
            $stripe = new StripeClient(env('STRIPE_SECRET'));

            // Prepare line items from order
            $lineItems = $order->items->map(function ($item) {
                return [
                    'price_data' => [
                        'currency' => 'usd',
                        'product_data' => [
                            'name' => $item->product_name,
                            'description' => 'SKU: ' . $item->product_sku,
                        ],
                        'unit_amount' => round($item->price * 100), // Convert to cents
                    ],
                    'quantity' => $item->quantity,
                ];
            })->toArray();

            // Add tax as line item
            if ($order->tax > 0) {
                $lineItems[] = [
                    'price_data' => [
                        'currency' => 'usd',
                        'product_data' => [
                            'name' => 'Tax',
                        ],
                        'unit_amount' => round($order->tax * 100),
                    ],
                    'quantity' => 1,
                ];
            }

            // Add shipping as line item
            if ($order->shipping_cost > 0) {
                $lineItems[] = [
                    'price_data' => [
                        'currency' => 'usd',
                        'product_data' => [
                            'name' => 'Shipping',
                        ],
                        'unit_amount' => round($order->shipping_cost * 100),
                    ],
                    'quantity' => 1,
                ];
            }

            $session = $stripe->checkout->sessions->create([
                'payment_method_types' => ['card'],
                'line_items' => $lineItems,
                'mode' => 'payment',
                'success_url' => route('payment.success') . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => route('payment.cancel') . '?order_id=' . $order->id,
                'metadata' => [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                ],
                'client_reference_id' => $order->order_number,
            ]);

            return $this->success([
                'session_id' => $session->id,
                'checkout_url' => $session->url,
                'order_id' => $order->id,
                'order_number' => $order->order_number,
            ]);
        } catch (\Exception $e) {
            return $this->error([], 'Failed to create checkout session: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Verify checkout session
     */
    public function verifySession(Request $request)
    {
        $validated = $request->validate([
            'session_id' => ['required', 'string']
        ]);

        try {
            $stripe = new StripeClient(env('STRIPE_SECRET'));
            $session = $stripe->checkout->sessions->retrieve($validated['session_id']);

            if ($session->payment_status === 'paid') {
                $orderId = $session->metadata->order_id ?? null;

                if (!$orderId) {
                    return $this->error([], 'Order not found in session metadata', 400);
                }

                $order = Order::findOrFail($orderId);

                // Create or update payment record
                $payment = Payment::updateOrCreate(
                    ['order_id' => $order->id],
                    [
                        'stripe_payment_id' => $session->payment_intent,
                        'stripe_payment_intent' => $session->id,
                        'amount' => $session->amount_total / 100,
                        'currency' => strtoupper($session->currency),
                        'status' => 'succeeded',
                        'payment_method_type' => 'card'
                    ]
                );

                // Update order status
                $order->payment_status = 'paid';
                $order->status = 'processing';
                $order->save();

                return $this->success([
                    'payment' => [
                        'id' => $payment->id,
                        'order_id' => $payment->order_id,
                        'stripe_payment_id' => $payment->stripe_payment_id,
                        'amount' => $payment->amount,
                        'currency' => $payment->currency,
                        'status' => $payment->status,
                    ],
                    'order' => [
                        'id' => $order->id,
                        'order_number' => $order->order_number,
                        'status' => $order->status,
                        'payment_status' => $order->payment_status,
                    ]
                ], 'Payment verified successfully');
            } else {
                return $this->error([], 'Payment not completed. Status: ' . $session->payment_status, 400);
            }
        } catch (\Exception $e) {
            return $this->error([], 'Failed to verify session: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Handle Stripe webhook
     */
    public function webhook(Request $request)
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $webhookSecret = env('STRIPE_WEBHOOK_SECRET');

        try {
            $event = Webhook::constructEvent($payload, $sigHeader, $webhookSecret);
        } catch (\UnexpectedValueException $e) {
            return response()->json(['error' => 'Invalid payload'], 400);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        // Handle the event
        switch ($event->type) {
            case 'checkout.session.completed':
                $session = $event->data->object;
                $this->handleCheckoutCompleted($session);
                break;

            case 'checkout.session.async_payment_succeeded':
                $session = $event->data->object;
                $this->handleCheckoutCompleted($session);
                break;

            case 'checkout.session.async_payment_failed':
                $session = $event->data->object;
                $this->handlePaymentFailed($session);
                break;

            case 'charge.refunded':
                $charge = $event->data->object;
                $this->handleRefund($charge);
                break;

            default:
                // Unexpected event type
                break;
        }

        return response()->json(['received' => true]);
    }

    private function handleCheckoutCompleted($session)
    {
        $orderId = $session->metadata->order_id ?? null;
        if (!$orderId) {
            return;
        }

        $order = Order::find($orderId);
        if (!$order) {
            return;
        }

        // Create or update payment record
        Payment::updateOrCreate(
            ['order_id' => $order->id],
            [
                'stripe_payment_id' => $session->payment_intent,
                'stripe_payment_intent' => $session->id,
                'amount' => $session->amount_total / 100,
                'currency' => strtoupper($session->currency),
                'status' => 'succeeded',
                'payment_method_type' => 'card'
            ]
        );

        // Update order status
        $order->payment_status = 'paid';
        $order->status = 'processing';
        $order->save();

        // TODO: Send confirmation email
    }

    private function handlePaymentFailed($session)
    {
        $orderId = $session->metadata->order_id ?? null;
        if (!$orderId) {
            return;
        }

        $order = Order::find($orderId);
        if (!$order) {
            return;
        }

        Payment::updateOrCreate(
            ['order_id' => $order->id],
            [
                'stripe_payment_intent' => $session->id,
                'amount' => $session->amount_total / 100,
                'currency' => strtoupper($session->currency),
                'status' => 'failed',
            ]
        );

        $order->payment_status = 'failed';
        $order->save();
    }

    private function handleRefund($charge)
    {
        $payment = Payment::where('stripe_payment_id', $charge->payment_intent)->first();
        if (!$payment) {
            return;
        }

        $payment->status = 'refunded';
        $payment->save();

        $order = $payment->order;
        if ($order) {
            $order->payment_status = 'refunded';
            $order->save();
        }
    }
}
