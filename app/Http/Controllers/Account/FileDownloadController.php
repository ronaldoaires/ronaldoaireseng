<?php

namespace App\Http\Controllers\Account;

use App\Enums\ProductType;
use App\Http\Controllers\Controller;
use App\Models\ProductFile;
use App\Services\LicensedPdfService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Streams a purchased file from the private disk. Files are never public:
 * every download checks the purchase and is logged.
 */
class FileDownloadController extends Controller
{
    public function __invoke(Request $request, int $file, LicensedPdfService $licensedPdf): StreamedResponse
    {
        $file = ProductFile::with('product')->findOrFail($file);

        abort_unless($file->product, 404);

        $purchase = LibraryController::purchaseOrFail($request, $file->product);

        $disk = Storage::disk($file->disk);

        abort_unless($disk->exists($file->path), 404);

        $path = $file->product->type === ProductType::Ebook && $file->extension() === 'pdf'
            ? $licensedPdf->path($file, $purchase)
            : $file->path;

        $file->downloads()->create([
            'user_id' => $request->user()->id,
            'ip_address' => $request->ip(),
            'downloaded_at' => now(),
        ]);

        return $disk->download($path, $file->original_name, [
            'Cache-Control' => 'private, no-store',
        ]);
    }
}
