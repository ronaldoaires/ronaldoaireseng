<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\LicensedPdfService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BookPdfController extends Controller
{
    public function __invoke(Request $request, Product $product, LicensedPdfService $licensedPdf): StreamedResponse
    {
        $purchase = LibraryController::purchaseOrFail($request, $product);

        abort_unless($product->hasOnlineReader(), 404);

        $file = $product->files()->where('disk', 'local')->where('original_name', 'like', '%.pdf')->firstOrFail();
        $disk = Storage::disk($file->disk);

        abort_unless($disk->exists($file->path), 404);

        $path = $licensedPdf->path($file, $purchase);

        return $disk->response($path, $file->original_name, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$file->original_name.'"',
            'Cache-Control' => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
