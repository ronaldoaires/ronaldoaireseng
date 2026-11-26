@props(['product', 'sales'])

@php
    $chapters = config('showcase.renders.ebook_chapters', []);
    $checkoutUrl = route('checkout.show', ['product' => $product->slug]);
    $arrow = 'flex size-11 items-center justify-center rounded-full border border-line bg-white text-ink transition hover:bg-primary-soft hover:text-primary focus-visible:outline-2 focus-visible:outline-primary [&.is-disabled]:pointer-events-none [&.is-disabled]:opacity-35';
@endphp

{{-- Benefit-led opening of the long sales page: numbers, the reader's own doubts and the 3D drawings. --}}
<div class="space-y-14">
    {{-- Big numbers: the size of the material at a glance. --}}
    <section aria-label="O ebook em números" class="grid grid-cols-2 gap-3 sm:grid-cols-4 sm:gap-4">
        @foreach ($sales['stats'] as $stat)
            <div class="rounded-card bg-surface p-5 text-center shadow-card ring-1 ring-line">
                <p class="font-display text-4xl font-bold leading-none tracking-tight text-primary sm:text-5xl">{{ $stat['value'] }}</p>
                <p class="mt-2 text-sm font-medium text-muted">{{ $stat['label'] }}</p>
            </div>
        @endforeach
    </section>

    {{-- The doubts the reader already has: recognition comes before the offer. --}}
    <section aria-labelledby="ebook-questions-heading">
        <div class="max-w-2xl">
            <p class="text-xs font-bold uppercase tracking-[0.16em] text-primary">Isso já aconteceu com você?</p>
            <h2 id="ebook-questions-heading" class="mt-2 font-display text-3xl font-bold tracking-tight text-ink sm:text-4xl">Dúvidas que aparecem em toda obra</h2>
        </div>

        <ul class="mt-8 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($sales['questions'] as $question)
                <li class="flex items-start gap-4 rounded-card bg-surface p-5 shadow-card ring-1 ring-line">
                    <span class="flex size-11 shrink-0 items-center justify-center rounded-control bg-primary-soft text-primary">
                        @svg('heroicon-o-' . $question['icon'], 'size-6', ['aria-hidden' => 'true'])
                    </span>
                    <p class="font-semibold leading-snug text-ink">{{ $question['text'] }}</p>
                </li>
            @endforeach
        </ul>

        <div class="mt-8 flex flex-col gap-5 rounded-card bg-primary-soft p-6 sm:flex-row sm:items-center sm:justify-between sm:p-8">
            <p class="max-w-2xl text-lg font-semibold leading-snug text-on-primary-soft">
                O livro responde cada uma delas de forma simples, com desenho e com o que a norma pede.
            </p>
            <x-ui.button :href="$checkoutUrl" variant="gradient" size="lg" class="shrink-0">{{ $sales['cta'] }}</x-ui.button>
        </div>
    </section>

    {{-- The 3D drawings: what sets this book apart from any other PDF about concrete. --}}
    @if ($chapters)
        <section aria-labelledby="ebook-3d-heading" data-carousel-scope>
            <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div class="max-w-2xl">
                    <p class="text-xs font-bold uppercase tracking-[0.16em] text-primary">Desenhos em 3D</p>
                    <h2 id="ebook-3d-heading" class="mt-2 font-display text-3xl font-bold tracking-tight text-ink sm:text-4xl">Você vê onde vai cada ferro</h2>
                    <p class="mt-3 text-lg text-muted">Cada capítulo mostra a peça em 3D, com a armadura no lugar certo. Fica fácil entender o que a prancha do projeto quer dizer.</p>
                </div>
                <div class="hidden gap-2 sm:flex">
                    <button type="button" class="{{ $arrow }}" data-carousel-prev aria-label="Anterior">@svg('heroicon-o-arrow-left', 'size-5', ['aria-hidden' => 'true'])</button>
                    <button type="button" class="{{ $arrow }}" data-carousel-next aria-label="Próximo">@svg('heroicon-o-arrow-right', 'size-5', ['aria-hidden' => 'true'])</button>
                </div>
            </div>

            <div class="swiper mt-8 overflow-hidden" data-carousel data-carousel-autoplay>
                <ul class="swiper-wrapper">
                    @foreach ($chapters as $image => $title)
                        <li class="swiper-slide h-auto! w-72! sm:w-80!">
                            <figure class="flex h-full flex-col rounded-[1.5rem] bg-surface p-2 shadow-card ring-1 ring-line">
                                <img src="{{ asset($image) }}" alt="" width="800" height="480" loading="lazy" decoding="async" draggable="false" class="aspect-[5/3] w-full rounded-[1.1rem] object-cover">
                                <figcaption class="flex flex-1 items-start px-3 pb-4 pt-4 font-display text-xl font-bold leading-tight text-ink">{{ $title }}</figcaption>
                            </figure>
                        </li>
                    @endforeach
                </ul>
            </div>
        </section>
    @endif
</div>
