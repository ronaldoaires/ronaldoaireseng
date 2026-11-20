<?php

use App\Models\ProductFeedback;
use App\Services\AccountClosingService;
use App\Services\LicensedPdfService;
use Illuminate\Support\Facades\Storage;

it('removes the licensed name and private PDF when an account with purchases is closed', function () {
    Storage::fake('local');

    $user = makeBuyer(['name' => 'Ana Maria']);
    $product = makeProduct();
    $file = attachPdf($product);
    $purchase = grantPurchase($user, $product);
    ProductFeedback::create([
        'product_id' => $product->id,
        'user_id' => $user->id,
        'review_user_id' => $user->id,
        'type' => ProductFeedback::TYPE_REVIEW,
        'status' => ProductFeedback::STATUS_APPROVED,
        'display_name' => $user->name,
        'rating' => 5,
        'content' => 'Uma avaliação publicada que contém dados pessoais.',
        'approved_at' => now(),
    ]);
    $path = app(LicensedPdfService::class)->path($file, $purchase);

    expect(Storage::disk('local')->exists($path))->toBeTrue();

    expect(app(AccountClosingService::class)->close($user))->toBe(AccountClosingService::ANONYMIZED)
        ->and($purchase->fresh()->licensed_name)->toBeNull()
        ->and(ProductFeedback::count())->toBe(0)
        ->and(Storage::disk('local')->exists($path))->toBeFalse();
});
