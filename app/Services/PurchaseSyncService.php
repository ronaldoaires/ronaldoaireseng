<?php

namespace App\Services;

use App\Models\Purchase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * Turns a Mercado Pago payment into local purchase state. Safe to run many
 * times for the same event (webhooks are retried and arrive out of order).
 */
class PurchaseSyncService
{
    private const REFERENCE_PREFIX = 'purchase-';

    /**
     * Applies one Mercado Pago payment to the purchase it belongs to.
     * Returns null when the payment is not a product purchase.
     */
    public function syncPayment(array $payment): ?Purchase
    {
        $reference = (string) ($payment['external_reference'] ?? '');

        // Only references created by our checkout are handled here.
        if (! str_starts_with($reference, self::REFERENCE_PREFIX)) {
            return null;
        }

        $id = substr($reference, strlen(self::REFERENCE_PREFIX));

        if (! ctype_digit($id)) {
            return null;
        }

        $purchase = Purchase::find((int) $id);

        if (! $purchase) {
            Log::warning('Mercado Pago: no local purchase for reference '.$reference);

            return null;
        }

        $paymentId = (string) ($payment['id'] ?? '');

        if (! ctype_digit($paymentId)) {
            Log::warning("Mercado Pago: payment without a valid id for purchase {$purchase->id}.");

            return $purchase;
        }

        // A paid purchase is never touched by another payment (an old rejected
        // attempt arriving late, or a duplicated payment).
        if ($purchase->status === 'paid' && $purchase->payment_id !== $paymentId) {
            Log::warning("Mercado Pago: payment {$paymentId} ignored, purchase {$purchase->id} is already paid.");

            return $purchase;
        }

        $status = match ($payment['status'] ?? null) {
            'approved' => 'paid',
            'rejected' => 'failed',
            'cancelled' => 'cancelled',
            'refunded', 'charged_back' => 'refunded',
            default => 'pending', // pending, in_process, authorized...
        };

        if ($status === 'paid' && ! $this->matchesPurchaseAmount($payment, $purchase)) {
            Log::warning("Mercado Pago: payment {$paymentId} amount or currency differs from purchase {$purchase->id}.");

            return $purchase;
        }

        $attributes = [
            'status' => $status,
            'payment_id' => $paymentId,
            'payment_url' => $status === 'paid' || $status === 'refunded' ? null : $purchase->payment_url,
            'payment_method' => $this->methodOf($payment),
        ];

        if ($status === 'paid') {
            $attributes['paid_at'] = isset($payment['date_approved'])
                ? Carbon::parse($payment['date_approved'])->setTimezone(config('app.timezone'))
                : now();

        }

        $purchase->update($attributes);

        return $purchase;
    }

    private function matchesPurchaseAmount(array $payment, Purchase $purchase): bool
    {
        $amount = $payment['transaction_amount'] ?? null;

        if (($payment['currency_id'] ?? null) !== 'BRL' || ! is_numeric($amount)) {
            return false;
        }

        return (int) round((float) $amount * 100) === (int) round((float) $purchase->amount * 100);
    }

    private function methodOf(array $payment): ?string
    {
        $type = $payment['payment_type_id'] ?? null;
        $method = $payment['payment_method_id'] ?? null;

        return match (true) {
            $method === 'pix' => 'pix',
            in_array($type, ['credit_card', 'debit_card'], true) => $type,
            default => $type ?? $method,
        };
    }
}
