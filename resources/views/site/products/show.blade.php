@php
    $typeLabel = $product->type->getLabel();
    $breadcrumbs = [
        ['name' => 'Início', 'url' => route('home')],
        ['name' => $product->type->pluralLabel(), 'url' => route('products.index', ['type' => $product->type->segment()])],
        ['name' => $product->title],
    ];
@endphp

<x-layouts.site
    :title="$product->meta_title"
    :description="$product->meta_description"
    :image="$product->cover_url"
    type="product"
    :json-ld="$jsonLd">

    {{-- Hero: cover, promise and price above the fold. On phones the price box
         comes right after the title, so the buy button is never far. --}}
    <x-site.page-hero>
        <div class="mx-auto grid max-w-6xl gap-8 px-4 py-8 sm:py-14 lg:grid-cols-12 lg:items-start">
            <div class="lg:col-span-7">
                <x-site.breadcrumb :items="$breadcrumbs" />

                <p class="mt-6 inline-flex items-center gap-1.5 rounded-full bg-white/90 px-3 py-1 text-sm font-semibold text-primary ring-1 ring-line">
                    @svg('heroicon-o-' . $product->type->iconName(), 'size-4')
                    {{ $typeLabel }}
                    @if ($product->version)
                        · {{ $product->version }}
                    @endif
                </p>

                <h1 class="mt-3 font-display text-4xl font-bold leading-[1.05] tracking-tight text-ink sm:text-6xl">{{ $product->title }}</h1>

                @if ($product->subtitle)
                    <p class="mt-4 text-pretty text-lg text-muted">{{ $product->subtitle }}</p>
                @endif

                @if (filled($product->highlights))
                    <ul class="mt-6 space-y-2.5">
                        @foreach ($product->highlights as $item)
                            <li class="flex items-start gap-2.5 text-muted">
                                @svg('heroicon-o-check-circle', 'mt-0.5 size-5 shrink-0 text-primary')
                                {{ $item }}
                            </li>
                        @endforeach
                    </ul>
                @endif

                @if ($product->hasOnlineReader() && $product->previewChapters->isNotEmpty())
                    @php($sample = $product->previewChapters->first())
                    <a
                        href="{{ route('products.preview', ['type' => $product->type->segment(), 'product' => $product->slug, 'chapter' => $sample->slug]) }}"
                        class="mt-6 inline-flex min-h-12 items-center gap-2 rounded-control border border-line bg-white px-4 text-sm font-semibold text-ink transition hover:bg-primary-soft hover:text-primary">
                        @svg('heroicon-o-book-open', 'size-5')
                        Ler uma amostra grátis
                    </a>
                @endif
            </div>

            <div id="comprar" class="grid scroll-mt-6 gap-6 sm:grid-cols-2 lg:col-span-5 lg:grid-cols-1">
                @if ($product->cover_url)
                    <img
                        src="{{ $product->cover_url }}"
                        alt="Capa de {{ $product->title }}"
                        width="600"
                        height="800"
                        fetchpriority="high"
                        class="mx-auto aspect-[3/4] w-full max-w-xs rounded-card object-cover shadow-card sm:max-w-none lg:max-w-xs">
                @endif

                <x-site.buy-box :product="$product" :owns="$owns" class="self-start" />
            </div>
        </div>
    </x-site.page-hero>

    <div class="mx-auto max-w-6xl space-y-14 px-4 py-12">
        <div class="grid gap-8 lg:grid-cols-3">
            @if ($body = \App\Support\RichContent::render($product->body))
                <article class="rich-content rounded-card bg-surface p-6 shadow-card sm:p-8 lg:col-span-2">
                    {{ $body }}
                </article>
            @endif

            <aside @class(['h-fit space-y-6', 'lg:col-span-3' => ! $body])>
                @if (filled($product->specs))
                    <section class="rounded-card bg-surface p-6 shadow-card" aria-labelledby="specs-heading">
                        <h2 id="specs-heading" class="text-lg font-bold text-ink">Ficha técnica</h2>
                        <dl class="mt-4 divide-y divide-line text-sm">
                            @foreach ($product->specs as $spec)
                                <div class="flex justify-between gap-4 py-2.5">
                                    <dt class="text-subtle">{{ $spec['label'] }}</dt>
                                    <dd class="text-right font-medium text-ink">{{ $spec['value'] }}</dd>
                                </div>
                            @endforeach
                        </dl>
                    </section>
                @endif

                @if ($product->files->isNotEmpty())
                    <section class="rounded-card bg-surface p-6 shadow-card" aria-labelledby="files-heading">
                        <h2 id="files-heading" class="text-lg font-bold text-ink">O que você baixa</h2>
                        <ul class="mt-4 space-y-3">
                            @foreach ($product->files as $file)
                                <li class="flex items-center gap-3 text-sm">
                                    <span class="flex size-10 shrink-0 items-center justify-center rounded-control bg-primary-soft text-primary">
                                        @svg('heroicon-o-' . $file->iconName(), 'size-5')
                                    </span>
                                    <span class="min-w-0">
                                        <span class="block font-medium text-ink">{{ $file->label }}</span>
                                        <span class="block text-xs uppercase text-subtle">{{ $file->extension() }} · {{ $file->formattedSize() }}</span>
                                    </span>
                                </li>
                            @endforeach
                            @if ($product->hasOnlineReader() && $product->chapters_count > 0)
                                <li class="flex items-center gap-3 text-sm">
                                    <span class="flex size-10 shrink-0 items-center justify-center rounded-control bg-primary-soft text-primary">
                                        @svg('heroicon-o-device-phone-mobile', 'size-5')
                                    </span>
                                    <span>
                                        <span class="block font-medium text-ink">Versão digital</span>
                                        <span class="block text-xs text-subtle">{{ $product->chapters_count }} capítulos para ler online</span>
                                    </span>
                                </li>
                            @endif
                        </ul>
                    </section>
                @endif
            </aside>
        </div>

        <x-site.showcase.responsible :area="\App\Enums\ServiceArea::Engineering" />

        @if (filled($product->faq))
            <section aria-labelledby="faq-heading">
                <h2 id="faq-heading" class="text-2xl font-bold tracking-tight text-ink">Perguntas frequentes</h2>

                {{-- Native <details>: accessible and works without JavaScript. --}}
                <div class="mt-6 divide-y divide-line overflow-hidden rounded-card bg-surface shadow-card">
                    @foreach ($product->faq as $item)
                        <details class="group">
                            <summary class="flex min-h-14 cursor-pointer list-none items-center justify-between gap-4 px-6 py-4 font-semibold text-ink [&::-webkit-details-marker]:hidden">
                                {{ $item['question'] }}
                                @svg('heroicon-o-chevron-down', 'size-5 shrink-0 text-subtle transition group-open:rotate-180')
                            </summary>
                            <p class="whitespace-pre-line px-6 pb-5 leading-relaxed text-muted">{{ $item['answer'] }}</p>
                        </details>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- Second chance to buy, after the reader went through the whole page. --}}
        <section class="grid gap-6 rounded-card bg-primary-soft p-6 sm:p-8 lg:grid-cols-2 lg:items-center" aria-labelledby="final-cta-heading">
            <div>
                <h2 id="final-cta-heading" class="text-2xl font-bold tracking-tight text-on-primary-soft">{{ $product->title }}</h2>
                <p class="mt-2 text-on-primary-soft/80">{{ $product->summary }}</p>
                <p class="mt-4 text-xs text-on-primary-soft/70">
                    Ao comprar você concorda com a
                    <a href="{{ route('legal.license') }}" class="font-semibold underline">Licença de Uso</a>:
                    uso pessoal, sem revenda ou compartilhamento do arquivo.
                </p>
            </div>
            <x-site.buy-box :product="$product" :owns="$owns" />
        </section>

        @if ($related->isNotEmpty())
            <section aria-labelledby="related-heading">
                <h2 id="related-heading" class="text-xl font-bold tracking-tight text-ink">Você também pode gostar</h2>

                <div class="mt-6 grid grid-cols-2 gap-4 sm:gap-6 lg:grid-cols-3">
                    @foreach ($related as $item)
                        <x-site.product-card :product="$item" />
                    @endforeach
                </div>
            </section>
        @endif
    </div>

    {{-- Phones: sticky buy bar, so the button is always one tap away. Sits above the bottom nav. --}}
    @unless ($owns)
        <div class="fixed inset-x-0 bottom-[calc(4rem+env(safe-area-inset-bottom))] z-20 border-t border-line bg-surface/95 px-4 py-3 backdrop-blur md:hidden">
            <div class="mx-auto flex max-w-md items-center justify-between gap-3">
                <p class="text-lg font-bold text-ink">{{ (float) $product->price <= 0 ? 'Grátis' : $product->formattedPrice() }}</p>
                <x-ui.button :href="route('checkout.show', ['product' => $product->slug])" class="flex-1">
                    {{ (float) $product->price <= 0 ? 'Quero receber' : 'Comprar' }}
                </x-ui.button>
            </div>
        </div>
        {{-- Room for the sticky bar at the end of the page. --}}
        <div class="h-20 md:hidden" aria-hidden="true"></div>
    @endunless
</x-layouts.site>
