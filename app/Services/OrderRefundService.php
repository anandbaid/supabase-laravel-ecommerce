<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Stripe\Refund;
use Stripe\Stripe;
use Throwable;

class OrderRefundService
{
    /**
     * Refund an order's Stripe payment (in full) and record the refund on
     * the order. No-ops (returns true) for orders that were never paid via
     * Stripe — those are refunded/settled outside the app (e.g. COD).
     *
     * @throws RuntimeException if the order was paid via Stripe but the refund call fails.
     */
    public function refund(Order $order, ?string $reason = null): bool
    {
        if (!$order->isRefundableViaStripe()) {
            return false;
        }

        if ($order->payment_status === 'refunded') {
            return true; // already refunded, nothing to do
        }

        Stripe::setApiKey(config('services.stripe.secret'));

        try {
            $refund = Refund::create([
                'payment_intent' => $order->stripe_payment_intent_id,
                'reason' => in_array($reason, ['duplicate', 'fraudulent', 'requested_by_customer'], true) ? $reason : 'requested_by_customer',
            ]);
        } catch (Throwable $e) {
            Log::error('Stripe refund failed for order ' . $order->order_number . ': ' . $e->getMessage(), ['exception' => $e]);
            throw new RuntimeException('The refund could not be processed with Stripe. Please try again or refund manually from the Stripe dashboard.');
        }

        $order->update([
            'payment_status' => 'refunded',
            'refund_amount' => $order->total,
            'refunded_at' => now(),
            'stripe_refund_id' => $refund->id,
        ]);

        return true;
    }
}
