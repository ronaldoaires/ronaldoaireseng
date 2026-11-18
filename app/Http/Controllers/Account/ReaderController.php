<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductChapter;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Online (digital) version of an ebook, for buyers only.
 */
class ReaderController extends Controller
{
    public function __invoke(Request $request, Product $product, ?ProductChapter $chapter = null): View|RedirectResponse
    {
        $purchase = LibraryController::purchaseOrFail($request, $product);

        abort_unless($product->hasOnlineReader(), 404);

        $chapters = $product->chapters()->get(['id', 'product_id', 'title', 'slug', 'sort_order']);

        abort_if($chapters->isEmpty(), 404);

        // No chapter in the URL: continue where the reader stopped, or start at the first one.
        if (! $chapter) {
            $target = $chapters->firstWhere('id', $purchase->last_chapter_id) ?? $chapters->first();

            return redirect()->route('account.reader', ['product' => $product->slug, 'chapter' => $target->slug]);
        }

        $index = $chapters->search(fn (ProductChapter $item) => $item->id === $chapter->id);

        $purchase->forceFill([
            'last_chapter_id' => $chapter->id,
            'last_read_at'    => now(),
        ])->saveQuietly();

        return view('site.account.reader', [
            'product'  => $product,
            'chapter'  => $chapter,
            'chapters' => $chapters,
            'previous' => $index > 0 ? $chapters[$index - 1] : null,
            'next'     => $chapters[$index + 1] ?? null,
            'position' => $index + 1,
        ]);
    }
}
