<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;
use Throwable;

class StripeWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $payload = $request->getContent();
        $signature = $request->header('Stripe-Signature');
        $secret = config('services.stripe.webhook_secret');

        try {
            $event = $secret
                ? Webhook::constructEvent($payload, $signature, $secret)
                : json_decode($payload, false, 512, JSON_THROW_ON_ERROR);
        } catch (SignatureVerificationException $e) {
            Log::error('Stripe webhook signature verification failed: ' . $e->getMessage());
            return response('Invalid signature', Response::HTTP_BAD_REQUEST);
        } catch (Throwable $e) {
            Log::error('Stripe webhook payload could not be parsed: ' . $e->getMessage(), ['exception' => $e]);
            return response('Invalid payload', Response::HTTP_BAD_REQUEST);
        }

        try {
            $type = $event->type ?? null;

            if ($type === 'checkout.session.completed' || $type === 'checkout.session.async_payment_succeeded') {
                $session = $event->data->object;
                $orderId = $session->metadata->order_id ?? null;

                $order = $orderId
                    ? Order::find($orderId)
                    : Order::where('stripe_checkout_session_id', $session->id)->first();

                if ($order) {
                    $order->update([
                        'payment_status' => 'paid',
                        'status' => $order->status === 'pending' ? 'processing' : $order->status,
                        'stripe_payment_intent_id' => $session->payment_intent ?? $order->stripe_payment_intent_id,
                    ]);
                } else {
                    Log::error('Stripe webhook: order not found for checkout session ' . ($session->id ?? 'unknown'));
                }
            }

            if ($type === 'checkout.session.async_payment_failed' || $type === 'checkout.session.expired') {
                $session = $event->data->object;
                $orderId = $session->metadata->order_id ?? null;
                $order = $orderId
                    ? Order::find($orderId)
                    : Order::where('stripe_checkout_session_id', $session->id)->first();

                if ($order) {
                    $order->update(['payment_status' => 'failed']);
                }
            }
        } catch (Throwable $e) {
            Log::error('Stripe webhook handling failed: ' . $e->getMessage(), ['exception' => $e]);
            // Return 200 anyway is wrong for genuine failures; return 500 so Stripe retries.
            return response('Webhook handling error', Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response('OK', Response::HTTP_OK);
    }
}
