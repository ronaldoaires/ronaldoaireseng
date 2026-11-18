<?php

namespace App\Support;

use Illuminate\Support\Collection;

/**
 * Main navigation items from config/site.php, shared by the header and the
 * mobile bottom bar, with their URL and active state resolved once.
 */
class SiteNav
{
    /**
     * @return Collection<int, array<string, mixed>>
     */
    public static function items(bool $bottomOnly = false): Collection
    {
        return collect(config('site.nav'))
            ->where('enabled', true)
            ->when($bottomOnly, fn (Collection $items) => $items->where('bottom', true))
            ->map(fn (array $item) => $item + [
                'url'       => route($item['route'], $item['params'] ?? []),
                'is_active' => static::isActive($item),
            ])
            ->values();
    }

    /**
     * "active" widens the highlight to a whole section; "match" narrows a
     * shared route (products.*) to one {type} segment (ebooks or planilhas).
     */
    private static function isActive(array $item): bool
    {
        if (! request()->routeIs($item['active'] ?? $item['route'])) {
            return false;
        }

        return ! isset($item['match']) || request()->route('type') === $item['match'];
    }
}
