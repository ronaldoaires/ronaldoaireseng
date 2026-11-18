<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Models\ProductFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Streams a purchased file from the private disk. Files are never public:
 * every download checks the purchase and is logged.
 */
class FileDownloadController extends Controller
{
    public function __invoke(Request $request, int $file): StreamedResponse
    {
        $file = ProductFile::with('product')->findOrFail($file);

        abort_unless($file->product && $request->user()->owns($file->product), 404);

        $disk = Storage::disk($file->disk);

        abort_unless($disk->exists($file->path), 404);

        $file->downloads()->create([
            'user_id'       => $request->user()->id,
            'ip_address'    => $request->ip(),
            'downloaded_at' => now(),
        ]);

        return $disk->download($file->path, $file->original_name, [
            'Cache-Control' => 'private, no-store',
        ]);
    }
}
