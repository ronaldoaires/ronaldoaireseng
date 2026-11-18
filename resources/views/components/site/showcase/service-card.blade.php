@props([
    'service',
    // Compact: image and name only (home grid), no summary.
    'compact' => false,
])

@php
$url = route('services.show', ['area' => $service->area->slug(), 'service' => $service->slug]);
@endphp

{{-- Whole card clickable through the stretched link on the title. --}}
<article {{ $attributes->merge(['class' => 'group/card relative flex flex-col overflow-hidden rounded-[1.5rem] bg-surface p-2 ring-1 ring-line transition has-[.card-link:hover]:-translate-y-0.5 has-[.card-link:hover]:shadow-[0_18px_40px_-18px_rgb(91_92_240/0.45)]']) }}>
    @if ($service->display_image_url)
        <img
            src="{{ $service->display_image_url }}"
            alt=""
            width="800" height="480"
            loading="lazy" decoding="async"
            class="aspect-[5/3] w-full rounded-[1.1rem] bg-canvas object-cover">
    @endif

    <div class="flex flex-1 flex-col p-4">

    <h3 @class(["font-display font-bold leading-tight text-ink", "flex-1 text-xl" => $compact, "text-2xl" => ! $compact])>
        <a href="{{ $url }}" class="card-link after:absolute after:inset-0 after:rounded-card focus:outline-none focus-visible:after:ring-2 focus-visible:after:ring-primary">
            {{ $service->name }}
        </a>
    </h3>

    @unless ($compact)
    <p class="mt-2 flex-1 text-sm leading-relaxed text-muted">{{ $service->summary }}</p>
    @endunless

    <span aria-hidden="true" class="mt-5 inline-flex items-center gap-1.5 text-sm font-semibold text-primary">
        Saiba mais
        @svg('heroicon-o-arrow-right', 'size-4 transition group-has-[.card-link:hover]/card:translate-x-1')
    </span>
    </div>
</article>
