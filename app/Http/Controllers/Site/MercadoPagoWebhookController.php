<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Services\MercadoPagoService;
use App\Services\PurchaseSyncService;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class MercadoPagoWebhookController extends Controller
{
    public function __invoke(Request $request, MercadoPagoService $mp, PurchaseSyncService $purchases): Response
    {
        abort_unless($mp->hasValidSignature($request), 401);

        $id   = $mp->notificationId($request);
        $type = $request->input('type') ?? $request->query('type');

        // One-off purchases only: every other notification type is acknowledged and ignored.
        if ($id === null || $type !== 'payment') {
            return response('ok');
        }

        try {
            $purchases->syncPayment($mp->getPayment($id));
        } catch (RequestException $e) {
            // The panel's "simulate notification" sends ids that do not exist: nothing to retry.
            if ($e->response->status() === 404) {
                Log::info("Mercado Pago webhook: payment {$id} not found.");

                return response('ok');
            }

            // Any other failure answers 500, so Mercado Pago retries later.
            throw $e;
        }

        return response('ok');
    }
}
