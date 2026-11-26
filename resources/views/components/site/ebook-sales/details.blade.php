@props(['product', 'sales'])

@php
    $checkoutUrl = route('checkout.show', ['product' => $product->slug]);
    $author = $sales['author'];
    $person = config('showcase.person');
    $authorLinks = array_intersect_key($person['links'], array_flip(['Instagram', 'YouTube']));
@endphp

{{-- Proof of value after the sample: the 42 errors, the chapter method, the audience, the contents and the author. --}}
<div class="space-y-14">
    {{-- The strongest hook: real mistakes, shown as problem → fix. --}}
    <section aria-labelledby="ebook-errors-heading" class="rounded-card bg-ink px-5 py-10 text-white sm:px-10 sm:py-14">
        <div class="max-w-3xl">
            <p class="text-xs font-bold uppercase tracking-[0.16em] text-accent">Um capítulo inteiro sobre isso</p>
            <h2 id="ebook-errors-heading" class="mt-2 font-display text-3xl font-bold tracking-tight sm:text-5xl">Os 42 erros que mais estragam estruturas</h2>
            <p class="mt-4 text-lg text-white/75">Em ordem de obra, do projeto ao uso. Cada erro mostra o problema e como fazer certo. Use como lista de conferência antes de cada etapa.</p>
        </div>

        <ul class="mt-10 grid gap-4 md:grid-cols-2">
            @foreach ($sales['errors'] as $error)
                <li class="rounded-card bg-white/[0.06] p-5 ring-1 ring-white/15 sm:p-6">
                    <div class="flex items-center gap-3">
                        <span class="flex size-10 shrink-0 items-center justify-center rounded-full bg-accent font-display text-lg font-bold text-brand-950">{{ $error['number'] }}</span>
                        <h3 class="font-display text-xl font-bold leading-tight">{{ $error['title'] }}</h3>
                    </div>
                    <p class="mt-4 flex items-start gap-2 text-white/80">
                        @svg('heroicon-s-x-circle', 'mt-0.5 size-5 shrink-0 text-red-400', ['aria-hidden' => 'true'])
                        <span><span class="font-semibold text-white">Problema:</span> {{ $error['problem'] }}</span>
                    </p>
                    <p class="mt-2 flex items-start gap-2 text-white/80">
                        @svg('heroicon-s-check-circle', 'mt-0.5 size-5 shrink-0 text-green-400', ['aria-hidden' => 'true'])
                        <span><span class="font-semibold text-white">Faça assim:</span> {{ $error['fix'] }}</span>
                    </p>
                </li>
            @endforeach
        </ul>

        <div class="mt-10">
            <p class="font-semibold">E mais {{ 42 - count($sales['errors']) }} erros, separados pelas 7 fases da obra:</p>
            <ul class="mt-4 flex flex-wrap gap-2">
                @foreach ($sales['phases'] as $index => $phase)
                    <li class="rounded-full bg-white/10 px-3.5 py-1.5 text-sm font-medium ring-1 ring-white/20">
                        <span class="text-accent">{{ $index + 1 }}.</span> {{ $phase }}
                    </li>
                @endforeach
            </ul>
        </div>

        <x-ui.button :href="$checkoutUrl" variant="light" size="lg" class="mt-10 w-full sm:w-auto">{{ $sales['cta'] }}</x-ui.button>
    </section>

    {{-- How every chapter teaches: the format is part of the product. --}}
    <section aria-labelledby="ebook-method-heading">
        <div class="max-w-2xl">
            <p class="text-xs font-bold uppercase tracking-[0.16em] text-primary">Como você aprende</p>
            <h2 id="ebook-method-heading" class="mt-2 font-display text-3xl font-bold tracking-tight text-ink sm:text-4xl">Todo capítulo começa pela resposta</h2>
            <p class="mt-3 text-lg text-muted">Você abre no assunto da sua dúvida e já encontra a resposta. Se quiser, vai mais fundo.</p>
        </div>

        <ol class="mt-8 grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-6">
            @foreach ($sales['method'] as $index => $step)
                <li class="rounded-card bg-surface p-4 shadow-card ring-1 ring-line sm:p-5">
                    <span class="flex size-9 items-center justify-center rounded-full bg-brand-gradient font-display font-bold text-white">{{ $index + 1 }}</span>
                    <h3 class="mt-3 font-display text-lg font-bold leading-tight text-ink">{{ $step['title'] }}</h3>
                    <p class="mt-1 text-sm leading-snug text-muted">{{ $step['text'] }}</p>
                </li>
            @endforeach
        </ol>
    </section>

    {{-- Who it is for: each reader finds their own reason to buy. --}}
    <section aria-labelledby="ebook-audience-heading">
        <div class="max-w-2xl">
            <p class="text-xs font-bold uppercase tracking-[0.16em] text-primary">Para quem é</p>
            <h2 id="ebook-audience-heading" class="mt-2 font-display text-3xl font-bold tracking-tight text-ink sm:text-4xl">Feito para quem vive a obra</h2>
            <p class="mt-3 text-lg text-muted">Não precisa saber cálculo. O livro avisa quando um trecho é para iniciante, estudante ou profissional.</p>
        </div>

        <ul class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ($sales['audience'] as $item)
                <li class="flex items-start gap-4 rounded-card bg-surface p-5 shadow-card ring-1 ring-line sm:block sm:p-6">
                    <span class="flex size-12 shrink-0 items-center justify-center rounded-control bg-primary-soft text-primary">
                        @svg('heroicon-o-' . $item['icon'], 'size-6', ['aria-hidden' => 'true'])
                    </span>
                    <div>
                        <h3 class="font-display text-xl font-bold text-ink sm:mt-4">{{ $item['title'] }}</h3>
                        <p class="mt-1 leading-relaxed text-muted sm:mt-2">{{ $item['text'] }}</p>
                    </div>
                </li>
            @endforeach
        </ul>
    </section>

    {{-- What is inside: the five parts of the book. --}}
    <section aria-labelledby="ebook-parts-heading" class="rounded-card bg-surface p-6 shadow-card ring-1 ring-line sm:p-10">
        <div class="max-w-2xl">
            <p class="text-xs font-bold uppercase tracking-[0.16em] text-primary">O que tem no livro</p>
            <h2 id="ebook-parts-heading" class="mt-2 font-display text-3xl font-bold tracking-tight text-ink sm:text-4xl">Do caminho das cargas à desforma</h2>
        </div>

        <ol class="mt-8 divide-y divide-line">
            @foreach ($sales['parts'] as $index => $part)
                <li class="flex items-start gap-4 py-5 first:pt-0 last:pb-0">
                    <span class="flex size-12 shrink-0 items-center justify-center rounded-control bg-primary-soft text-primary">
                        @svg('heroicon-o-' . $part['icon'], 'size-6', ['aria-hidden' => 'true'])
                    </span>
                    <div>
                        <h3 class="font-display text-xl font-bold text-ink"><span class="text-primary">Parte {{ $index + 1 }}.</span> {{ $part['title'] }}</h3>
                        <p class="mt-1 leading-relaxed text-muted">{{ $part['text'] }}</p>
                    </div>
                </li>
            @endforeach
        </ol>
    </section>

    {{-- The author: a face and a registry build the trust a technical book needs. --}}
    <section aria-labelledby="ebook-author-heading" class="grid items-center gap-8 lg:grid-cols-12 lg:gap-12">
        <figure class="relative mx-auto w-full max-w-xs overflow-hidden rounded-2xl lg:col-span-4 lg:max-w-none">
            <img src="{{ asset($author['photo']) }}" alt="{{ $person['name'] }}, engenheiro civil, de capacete branco" width="720" height="720" loading="lazy" decoding="async" class="aspect-square w-full object-cover">
            <figcaption class="absolute bottom-4 left-4 rounded-md bg-white px-3 py-1.5 text-sm font-semibold text-ink">CREA-MT 62629</figcaption>
        </figure>

        <div class="lg:col-span-8">
            <p class="text-xs font-bold uppercase tracking-[0.16em] text-primary">Quem escreveu</p>
            <h2 id="ebook-author-heading" class="mt-2 font-display text-3xl font-bold tracking-tight text-ink sm:text-4xl">{{ $person['name'] }}</h2>
            <p class="mt-4 max-w-2xl text-lg leading-relaxed text-muted">{{ $author['text'] }}</p>

            <ul class="mt-6 flex flex-wrap gap-2">
                @foreach ($author['credentials'] as $credential)
                    <li class="inline-flex items-center gap-1.5 rounded-full bg-primary-soft px-3 py-1.5 text-sm font-semibold text-on-primary-soft">
                        @svg('heroicon-o-check-badge', 'size-4', ['aria-hidden' => 'true'])
                        {{ $credential }}
                    </li>
                @endforeach
            </ul>

            @if ($authorLinks)
                <p class="mt-6 text-muted">
                    Veja meus vídeos sobre erros de obra no
                    @foreach ($authorLinks as $network => $link)
                        <a href="{{ $link }}" target="_blank" rel="noopener" class="font-semibold text-primary underline">{{ $network }}</a>@if (! $loop->last) e @endif
                    @endforeach.
                </p>
            @endif
        </div>
    </section>
</div>
