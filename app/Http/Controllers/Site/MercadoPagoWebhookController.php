<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Services\MercadoPagoService;
use App\Services\PurchaseSyncService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class MercadoPagoWebhookController extends Controller
{
    public function __invoke(Request $request, MercadoPagoService $mp, PurchaseSyncService $purchases): Response
    {
        abort_unless($mp->hasValidSignature($request), 401);

        $id = $mp->notificationId($request);
        $type = $request->input('type') ?? $request->query('type');

        // One-off purchases only: every other notification type is acknowledged and ignored.
        if ($id === null || $type !== 'payment') {
            return response('ok');
        }

        $purchases->syncPayment($mp->getPayment($id));

        return response('ok');
    }
}
