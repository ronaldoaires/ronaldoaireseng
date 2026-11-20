<?php

namespace App\Http\Controllers\Site;

use App\Enums\ProductType;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductChapter;
use App\Support\StructuredData;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(string $type): View
    {
        $productType = ProductType::fromSegment($type);

        $products = Product::published()
            ->ofType($productType)
            ->ordered()
            ->get();

        $url = route('products.index', ['type' => $type]);

        $jsonLd = StructuredData::graph(
            StructuredData::breadcrumbs([
                ['name' => 'Início', 'url' => route('home')],
                ['name' => $productType->pluralLabel()],
            ]),
            StructuredData::itemList(
                $productType->pluralLabel(),
                $url,
                $productType->headline(),
                $products->map(fn (Product $product) => ['name' => $product->title, 'url' => $product->url])->all(),
            ),
        );

        return view('site.products.index', [
            'type' => $productType,
            'products' => $products,
            'jsonLd' => $jsonLd,
            'owned' => $this->ownedIds($products->isNotEmpty()),
        ]);
    }

    public function show(Request $request, string $type, Product $product): View|RedirectResponse
    {
        abort_unless($product->isPublished(), 404);

        // Old or mistyped prefix (/planilhas/<ebook>): one canonical URL per product.
        if ($product->type->segment() !== $type) {
            return redirect($product->url, 301);
        }

        $product->load(['files', 'previewChapters']);
        $product->loadCount('chapters');
        $reviews = $product->approvedReviews()->latest('approved_at')->paginate(6, ['*'], 'avaliacoes')->fragment('avaliacoes');
        $reviewCount = $product->approvedReviews()->count();
        $ratingAverage = $reviewCount > 0 ? (float) $product->approvedReviews()->avg('rating') : null;

        // Visits from the admin do not count.
        if (! $request->user()?->isAdmin()) {
            $product->incrementViews();
        }

        $jsonLd = StructuredData::graph(
            StructuredData::breadcrumbs([
                ['name' => 'Início', 'url' => route('home')],
                ['name' => $product->type->pluralLabel(), 'url' => route('products.index', ['type' => $type])],
                ['name' => $product->title],
            ]),
            StructuredData::product($product, $reviewCount, $ratingAverage, $reviews->items()),
            StructuredData::faq($product->faq),
        );

        return view('site.products.show', [
            'product' => $product,
            'jsonLd' => $jsonLd,
            'owns' => (bool) $request->user()?->owns($product),
            'reviews' => $reviews,
            'reviewCount' => $reviewCount,
            'ratingAverage' => $ratingAverage,
            'related' => Product::published()
                ->whereKeyNot($product->id)
                ->ordered()
                ->limit(3)
                ->get(),
        ]);
    }

    /**
     * Free sample chapter, readable by anyone. Buyers are sent to the full reader.
     */
    public function preview(Request $request, string $type, Product $product, ProductChapter $chapter): View|RedirectResponse
    {
        abort_unless($product->isPublished() && $product->hasOnlineReader() && $chapter->is_preview, 404);

        if ($product->type->segment() !== $type) {
            return redirect(route('products.preview', [
                'type' => $product->type->segment(),
                'product' => $product->slug,
                'chapter' => $chapter->slug,
            ]), 301);
        }

        if ($request->user()?->owns($product)) {
            return redirect()->route('account.reader', ['product' => $product->slug, 'chapter' => $chapter->slug]);
        }

        return view('site.products.preview', [
            'product' => $product,
            'chapter' => $chapter,
            'chapters' => $product->chapters()->get(['id', 'product_id', 'title', 'slug', 'is_preview']),
        ]);
    }

    /**
     * @return array<int, int>
     */
    private function ownedIds(bool $needed): array
    {
        return $needed ? (auth()->user()?->ownedProductIds() ?? []) : [];
    }
}
