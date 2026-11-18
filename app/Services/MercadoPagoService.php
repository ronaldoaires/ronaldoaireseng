<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Product;
use App\Models\Purchase;

/**
 * Thin wrapper over the Mercado Pago REST API (no SDK).
 * Every call throws Illuminate\Http\Client\RequestException on a 4xx/5xx answer.
 */
class MercadoPagoService
{
    private function client(): PendingRequest
    {
        return Http::baseUrl('https://api.mercadopago.com')
            ->withToken((string) config('services.mercadopago.access_token'))
            ->acceptJson()
            ->asJson()
            ->timeout(15);
    }

    /**
     * Creates a Checkout Pro preference for a one-off product purchase.
     * The answer carries the init_point where the customer pays (Pix or card).
     */
    public function createPreference(Purchase $purchase, User $user, Product $product): array
    {
        // Some hosts set serialize_precision=17, which turns 49.9 into 49.899999999999999
        // in the JSON body. Mercado Pago rejects anything beyond two decimals.
        ini_set('serialize_precision', '-1');

        $returnUrl = route('checkout.finish', ['purchase' => $purchase->id]);

        return $this->client()->post('/checkout/preferences', [
            'items' => [[
                'id'          => (string) $product->id,
                'title'       => $product->title,
                'description' => $product->type->getLabel() . ' digital',
                'category_id' => 'learnings',
                'quantity'    => 1,
                'currency_id' => 'BRL',
                'unit_price'  => round((float) $purchase->amount, 2),
            ]],
            'payer'              => ['email' => $user->email],
            // The prefix identifies our purchases in the webhook.
            'external_reference' => 'purchase-' . $purchase->id,
            'back_urls'          => [
                'success' => $returnUrl,
                'pending' => $returnUrl,
                'failure' => $returnUrl,
            ],
            'auto_return'        => 'approved',
            'notification_url'   => route('webhooks.mercadopago'),
        ])->throw()->json();
    }

    public function getPayment(string|int $id): array
    {
        return $this->client()->get("/v1/payments/{$id}")->throw()->json();
    }

    /**
     * Id of the resource a webhook is about. PHP turns the dot of "data.id" into
     * an underscore in query strings, so both spellings are checked.
     */
    public function notificationId(Request $request): ?string
    {
        $id = $request->query->get('data_id')
            ?? $request->query->get('data.id')
            ?? $request->input('data.id');

        return filled($id) ? (string) $id : null;
    }

    /**
     * Validates the x-signature header: HMAC-SHA256 of "id:{data.id};request-id:{x-request-id};ts:{ts};".
     */
    public function hasValidSignature(Request $request): bool
    {
        $secret    = (string) config('services.mercadopago.webhook_secret');
        $header    = (string) $request->header('x-signature');
        $requestId = (string) $request->header('x-request-id');

        if ($secret === '' || $header === '') {
            return false;
        }

        $parts = [];

        foreach (explode(',', $header) as $chunk) {
            $pair = explode('=', trim($chunk), 2);

            if (count($pair) === 2) {
                $parts[$pair[0]] = $pair[1];
            }
        }

        if (! isset($parts['ts'], $parts['v1'])) {
            return false;
        }

        $dataId   = $this->notificationId($request);
        $manifest = ($dataId !== null ? 'id:' . strtolower($dataId) . ';' : '')
            . ($requestId !== '' ? 'request-id:' . $requestId . ';' : '')
            . 'ts:' . $parts['ts'] . ';';

        return hash_equals(hash_hmac('sha256', $manifest, $secret), $parts['v1']);
    }
}
