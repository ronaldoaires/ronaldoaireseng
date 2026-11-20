<?php

use App\Enums\ProductType;
use App\Models\Product;
use App\Models\ProductFile;
use App\Models\Purchase;
use App\Models\User;
use App\Support\BotTrap;
use Illuminate\Support\Facades\Storage;

/*
|--------------------------------------------------------------------------
| Shared helpers for the feature tests
|--------------------------------------------------------------------------
|
| Loaded once from tests/Pest.php. Pest helpers are global functions, so
| every name here must stay unique across the whole test suite.
|
*/

/**
 * Form token of a public form rendered 10 seconds ago (passes the time trap).
 */
function formToken(): string
{
    test()->travel(-10)->seconds();
    $token = BotTrap::token();
    test()->travelBack();

    return $token;
}

function makeBuyer(array $overrides = []): User
{
    return User::factory()->create(array_merge([
        'role' => User::ROLE_USER,
        'terms_version' => config('legal.version'),
    ], $overrides));
}

/**
 * Published product, an ebook with online reader unless overridden.
 */
function makeProduct(array $overrides = []): Product
{
    static $n = 0;
    $n++;

    return Product::create(array_merge([
        'type' => ProductType::Ebook,
        'title' => "Ebook {$n}",
        'slug' => "ebook-{$n}",
        'summary' => 'Resumo do material.',
        'price' => 49.90,
        'has_reader' => true,
        'is_published' => true,
    ], $overrides));
}

function grantPurchase(User $user, Product $product, string $status = 'paid'): Purchase
{
    return Purchase::create([
        'user_id' => $user->id,
        'product_id' => $product->id,
        'amount' => $product->price,
        'licensed_name' => $user->name,
        'status' => $status,
        'paid_at' => $status === 'paid' ? now() : null,
    ]);
}

/**
 * Stores a fake PDF on the (faked) private disk and registers it for the product.
 * The test must call Storage::fake('local') first.
 */
function attachPdf(Product $product): ProductFile
{
    $path = "products/files/{$product->slug}.pdf";

    $pdf = new FPDF;
    $pdf->AddPage();
    $pdf->SetFont('Helvetica', '', 12);
    $pdf->Cell(0, 10, 'Primeira pagina do ebook');
    $pdf->AddPage();
    $pdf->Cell(0, 10, 'Segunda pagina do ebook');

    Storage::disk('local')->put($path, $pdf->Output('S'));

    return $product->files()->create([
        'label' => 'Ebook em PDF',
        'disk' => 'local',
        'path' => $path,
        'original_name' => "{$product->slug}.pdf",
    ]);
}
