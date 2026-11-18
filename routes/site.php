<?php

use App\Enums\ProductType;
use App\Http\Controllers\Site\CheckoutController;
use App\Http\Controllers\Site\HomeController;
use App\Http\Controllers\Site\MercadoPagoWebhookController;
use App\Http\Controllers\Site\ProductController;
use App\Http\Controllers\Site\SitemapController;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');

// Engineering services, portfolio and contact (/servicos, /portfolio, /contato).
require base_path('routes/showcase.php');

// Store: /ebooks, /ebooks/{slug}, /planilhas, /planilhas/{slug}.
// The first segment comes from App\Enums\ProductType, so a new product type
// only needs a new enum case.
$types = ProductType::segmentPattern();

Route::get('/{type}', [ProductController::class, 'index'])
    ->where('type', $types)
    ->name('products.index');

Route::get('/{type}/{product:slug}', [ProductController::class, 'show'])
    ->where('type', $types)
    ->name('products.show');

// Free sample chapters of an ebook (public, indexable).
Route::get('/{type}/{product:slug}/amostra/{chapter:slug}', [ProductController::class, 'preview'])
    ->where('type', $types)
    ->scopeBindings()
    ->name('products.preview');

// Checkout: needs a logged-in, verified user. Guests are sent to the login and
// come back here afterwards (intended URL).
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/comprar/{product:slug}', [CheckoutController::class, 'show'])->name('checkout.show');

    Route::post('/comprar/{product:slug}', [CheckoutController::class, 'store'])
        ->middleware('throttle:10,1')
        ->name('checkout.store');

    Route::get('/compra/{purchase}/retorno', [CheckoutController::class, 'finish'])
        ->whereNumber('purchase')
        ->name('checkout.finish');
});

// Called by Mercado Pago's servers: no session and no CSRF token. The x-signature header is the protection.
Route::post('/webhooks/mercadopago', MercadoPagoWebhookController::class)
    ->withoutMiddleware([PreventRequestForgery::class])
    ->middleware('throttle:120,1')
    ->name('webhooks.mercadopago');

Route::view('/termos-de-uso', 'site.legal.terms')->name('legal.terms');
Route::view('/politica-de-privacidade', 'site.legal.privacy')->name('legal.privacy');
Route::view('/licenca-de-uso', 'site.legal.license')->name('legal.license');
