<?php

use App\Models\Purchase;
use App\Services\PurchaseSyncService;
use Illuminate\Support\Facades\Http;

it('sends guests to the login before the checkout', function () {
    makeProduct(['slug' => 'concreto-armado']);

    $this->get('/comprar/concreto-armado')->assertRedirect(route('login'));
});

it('releases a free product straight into the library', function () {
    $user = makeBuyer();
    $product = makeProduct(['slug' => 'guia-gratis', 'price' => 0]);

    $this->actingAs($user)
        ->post('/comprar/guia-gratis', ['licensed_name' => $user->name])
        ->assertRedirect(route('account.library.show', ['product' => 'guia-gratis']));

    $purchase = Purchase::sole();

    expect($purchase->isPaid())->toBeTrue()
        ->and((float) $purchase->amount)->toBe(0.0)
        ->and($user->fresh()->owns($product))->toBeTrue();
});

it('asks for CPF and phone and sends paid products to Mercado Pago', function () {
    Http::fake([
        'api.mercadopago.com/checkout/preferences' => Http::response(['init_point' => 'https://mp.test/pay/1']),
    ]);

    $user = makeBuyer();
    makeProduct(['slug' => 'concreto-armado', 'price' => 79.90]);

    $this->actingAs($user)
        ->post('/comprar/concreto-armado', [])
        ->assertSessionHasErrors(['licensed_name', 'cpf', 'phone']);

    $this->actingAs($user)
        ->post('/comprar/concreto-armado', ['licensed_name' => 'Ana Maria', 'cpf' => '529.982.247-25', 'phone' => '(66) 99999-8888'])
        ->assertRedirect('https://mp.test/pay/1');

    $purchase = Purchase::sole();

    expect($purchase->status)->toBe('pending')
        ->and((float) $purchase->amount)->toBe(79.9)
        ->and($purchase->licensed_name)->toBe('Ana Maria')
        ->and($user->fresh()->name)->toBe('Ana Maria')
        ->and($user->fresh()->cpf)->toBe('52998224725');

    Http::assertSent(fn ($request) => $request['external_reference'] === 'purchase-'.$purchase->id
        && $request['items'][0]['unit_price'] === 79.9);
});

it('keeps the same payment link when the buyer returns before paying', function () {
    Http::preventStrayRequests();
    Http::fake([
        'api.mercadopago.com/checkout/preferences' => Http::response(['init_point' => 'https://mp.test/pay/1']),
    ]);

    $user = makeBuyer();
    $product = makeProduct(['slug' => 'concreto-armado', 'price' => 79.90]);

    $this->actingAs($user)
        ->post('/comprar/concreto-armado', ['licensed_name' => $user->name, 'cpf' => '529.982.247-25', 'phone' => '(66) 99999-8888'])
        ->assertRedirect('https://mp.test/pay/1');

    $purchase = Purchase::sole();

    expect($purchase->payment_url)->toBe('https://mp.test/pay/1');

    $this->get($product->url)->assertOk()->assertSee('Comprar agora');
    $this->get('/comprar/concreto-armado')->assertRedirect('https://mp.test/pay/1');
    $this->get(route('account.purchases'))->assertOk()->assertSee('Continuar pagamento');
    $this->get(route('checkout.finish', ['purchase' => $purchase->id]))->assertOk()->assertSee('Continuar pagamento');
    $this->post('/comprar/concreto-armado')->assertRedirect('https://mp.test/pay/1');

    expect(Purchase::count())->toBe(1);
    Http::assertSentCount(1);
});

it('keeps the payment link available after a rejected or cancelled attempt', function (string $remoteStatus, string $localStatus) {
    Http::preventStrayRequests();
    Http::fake([
        'api.mercadopago.com/checkout/preferences' => Http::response(['init_point' => 'https://mp.test/pay/1']),
    ]);

    $user = makeBuyer();
    $product = makeProduct(['slug' => 'concreto-armado', 'price' => 79.90]);

    $this->actingAs($user)
        ->post('/comprar/concreto-armado', ['licensed_name' => $user->name, 'cpf' => '529.982.247-25', 'phone' => '(66) 99999-8888'])
        ->assertRedirect('https://mp.test/pay/1');

    $purchase = Purchase::sole();

    app(PurchaseSyncService::class)->syncPayment([
        'id' => 123,
        'status' => $remoteStatus,
        'external_reference' => 'purchase-'.$purchase->id,
    ]);

    expect($purchase->fresh()->status)->toBe($localStatus)
        ->and($purchase->fresh()->payment_url)->toBe('https://mp.test/pay/1')
        ->and($user->fresh()->owns($product))->toBeFalse();

    $this->get($product->url)->assertOk()->assertSee('Comprar agora');
    $this->get('/comprar/concreto-armado')->assertRedirect('https://mp.test/pay/1');
    $this->get(route('account.purchases'))->assertOk()->assertSee('Pagar novamente');
    $this->post('/comprar/concreto-armado')->assertRedirect('https://mp.test/pay/1');

    expect(Purchase::count())->toBe(1);
    Http::assertSentCount(1);
})->with([
    'rejected' => ['rejected', 'failed'],
    'cancelled' => ['cancelled', 'cancelled'],
]);

it('creates a new checkout for an older failed order without a saved payment link', function () {
    Http::preventStrayRequests();
    Http::fake([
        'api.mercadopago.com/checkout/preferences' => Http::response(['init_point' => 'https://mp.test/pay/new']),
    ]);

    $user = makeBuyer();
    $product = makeProduct(['slug' => 'concreto-armado', 'price' => 79.90]);
    $purchase = grantPurchase($user, $product, 'failed');

    $this->actingAs($user)
        ->get('/comprar/concreto-armado')
        ->assertOk()
        ->assertSee('Ir para o pagamento');

    $this->post('/comprar/concreto-armado', ['licensed_name' => $user->name, 'cpf' => '529.982.247-25', 'phone' => '(66) 99999-8888'])
        ->assertRedirect('https://mp.test/pay/new');

    expect($purchase->fresh()->status)->toBe('pending')
        ->and($purchase->fresh()->payment_url)->toBe('https://mp.test/pay/new')
        ->and(Purchase::count())->toBe(1);

    Http::assertSentCount(1);
});

it('shows payment status instead of creating another checkout while confirmation is pending', function () {
    Http::preventStrayRequests();
    $purchaseId = null;
    Http::fake([
        'api.mercadopago.com/checkout/preferences' => Http::response(['init_point' => 'https://mp.test/pay/1']),
        'api.mercadopago.com/v1/payments/123' => function () use (&$purchaseId) {
            return Http::response([
                'id' => 123,
                'status' => 'pending',
                'external_reference' => 'purchase-'.$purchaseId,
            ]);
        },
    ]);

    $user = makeBuyer();
    $product = makeProduct(['slug' => 'concreto-armado', 'price' => 79.90]);

    $this->actingAs($user)
        ->post('/comprar/concreto-armado', ['licensed_name' => $user->name, 'cpf' => '529.982.247-25', 'phone' => '(66) 99999-8888'])
        ->assertRedirect('https://mp.test/pay/1');

    $purchase = Purchase::sole();
    $purchaseId = $purchase->id;

    app(PurchaseSyncService::class)->syncPayment([
        'id' => 123,
        'status' => 'pending',
        'external_reference' => 'purchase-'.$purchase->id,
    ]);

    $returnUrl = route('checkout.finish', ['purchase' => $purchase->id, 'payment_id' => '123']);

    $this->get('/comprar/concreto-armado')->assertRedirect($returnUrl);
    $this->post('/comprar/concreto-armado')->assertRedirect($returnUrl);
    $this->get(route('account.purchases'))->assertOk()->assertSee('Acompanhar pagamento');
    $this->get($returnUrl)->assertOk()->assertSee('Estamos confirmando seu pagamento');

    expect(Purchase::count())->toBe(1);
    Http::assertSentCount(2);
});

it('keeps the checkout button available when Mercado Pago cannot create a preference', function () {
    Http::preventStrayRequests();
    Http::fake([
        'api.mercadopago.com/checkout/preferences' => Http::response([], 500),
    ]);

    $user = makeBuyer();
    makeProduct(['slug' => 'concreto-armado', 'price' => 79.90]);

    $this->actingAs($user)
        ->post('/comprar/concreto-armado', ['licensed_name' => $user->name, 'cpf' => '529.982.247-25', 'phone' => '(66) 99999-8888'])
        ->assertSessionHasErrors('payment');

    $this->get('/comprar/concreto-armado')->assertOk()->assertSee('Ir para o pagamento');

    expect(Purchase::count())->toBe(0);
    Http::assertSentCount(1);
});

it('requires the terms when the accepted version is outdated', function () {
    $user = makeBuyer(['terms_version' => 'old']);
    makeProduct(['slug' => 'guia-gratis', 'price' => 0]);

    $this->actingAs($user)->post('/comprar/guia-gratis')->assertSessionHasErrors('terms');

    $this->actingAs($user)->post('/comprar/guia-gratis', ['licensed_name' => $user->name, 'terms' => '1'])->assertRedirect();

    expect($user->fresh()->terms_version)->toBe(config('legal.version'));
});

it('sends a buyer who already owns the product to the library', function () {
    $user = makeBuyer();
    $product = makeProduct(['slug' => 'concreto-armado']);
    grantPurchase($user, $product);

    $this->actingAs($user)
        ->get('/comprar/concreto-armado')
        ->assertRedirect(route('account.library.show', ['product' => 'concreto-armado']));
});

it('marks the purchase as paid from the Mercado Pago webhook', function () {
    config(['services.mercadopago.webhook_secret' => 'segredo']);

    $user = makeBuyer();
    $product = makeProduct();
    $purchase = grantPurchase($user, $product, 'pending');

    Http::fake([
        'api.mercadopago.com/v1/payments/123' => Http::response([
            'id' => 123,
            'status' => 'approved',
            'external_reference' => 'purchase-'.$purchase->id,
            'payment_method_id' => 'pix',
            'payment_type_id' => 'bank_transfer',
            'transaction_amount' => 49.9,
            'currency_id' => 'BRL',
            'date_approved' => now()->toAtomString(),
        ]),
    ]);

    $ts = (string) time();
    $requestId = 'req-1';
    $hash = hash_hmac('sha256', "id:123;request-id:{$requestId};ts:{$ts};", 'segredo');

    $this->withHeaders(['x-signature' => "ts={$ts},v1={$hash}", 'x-request-id' => $requestId])
        ->postJson('/webhooks/mercadopago?data.id=123&type=payment', ['type' => 'payment', 'data' => ['id' => '123']])
        ->assertOk();

    expect($purchase->fresh()->isPaid())->toBeTrue()
        ->and($purchase->fresh()->payment_method)->toBe('pix');
});

it('does not release an approved payment for a different amount or currency', function (float $amount, string $currency) {
    $user = makeBuyer();
    $product = makeProduct();
    $purchase = grantPurchase($user, $product, 'pending');

    app(PurchaseSyncService::class)->syncPayment([
        'id' => 456,
        'status' => 'approved',
        'external_reference' => 'purchase-'.$purchase->id,
        'transaction_amount' => $amount,
        'currency_id' => $currency,
    ]);

    expect($purchase->fresh()->isPaid())->toBeFalse()
        ->and($user->fresh()->owns($product))->toBeFalse();
})->with([
    'lower amount' => [9.90, 'BRL'],
    'wrong currency' => [49.90, 'USD'],
]);

it('rejects webhooks without a valid signature', function () {
    config(['services.mercadopago.webhook_secret' => 'segredo']);

    $this->postJson('/webhooks/mercadopago', ['type' => 'payment'])->assertUnauthorized();
});

it('asks Mercado Pago to retry when a payment cannot yet be fetched', function () {
    config(['services.mercadopago.webhook_secret' => 'segredo']);

    Http::fake([
        'api.mercadopago.com/v1/payments/123' => Http::response([], 404),
    ]);

    $ts = (string) time();
    $requestId = 'req-retry';
    $hash = hash_hmac('sha256', "id:123;request-id:{$requestId};ts:{$ts};", 'segredo');

    $this->withHeaders(['x-signature' => "ts={$ts},v1={$hash}", 'x-request-id' => $requestId])
        ->postJson('/webhooks/mercadopago?data.id=123&type=payment', ['type' => 'payment', 'data' => ['id' => '123']])
        ->assertInternalServerError();
});
