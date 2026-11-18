<?php

use App\Models\Purchase;
use Illuminate\Support\Facades\Http;

it('sends guests to the login before the checkout', function () {
    makeProduct(['slug' => 'concreto-armado']);

    $this->get('/comprar/concreto-armado')->assertRedirect(route('login'));
});

it('releases a free product straight into the library', function () {
    $user    = makeBuyer();
    $product = makeProduct(['slug' => 'guia-gratis', 'price' => 0]);

    $this->actingAs($user)
        ->post('/comprar/guia-gratis')
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
        ->assertSessionHasErrors(['cpf', 'phone']);

    $this->actingAs($user)
        ->post('/comprar/concreto-armado', ['cpf' => '529.982.247-25', 'phone' => '(66) 99999-8888'])
        ->assertRedirect('https://mp.test/pay/1');

    $purchase = Purchase::sole();

    expect($purchase->status)->toBe('pending')
        ->and((float) $purchase->amount)->toBe(79.9)
        ->and($user->fresh()->cpf)->toBe('52998224725');

    Http::assertSent(fn ($request) => $request['external_reference'] === 'purchase-' . $purchase->id
        && $request['items'][0]['unit_price'] === 79.9);
});

it('requires the terms when the accepted version is outdated', function () {
    $user = makeBuyer(['terms_version' => 'old']);
    makeProduct(['slug' => 'guia-gratis', 'price' => 0]);

    $this->actingAs($user)->post('/comprar/guia-gratis')->assertSessionHasErrors('terms');

    $this->actingAs($user)->post('/comprar/guia-gratis', ['terms' => '1'])->assertRedirect();

    expect($user->fresh()->terms_version)->toBe(config('legal.version'));
});

it('sends a buyer who already owns the product to the library', function () {
    $user    = makeBuyer();
    $product = makeProduct(['slug' => 'concreto-armado']);
    grantPurchase($user, $product);

    $this->actingAs($user)
        ->get('/comprar/concreto-armado')
        ->assertRedirect(route('account.library.show', ['product' => 'concreto-armado']));
});

it('marks the purchase as paid from the Mercado Pago webhook', function () {
    config(['services.mercadopago.webhook_secret' => 'segredo']);

    $user     = makeBuyer();
    $product  = makeProduct();
    $purchase = grantPurchase($user, $product, 'pending');

    Http::fake([
        'api.mercadopago.com/v1/payments/123' => Http::response([
            'id'                 => 123,
            'status'             => 'approved',
            'external_reference' => 'purchase-' . $purchase->id,
            'payment_method_id'  => 'pix',
            'payment_type_id'    => 'bank_transfer',
            'transaction_amount' => 49.9,
            'date_approved'      => now()->toAtomString(),
        ]),
    ]);

    $ts        = (string) time();
    $requestId = 'req-1';
    $hash      = hash_hmac('sha256', "id:123;request-id:{$requestId};ts:{$ts};", 'segredo');

    $this->withHeaders(['x-signature' => "ts={$ts},v1={$hash}", 'x-request-id' => $requestId])
        ->postJson('/webhooks/mercadopago?data.id=123&type=payment', ['type' => 'payment', 'data' => ['id' => '123']])
        ->assertOk();

    expect($purchase->fresh()->isPaid())->toBeTrue()
        ->and($purchase->fresh()->payment_method)->toBe('pix');
});

it('rejects webhooks without a valid signature', function () {
    config(['services.mercadopago.webhook_secret' => 'segredo']);

    $this->postJson('/webhooks/mercadopago', ['type' => 'payment'])->assertUnauthorized();
});
