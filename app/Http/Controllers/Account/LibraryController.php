<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Purchase;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class LibraryController extends Controller
{
    public function index(Request $request): View
    {
        $purchases = $request->user()
            ->purchases()
            ->paid()
            ->with(['product' => fn ($query) => $query->withCount(['files', 'chapters']), 'lastChapter'])
            ->latest('paid_at')
            ->get();

        return view('site.account.library.index', ['purchases' => $purchases]);
    }

    public function show(Request $request, Product $product): View
    {
        $purchase = $this->purchaseOrFail($request, $product);

        $product->load(['files', 'chapters:id,product_id,title,slug,is_preview,sort_order']);
        $purchase->load('lastChapter');

        // Last download of each file, so the buyer sees what is new.
        $lastDownloads = $request->user()
            ->productDownloads()
            ->whereIn('product_file_id', $product->files->pluck('id'))
            ->selectRaw('product_file_id, max(downloaded_at) as last_at')
            ->groupBy('product_file_id')
            ->pluck('last_at', 'product_file_id');

        return view('site.account.library.show', [
            'product'       => $product,
            'purchase'      => $purchase,
            'lastDownloads' => $lastDownloads,
        ]);
    }

    /**
     * The paid purchase that gives this user access, or a 404 (never reveal
     * whether a product exists to someone who does not own it).
     */
    public static function purchaseOrFail(Request $request, Product $product): Purchase
    {
        return $request->user()
            ->purchases()
            ->paid()
            ->where('product_id', $product->id)
            ->firstOr(fn () => abort(404));
    }
}
