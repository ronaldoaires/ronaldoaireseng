<?php

namespace App\Http\Controllers\Site;

use App\Enums\ServiceArea;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Project;
use App\Models\Service;
use App\Support\StructuredData;
use Illuminate\Contracts\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        $services = Service::active()->get();

        $jsonLd = StructuredData::graph(
            [
                '@type' => 'WebSite',
                'name'  => config('app.name'),
                'url'   => url('/'),
            ],
            StructuredData::organization(),
        );

        $products = Product::published()->ordered()->get();

        return view('site.home', [
            'jsonLd'   => $jsonLd,
            // Featured first; the section hides itself when there is nothing published.
            'products' => $products->sortByDesc('is_featured')->take(6)->values(),
            'owned'    => $products->isNotEmpty() ? (auth()->user()?->ownedProductIds() ?? []) : [],
            // Only areas that have something to show, in the enum order.
            'areas'    => collect(ServiceArea::cases())
                ->mapWithKeys(fn (ServiceArea $area) => [$area->value => $services->filter(fn (Service $s) => $s->area === $area)->values()])
                ->filter(fn ($group) => $group->isNotEmpty()),
            'projects' => Project::active()
                ->featured()
                ->with('service')
                ->whereHas('service', fn ($query) => $query->where('is_active', true))
                ->limit(6)
                ->get(),
        ]);
    }
}
