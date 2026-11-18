@php
$chapters = config('showcase.renders.ebook_chapters', []);
// Link to the ebook's sales page when it is published; otherwise the ebook list.
$ebook = \App\Models\Product::query()
    ->where('slug', 'like', config('showcase.renders.ebook_slug_prefix') . '%')
    ->where('is_published', true)
    ->first();
$url = $ebook?->url ?? route('products.index', ['type' => 'ebooks']);
$arrow = 'flex size-12 items-center justify-center rounded-full bg-white text-ink ring-1 ring-line transition hover:bg-primary-soft hover:text-primary focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary [&.is-disabled]:pointer-events-none [&.is-disabled]:opacity-35';
@endphp

@if ($chapters)
{{-- "Inside the ebook": the 3D render of each chapter in a Swiper carousel. --}}
<section class="overflow-hidden bg-surface" aria-labelledby="ebook-inside-heading" data-carousel-scope>
    <div class="mx-auto max-w-6xl px-4 pt-16">
        <div class="flex flex-col gap-6 sm:flex-row sm:items-end sm:justify-between">
            <div class="max-w-2xl">
                <h2 id="ebook-inside-heading" class="font-display text-4xl font-bold tracking-tight text-ink sm:text-5xl">Concreto Armado sem Mistério</h2>
                <p class="mt-3 text-lg text-muted">As dúvidas da obra respondidas pela norma, com cada peça em 3D para você girar e entender.</p>
            </div>
            <x-ui.button :href="$url" variant="gradient" size="lg" class="shrink-0">Conhecer o ebook</x-ui.button>
        </div>

    </div>

    {{-- Full-bleed carousel: loops and advances slowly on its own. --}}
    <div class="swiper mt-10" data-carousel data-carousel-autoplay>
        <ul class="swiper-wrapper">
            @foreach ($chapters as $image => $title)
                <li class="swiper-slide h-auto! w-72! sm:w-80!">
                    <figure class="flex h-full flex-col rounded-[1.5rem] bg-canvas p-2 ring-1 ring-line">
                        <img
                            src="{{ asset($image) }}"
                            alt=""
                            width="800" height="480"
                            loading="lazy" decoding="async"
                            draggable="false"
                            class="aspect-[5/3] w-full rounded-[1.1rem] object-cover">
                        <figcaption class="flex flex-1 items-start px-3 pb-4 pt-4 font-display text-xl font-bold leading-tight text-ink">
                            {{ $title }}
                        </figcaption>
                    </figure>
                </li>
            @endforeach
        </ul>
    </div>

    <div class="mt-8 flex justify-center gap-3 pb-16">
        <button type="button" class="{{ $arrow }}" data-carousel-prev aria-label="Anterior">
            @svg('heroicon-o-arrow-left', 'size-5', ['aria-hidden' => 'true'])
        </button>
        <button type="button" class="{{ $arrow }}" data-carousel-next aria-label="Próximo">
            @svg('heroicon-o-arrow-right', 'size-5', ['aria-hidden' => 'true'])
        </button>
    </div>
</section>
@endif
