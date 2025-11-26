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
     * Create payment intent
     */
    public function createIntent(Request $request)
    {
        $validated = $request->validate([
            'order_id' => ['required', 'integer', 'exists:orders,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'currency' => ['required', 'string', 'size:3']
        ]);

        $order = Order::findOrFail($validated['order_id']);
        $user = auth()->user();

        if ($order->user_id !== $user->id) {
            return $this->error([], 'Unauthorized', 403);
        }

        try {
            $stripe = new StripeClient(env('STRIPE_SECRET'));
            $paymentIntent = $stripe->paymentIntents->create([
                'amount' => round($validated['amount'] * 100), // Convert to cents
                'currency' => strtolower($validated['currency']),
                'metadata' => [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                ]
            ]);

            return $this->success([
                'payment_intent_id' => $paymentIntent->id,
                'client_secret' => $paymentIntent->client_secret,
                'amount' => $validated['amount'],
                'currency' => strtoupper($validated['currency']),
                'status' => $paymentIntent->status,
            ]);
        } catch (\Exception $e) {
            return $this->error([], 'Failed to create payment intent: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Confirm payment
     */
    public function confirmPayment(Request $request)
    {
        $validated = $request->validate([
            'payment_intent_id' => ['required', 'string'],
            'order_id' => ['required', 'integer', 'exists:orders,id']
        ]);

        $order = Order::findOrFail($validated['order_id']);
        $user = auth()->user();

        if ($order->user_id !== $user->id) {
            return $this->error([], 'Unauthorized', 403);
        }

        try {
            $stripe = new StripeClient(env('STRIPE_SECRET'));
            $paymentIntent = $stripe->paymentIntents->retrieve($validated['payment_intent_id']);

            if ($paymentIntent->status === 'succeeded') {
                // Create or update payment record
                $payment = Payment::updateOrCreate(
                    ['order_id' => $order->id],
                    [
                        'stripe_payment_id' => $paymentIntent->latest_charge ?? null,
                        'stripe_payment_intent' => $paymentIntent->id,
                        'amount' => $paymentIntent->amount / 100,
                        'currency' => strtoupper($paymentIntent->currency),
                        'status' => 'succeeded',
                        'payment_method_type' => $paymentIntent->payment_method_types[0] ?? 'card'
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
                        'stripe_payment_intent' => $payment->stripe_payment_intent,
                        'amount' => $payment->amount,
                        'currency' => $payment->currency,
                        'status' => $payment->status,
                        'payment_method_type' => $payment->payment_method_type,
                    ],
                    'order' => [
                        'id' => $order->id,
                        'order_number' => $order->order_number,
                        'status' => $order->status,
                        'payment_status' => $order->payment_status,
                    ]
                ], 'Payment confirmed successfully');
            } else {
                return $this->error([], 'Payment not completed yet. Status: ' . $paymentIntent->status, 400);
            }
        } catch (\Exception $e) {
            return $this->error([], 'Failed to confirm payment: ' . $e->getMessage(), 500);
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
            case 'payment_intent.succeeded':
                $paymentIntent = $event->data->object;
                $this->handlePaymentSuccess($paymentIntent);
                break;

            case 'payment_intent.payment_failed':
                $paymentIntent = $event->data->object;
                $this->handlePaymentFailed($paymentIntent);
                break;

            case 'charge.refunded':
                $charge = $event->data->object;
                $this->handleRefund($charge);
                break;

            case 'payment_intent.canceled':
                $paymentIntent = $event->data->object;
                $this->handlePaymentCanceled($paymentIntent);
                break;

            default:
                // Unexpected event type
                break;
        }

        return response()->json(['received' => true]);
    }

    private function handlePaymentSuccess($paymentIntent)
    {
        $orderId = $paymentIntent->metadata->order_id ?? null;
        if (!$orderId)
            return;

        $order = Order::find($orderId);
        if (!$order)
            return;

        Payment::updateOrCreate(
            ['order_id' => $order->id],
            [
                'stripe_payment_id' => $paymentIntent->latest_charge ?? null,
                'stripe_payment_intent' => $paymentIntent->id,
                'amount' => $paymentIntent->amount / 100,
                'currency' => strtoupper($paymentIntent->currency),
                'status' => 'succeeded',
                'payment_method_type' => $paymentIntent->payment_method_types[0] ?? 'card'
            ]
        );

        $order->payment_status = 'paid';
        $order->status = 'processing';
        $order->save();

        // TODO: Send confirmation email
    }

    private function handlePaymentFailed($paymentIntent)
    {
        $orderId = $paymentIntent->metadata->order_id ?? null;
        if (!$orderId)
            return;

        $order = Order::find($orderId);
        if (!$order)
            return;

        Payment::updateOrCreate(
            ['order_id' => $order->id],
            [
                'stripe_payment_intent' => $paymentIntent->id,
                'amount' => $paymentIntent->amount / 100,
                'currency' => strtoupper($paymentIntent->currency),
                'status' => 'failed',
            ]
        );

        $order->payment_status = 'failed';
        $order->save();
    }

    private function handleRefund($charge)
    {
        $payment = Payment::where('stripe_payment_id', $charge->id)->first();
        if (!$payment)
            return;

        $payment->status = 'refunded';
        $payment->save();

        $order = $payment->order;
        if ($order) {
            $order->payment_status = 'refunded';
            $order->save();
        }
    }

    private function handlePaymentCanceled($paymentIntent)
    {
        $orderId = $paymentIntent->metadata->order_id ?? null;
        if (!$orderId)
            return;

        $order = Order::find($orderId);
        if (!$order)
            return;

        $payment = Payment::where('order_id', $order->id)->first();
        if ($payment) {
            $payment->status = 'cancelled';
            $payment->save();
        }

        $order->payment_status = 'failed';
        $order->save();
    }
}
