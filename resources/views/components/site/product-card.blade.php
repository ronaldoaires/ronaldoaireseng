@props(['product', 'owned' => false, 'headingLevel' => 'h3'])

@php
// Technical standard shown as a tag: the "Norma" row of the product's spec sheet.
$standard = collect($product->specs ?? [])
    ->first(fn ($row) => \Illuminate\Support\Str::lower(trim($row['label'] ?? '')) === 'norma')['value'] ?? null;
@endphp

{{-- Whole card is one link (the title's ::after covers the card), so the tap
     target is large on phones and screen readers hear a single link. --}}
<article class="group relative flex flex-col overflow-hidden rounded-[1.5rem] bg-surface p-2 ring-1 ring-line transition hover:-translate-y-0.5 hover:shadow-[0_18px_40px_-18px_rgb(91_92_240/0.45)] hover:ring-primary/30">
    <div class="relative aspect-[3/4] overflow-hidden rounded-[1.1rem] bg-canvas">
        @if ($product->cover_thumb_url)
            <img
                src="{{ $product->cover_thumb_url }}"
                alt=""
                width="300"
                height="400"
                loading="lazy"
                decoding="async"
                class="absolute inset-0 size-full object-cover">
        @elseif ($fallbacks = config('showcase.renders.product_fallbacks'))
            {{-- No cover yet: an ebook render keeps the grid from showing empty boxes. --}}
            <img
                src="{{ asset($fallbacks[$product->id % count($fallbacks)]) }}"
                alt=""
                width="300"
                height="400"
                loading="lazy"
                decoding="async"
                class="absolute inset-0 size-full object-cover">
        @else
            <span class="absolute inset-0 flex items-center justify-center text-subtle">
                @svg('heroicon-o-' . $product->type->iconName(), 'size-12')
            </span>
        @endif

        <span class="absolute left-3 top-3 inline-flex items-center gap-1 rounded-full bg-surface/95 px-2.5 py-1 text-xs font-semibold text-ink shadow-sm">
            @svg('heroicon-o-' . $product->type->iconName(), 'size-4 text-primary')
            {{ $product->type->getLabel() }}
        </span>

        @if ($product->discountPercent())
            <span class="absolute right-3 top-3 rounded-full bg-accent px-2.5 py-1 text-xs font-bold text-brand-950">
                -{{ $product->discountPercent() }}%
            </span>
        @endif
    </div>

    <div class="flex flex-1 flex-col p-3 pt-4">
        @if ($standard)
            <p class="mb-2 inline-flex w-fit items-center gap-1.5 rounded-full bg-primary-soft px-2.5 py-1 text-xs font-semibold text-on-primary-soft">
                @svg('heroicon-o-scale', 'size-3.5', ['aria-hidden' => 'true'])
                {{ $standard }}
            </p>
        @endif
        <{{ $headingLevel }} class="font-display text-xl font-bold leading-tight text-ink">
            <a href="{{ $product->url }}" class="after:absolute after:inset-0 focus:outline-none focus-visible:after:rounded-card focus-visible:after:ring-2 focus-visible:after:ring-primary">
                {{ $product->title }}
            </a>
        </{{ $headingLevel }}>


        <p class="mt-1.5 line-clamp-2 text-sm text-muted">{{ $product->subtitle ?: $product->summary }}</p>

        <div class="mt-auto flex items-end justify-between gap-2 pt-4">
            @if ($owned)
                <x-ui.badge tone="success">Na sua biblioteca</x-ui.badge>
            @elseif ((float) $product->price <= 0)
                <span class="text-lg font-bold text-success">Grátis</span>
            @else
                <p class="leading-tight">
                    @if ($product->hasDiscount())
                        <span class="block text-xs text-subtle line-through">{{ $product->formattedCompareAtPrice() }}</span>
                    @endif
                    <span class="whitespace-nowrap text-lg font-bold text-ink">{{ $product->formattedPrice() }}</span>
                </p>
            @endif

            <span class="inline-flex shrink-0 items-center gap-1 text-sm font-semibold text-primary" aria-hidden="true">
                <span class="hidden sm:inline">Ver</span>
                @svg('heroicon-o-arrow-right', 'size-4 transition group-hover:translate-x-0.5')
            </span>
        </div>
    </div>
</article>
