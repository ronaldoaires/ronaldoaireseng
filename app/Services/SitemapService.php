<?php

namespace App\Services;

use App\Enums\ProductType;
use App\Enums\ServiceArea;
use App\Models\Product;
use App\Models\ProductChapter;
use App\Models\Project;
use App\Models\Service;
use Illuminate\Support\Carbon;

/**
 * Builds the list of public, indexable URLs for sitemap.xml.
 * Mirrors the noindex rules of the pages: only published and active records.
 */
class SitemapService
{
    /**
     * @return array<int, array{loc: string, lastmod: ?string}>
     */
    public function entries(): array
    {
        $entries = [];

        $entries[] = ['loc' => route('home'), 'lastmod' => null];
        $entries[] = ['loc' => route('legal.terms'), 'lastmod' => null];
        $entries[] = ['loc' => route('legal.privacy'), 'lastmod' => null];

        $entries[] = ['loc' => route('legal.license'), 'lastmod' => null];

        $products = Product::published()->get(['id', 'type', 'slug', 'updated_at', 'has_reader']);

        foreach (ProductType::cases() as $type) {
            $ofType = $products->filter(fn (Product $product) => $product->type === $type);

            if ($ofType->isEmpty()) {
                continue;
            }

            $entries[] = [
                'loc'     => route('products.index', ['type' => $type->segment()]),
                'lastmod' => $this->iso($ofType->max('updated_at')),
            ];
        }

        $previews = ProductChapter::query()
            ->where('is_preview', true)
            ->whereIn('product_id', $products->where('has_reader', true)->pluck('id'))
            ->get(['product_id', 'slug', 'updated_at'])
            ->groupBy('product_id');

        foreach ($products as $product) {
            $entries[] = [
                'loc'     => $product->url,
                'lastmod' => $this->iso($product->updated_at),
            ];

            foreach ($previews->get($product->id, []) as $chapter) {
                $entries[] = [
                    'loc'     => route('products.preview', [
                        'type'    => $product->type->segment(),
                        'product' => $product->slug,
                        'chapter' => $chapter->slug,
                    ]),
                    'lastmod' => $this->iso($chapter->updated_at),
                ];
            }
        }

        array_push($entries, ...$this->showcaseEntries());

        return $entries;
    }

    /**
     * Services hub, area pages, service pages, portfolio and projects.
     * Same rules as the pages: only active records, and an area only when
     * it has at least one active service.
     *
     * @return array<int, array{loc: string, lastmod: ?string}>
     */
    private function showcaseEntries(): array
    {
        $services = Service::active()->get(['id', 'area', 'slug', 'updated_at', 'sort_order', 'name']);

        if ($services->isEmpty()) {
            return [];
        }

        $entries = [[
            'loc'     => route('services.index'),
            'lastmod' => $this->iso($services->max('updated_at')),
        ]];

        foreach (ServiceArea::cases() as $area) {
            $inArea = $services->filter(fn (Service $service) => $service->area === $area);

            if ($inArea->isNotEmpty()) {
                $entries[] = [
                    'loc'     => route('services.area', ['area' => $area->slug()]),
                    'lastmod' => $this->iso($inArea->max('updated_at')),
                ];
            }
        }

        foreach ($services as $service) {
            $entries[] = [
                'loc'     => route('services.show', ['area' => $service->area->slug(), 'service' => $service->slug]),
                'lastmod' => $this->iso($service->updated_at),
            ];
        }

        $projects = Project::query()
            ->where('is_active', true)
            ->whereIn('service_id', $services->pluck('id'))
            ->get(['id', 'slug', 'updated_at']);

        if ($projects->isNotEmpty()) {
            $entries[] = [
                'loc'     => route('portfolio.index'),
                'lastmod' => $this->iso($projects->max('updated_at')),
            ];

            foreach ($projects as $project) {
                $entries[] = [
                    'loc'     => route('portfolio.show', ['project' => $project->slug]),
                    'lastmod' => $this->iso($project->updated_at),
                ];
            }
        }

        return $entries;
    }

    /**
     * W3C datetime (with the app timezone offset), or null when there is no date.
     */
    private function iso(mixed $date): ?string
    {
        return $date ? Carbon::parse($date)->toAtomString() : null;
    }
}
