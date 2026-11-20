<?php

use App\Models\Purchase;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialUser;

it('uses the verified Google name for a new customer account', function () {
    Socialite::fake('google', SocialUser::fake([
        'id' => 'google-customer-1',
        'name' => 'Ana Maria Silva',
        'email' => 'ana@example.com',
        'email_verified' => true,
    ]));

    $this->get(route('social.callback', ['provider' => 'google']))->assertRedirect(route('home'));

    $user = User::where('email', 'ana@example.com')->sole();

    expect($user->name)->toBe('Ana Maria Silva')
        ->and($user->hasVerifiedEmail())->toBeTrue();

    $this->assertAuthenticatedAs($user);
});

it('keeps the account name when a verified customer links Google login', function () {
    $user = makeBuyer(['name' => 'Ana da Silva', 'email' => 'ana@example.com']);

    Socialite::fake('google', SocialUser::fake([
        'id' => 'google-customer-2',
        'name' => 'Ana M. Silva',
        'email' => 'ana@example.com',
        'email_verified' => true,
    ]));

    $this->get(route('social.callback', ['provider' => 'google']))->assertRedirect(route('home'));

    expect($user->fresh()->name)->toBe('Ana da Silva');
    $this->assertAuthenticatedAs($user);
});

it('rejects a Google account without a verified email', function () {
    Socialite::fake('google', SocialUser::fake([
        'email' => 'ana@example.com',
        'email_verified' => false,
    ]));

    $this->get(route('social.callback', ['provider' => 'google']))
        ->assertRedirect(route('login'))
        ->assertSessionHasErrors('social');

    $this->assertGuest();
});

it('uses the confirmed checkout name for a Google customer ebook license', function () {
    Socialite::fake('google', SocialUser::fake([
        'id' => 'google-customer-3',
        'name' => 'Ana M. Silva',
        'email' => 'ana@example.com',
        'email_verified' => true,
    ]));

    $this->get(route('social.callback', ['provider' => 'google']))->assertRedirect(route('home'));

    $product = makeProduct(['slug' => 'livro-social']);
    $this->get(route('checkout.show', ['product' => $product->slug]))
        ->assertOk()
        ->assertSee('Ana M. Silva');

    Http::fake(['api.mercadopago.com/checkout/preferences' => Http::response(['init_point' => 'https://mp.test/pay/1'])]);

    $this->post(route('checkout.store', ['product' => $product->slug]), [
        'licensed_name' => 'Ana Maria Silva',
        'cpf' => '529.982.247-25',
        'phone' => '(66) 99999-8888',
        'terms' => '1',
    ])->assertRedirect('https://mp.test/pay/1');

    expect(Purchase::sole()->licensed_name)->toBe('Ana Maria Silva')
        ->and(User::sole()->name)->toBe('Ana Maria Silva');
});
