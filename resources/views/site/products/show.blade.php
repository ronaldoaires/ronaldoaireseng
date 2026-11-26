@php
    $typeLabel = $product->type->getLabel();
    $breadcrumbs = [
        ['name' => 'Início', 'url' => route('home')],
        ['name' => $product->type->pluralLabel(), 'url' => route('products.index', ['type' => $product->type->segment()])],
        ['name' => $product->title],
    ];
    $previewPdfModalId = 'preview-pdf-' . $product->id;
    $previewImageModalId = 'preview-image-' . $product->id;
    // Long benefit-led layout for products listed in config/showcase.php (ebook_sales).
    $sales = config('showcase.ebook_sales.' . $product->slug);
    $checkoutUrl = route('checkout.show', ['product' => $product->slug]);
@endphp

<x-layouts.site
    :title="$product->meta_title"
    :description="$product->meta_description"
    :image="$product->cover_url"
    type="product"
    :json-ld="$jsonLd">

    {{-- Hero: the promise, a real book cover and the main purchase action. --}}
    <x-site.page-hero>
        <div @if ($sales) id="comprar" @endif class="mx-auto grid max-w-6xl scroll-mt-6 gap-8 px-4 py-8 sm:py-14 lg:grid-cols-12 lg:items-start">
            <div class="lg:col-span-7">
                <div @class(['hidden sm:block' => $sales])>
                    <x-site.breadcrumb :items="$breadcrumbs" />
                </div>

                <p @class(['inline-flex items-center gap-1.5 rounded-full bg-white/90 px-3 py-1 text-sm font-semibold text-primary ring-1 ring-line', 'mt-6' => ! $sales, 'sm:mt-6' => $sales])>
                    @svg('heroicon-o-' . $product->type->iconName(), 'size-4')
                    {{ $sales ? $typeLabel . ' · ' . $product->title : $typeLabel }}
                    @if ($product->version && ! $sales)
                        · {{ $product->version }}
                    @endif
                </p>

                @if ($sales)
                    {{-- Sales layout: the headline is the benefit; the product name sits in the badge above. --}}
                    <h1 class="mt-4 text-balance font-display text-4xl font-bold leading-[1.05] tracking-tight text-ink sm:text-6xl">{{ $sales['headline'] }}</h1>
                    <p class="mt-4 max-w-2xl text-pretty text-lg leading-relaxed text-muted sm:text-xl">{{ $sales['promise'] }}</p>

                    {{-- Phones: cover, price and button on the first screen, before any long text. --}}
                    <div data-primary-buy-box class="mt-6 flex items-center gap-4 rounded-card bg-surface p-3 shadow-card ring-1 ring-line lg:hidden">
                        @if ($product->cover_url)
                            <img src="{{ $product->cover_url }}" alt="Capa de {{ $product->title }}" width="600" height="800" fetchpriority="high" class="aspect-[3/4] w-24 shrink-0 rounded-lg object-cover shadow-card sm:w-32">
                        @endif
                        <div class="min-w-0 flex-1">
                            @if ($owns)
                                <p class="text-sm font-semibold text-success">Você já tem este ebook</p>
                                <x-ui.button :href="route('account.library.show', ['product' => $product->slug])" class="mt-2 w-full">Abrir na biblioteca</x-ui.button>
                            @else
                                @if ($product->hasDiscount())
                                    <p class="text-xs text-subtle">
                                        De <span class="line-through">{{ $product->formattedCompareAtPrice() }}</span>
                                        <span class="ml-1 rounded-full bg-accent px-1.5 py-0.5 font-bold text-brand-950">-{{ $product->discountPercent() }}%</span>
                                    </p>
                                @endif
                                <p class="mt-0.5 text-3xl font-bold leading-none tracking-tight text-ink">{{ $product->formattedPrice() }}</p>
                                <p class="mt-1 text-xs text-muted">{{ $sales['price_note'] }} · Pix ou cartão</p>
                                <x-ui.button :href="$checkoutUrl" variant="gradient" class="mt-2.5 w-full">{{ $sales['cta'] }}</x-ui.button>
                            @endif
                        </div>
                    </div>
                @else
                    <h1 class="mt-3 font-display text-4xl font-bold leading-[1.05] tracking-tight text-ink sm:text-6xl">{{ $product->title }}</h1>

                    @if ($product->subtitle)
                        <p class="mt-4 text-pretty text-xl font-medium text-ink/80">{{ $product->subtitle }}</p>
                    @endif

                    <p class="mt-5 max-w-2xl text-pretty text-base leading-relaxed text-muted sm:text-lg">{{ $product->summary }}</p>
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

                @if ($product->preview_pdf_url)
                    <a href="#amostra" class="mt-6 inline-flex min-h-12 items-center gap-2 rounded-control border border-line bg-white px-4 text-sm font-semibold text-ink transition hover:bg-primary-soft hover:text-primary">
                        @svg('heroicon-o-book-open', 'size-5')
                        Ver amostra do ebook
                    </a>
                @endif
            </div>

            <div @if (! $sales) id="comprar" @endif @class(['scroll-mt-6 gap-6 sm:grid-cols-2 lg:col-span-5 lg:grid-cols-1 lg:gap-4', 'grid' => ! $sales, 'hidden lg:grid' => $sales])>
                @if ($product->cover_url)
                    <img
                        src="{{ $product->cover_url }}"
                        alt="Capa de {{ $product->title }}"
                        width="600"
                        height="800"
                        fetchpriority="high"
                        class="mx-auto aspect-[3/4] w-full max-w-sm rounded-card object-cover shadow-card sm:max-w-none lg:max-w-sm">
                @endif

                @if ($sales)
                    <x-site.buy-box :product="$product" :owns="$owns" :price-note="$sales['price_note']" :cta="$sales['cta']" class="self-start" />
                @else
                    <x-site.buy-box :product="$product" :owns="$owns" data-primary-buy-box class="self-start" />
                @endif
            </div>
        </div>
    </x-site.page-hero>

    <div class="mx-auto max-w-6xl space-y-14 px-4 py-12">
        @if ($sales)
            <x-site.ebook-sales.intro :product="$product" :sales="$sales" />
        @endif

        @if ($product->sales_video_embed_url)
            <section aria-labelledby="video-heading" class="mx-auto max-w-4xl">
                <h2 id="video-heading" class="mb-5 font-display text-2xl font-bold text-ink sm:text-3xl">Conheça o material</h2>
                <iframe src="{{ $product->sales_video_embed_url }}" title="Vídeo de apresentação de {{ $product->title }}" loading="lazy" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen class="aspect-video w-full rounded-card border border-line bg-ink shadow-card"></iframe>
            </section>
        @endif

        @if ($product->preview_pdf_url)
            <section id="amostra" aria-labelledby="sample-heading" data-carousel-scope class="scroll-mt-24 rounded-card bg-surface p-5 shadow-card sm:p-8">
                <div class="mb-6 flex flex-wrap items-end justify-between gap-4">
                    <div class="max-w-2xl">
                        <p class="text-xs font-bold uppercase tracking-[0.16em] text-primary">Amostra gratuita</p>
                        <h2 id="sample-heading" class="mt-2 font-display text-2xl font-bold tracking-tight text-ink sm:text-3xl">Veja o livro por dentro</h2>
                        <p class="mt-2 text-muted">Páginas selecionadas do próprio material, para você avaliar a linguagem, as ilustrações e o nível de detalhe antes de comprar.</p>
                    </div>
                    <button type="button" data-modal-open="{{ $previewPdfModalId }}" aria-haspopup="dialog" aria-controls="{{ $previewPdfModalId }}" class="inline-flex min-h-11 items-center gap-2 rounded-control border border-line px-4 text-sm font-semibold text-ink hover:bg-primary-soft">
                        @svg('heroicon-o-book-open', 'size-4')
                        Ler prévia completa
                    </button>
                </div>
                @if (filled($product->preview_images))
                    <div class="swiper overflow-hidden" data-carousel data-carousel-finite data-carousel-shuffle aria-label="Páginas selecionadas do material">
                        <ul class="swiper-wrapper">
                            @foreach ($product->preview_images as $image)
                                @php($imageUrl = \Illuminate\Support\Facades\Storage::disk('public')->url($image['path']))
                                <li class="swiper-slide h-auto! w-[78vw]! max-w-96! sm:w-80!">
                                    <button type="button" data-modal-open="{{ $previewImageModalId }}" data-modal-image-src="{{ $imageUrl }}" data-modal-image-alt="{{ $image['alt'] }}" aria-haspopup="dialog" aria-controls="{{ $previewImageModalId }}" aria-label="Ampliar: {{ $image['alt'] }}" class="group relative block h-full w-full overflow-hidden rounded-control border border-line bg-white text-left focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary">
                                        <img src="{{ $imageUrl }}" alt="{{ $image['alt'] }}" loading="lazy" width="1061" height="1500" draggable="false" class="w-full">
                                        <span aria-hidden="true" class="absolute right-3 top-3 flex size-10 items-center justify-center rounded-control bg-white text-ink shadow-card transition group-hover:text-primary">@svg('heroicon-o-magnifying-glass-plus', 'size-5')</span>
                                    </button>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                    <div class="mt-5 flex items-center justify-between gap-4">
                        <p class="text-sm text-muted">Arraste ou use as setas para ver as {{ count($product->preview_images) }} páginas.</p>
                        <div class="flex gap-2">
                            <button type="button" data-carousel-prev aria-label="Página anterior" class="flex size-11 items-center justify-center rounded-full border border-line text-ink hover:bg-primary-soft focus-visible:outline-2 focus-visible:outline-primary [&.is-disabled]:pointer-events-none [&.is-disabled]:opacity-35">@svg('heroicon-o-arrow-left', 'size-5')</button>
                            <button type="button" data-carousel-next aria-label="Próxima página" class="flex size-11 items-center justify-center rounded-full border border-line text-ink hover:bg-primary-soft focus-visible:outline-2 focus-visible:outline-primary [&.is-disabled]:pointer-events-none [&.is-disabled]:opacity-35">@svg('heroicon-o-arrow-right', 'size-5')</button>
                        </div>
                    </div>
                @endif
                <p class="mt-4 text-sm text-muted">Quer ler com calma? <button type="button" data-modal-open="{{ $previewPdfModalId }}" aria-haspopup="dialog" aria-controls="{{ $previewPdfModalId }}" class="font-semibold text-primary underline">Abra a amostra completa aqui</button>.</p>
            </section>

            <x-ui.modal :id="$previewPdfModalId" title="Prévia de {{ $product->title }}" size="reading" class="p-0! sm:p-0!">
                <div class="mx-auto max-w-3xl px-4 pt-5 sm:px-6">
                    @if (filled($product->preview_images))
                        <p class="text-sm text-muted">Leia aqui as {{ count($product->preview_images) }} páginas selecionadas do PDF.</p>
                    @endif
                    <a href="{{ $product->preview_pdf_url }}" download class="mt-3 inline-flex min-h-11 items-center gap-2 text-sm font-semibold text-primary underline">@svg('heroicon-o-arrow-down-tray', 'size-4') Baixar PDF da amostra</a>
                </div>
                @if (filled($product->preview_images))
                    <ol class="mx-auto max-w-3xl space-y-8 p-4 sm:p-6">
                        @foreach ($product->preview_images as $image)
                            <li>
                                <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($image['path']) }}" alt="{{ $image['alt'] }}" loading="lazy" width="1061" height="1500" class="w-full rounded-control border border-line">
                            </li>
                        @endforeach
                    </ol>
                @else
                    <iframe src="{{ $product->preview_pdf_url }}#toolbar=1" title="PDF de amostra de {{ $product->title }}" loading="lazy" class="mt-4 h-[70dvh] w-full bg-white"></iframe>
                @endif
            </x-ui.modal>

            <x-ui.modal :id="$previewImageModalId" title="Página ampliada da amostra" size="image">
                <img data-modal-image alt="" class="mx-auto w-full max-w-[52rem]">
                <p data-modal-image-caption class="mt-3 text-center text-sm text-muted"></p>
            </x-ui.modal>
        @endif

        @if ($sales)
            <x-site.ebook-sales.details :product="$product" :sales="$sales" />
        @elseif (filled($product->sales_reasons))
            <section aria-labelledby="reasons-heading" class="rounded-card bg-ink px-6 py-9 text-white sm:px-10 sm:py-12">
                <div class="max-w-3xl">
                    <h2 id="reasons-heading" class="font-display text-3xl font-bold tracking-tight sm:text-4xl">Por que adquirir {{ $product->title }}?</h2>
                    <p class="mt-3 text-white/75">Para entender a obra com mais clareza e saber o que conferir antes de cada etapa.</p>
                </div>
                <div class="mt-8 grid gap-6 md:grid-cols-3">
                    @foreach ($product->sales_reasons as $reason)
                        <div class="border-t border-white/25 pt-5">
                            <h3 class="font-display text-xl font-bold">{{ $reason['title'] }}</h3>
                            <p class="mt-2 leading-relaxed text-white/75">{{ $reason['description'] }}</p>
                        </div>
                    @endforeach
                </div>
                <x-ui.button :href="route('checkout.show', ['product' => $product->slug])" class="mt-8">Comprar agora</x-ui.button>
            </section>
        @endif

        <div class="grid gap-8 lg:grid-cols-3">
            {{-- The sales layout already tells the whole story in its own sections. --}}
            @if (! $sales && ($body = \App\Support\RichContent::render($product->body)))
                <article class="rich-content rounded-card bg-surface p-6 shadow-card sm:p-8 lg:col-span-2">
                    {{ $body }}
                </article>
            @endif

            <aside @class(['h-fit space-y-6', 'lg:col-span-3 grid gap-6 space-y-0! md:grid-cols-2' => empty($body)])>
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
                            @if ($product->hasOnlineReader() && ($product->chapters_count > 0 || $product->files->contains(fn ($file) => $file->extension() === 'pdf')))
                                <li class="flex items-center gap-3 text-sm">
                                    <span class="flex size-10 shrink-0 items-center justify-center rounded-control bg-primary-soft text-primary">
                                        @svg('heroicon-o-device-phone-mobile', 'size-5')
                                    </span>
                                    <span>
                                        <span class="block font-medium text-ink">Versão digital</span>
                                        <span class="block text-xs text-subtle">{{ $product->chapters_count > 0 ? $product->chapters_count.' capítulos' : 'Livro completo em PDF' }} para ler online</span>
                                    </span>
                                </li>
                            @endif
                        </ul>
                    </section>
                @endif
            </aside>
        </div>

        {{-- An empty reviews block reads as "nobody bought it": show it only once there are reviews, or to a buyer who can write one. --}}
        @if ($reviewCount > 0 || $owns || session('feedback_status'))
        <section id="avaliacoes" aria-labelledby="reviews-heading" class="scroll-mt-24 rounded-card bg-surface p-6 shadow-card sm:p-8">
            <div class="flex flex-col justify-between gap-6 sm:flex-row sm:items-end">
                <div class="max-w-2xl">
                    <h2 id="reviews-heading" class="font-display text-2xl font-bold tracking-tight text-ink sm:text-3xl">Avaliações reais de quem adquiriu</h2>
                    @if ($reviewCount > 0)
                        <p class="mt-3 flex flex-wrap items-center gap-2 text-muted">
                            <span aria-hidden="true" class="text-lg tracking-wide">
                                @for ($star = 1; $star <= 5; $star++)
                                    <span class="{{ $star <= round($ratingAverage) ? 'text-amber-500' : 'text-line' }}">★</span>
                                @endfor
                            </span>
                            <strong class="text-ink">{{ number_format($ratingAverage, 1, ',', '.') }} de 5</strong>
                        </p>
                    @else
                        <p class="mt-3 text-muted">Ainda não há avaliações publicadas. Quem comprou pode contar como foi a experiência com o material.</p>
                    @endif
                </div>
                <div class="flex flex-wrap gap-3">
                    <x-ui.button :href="route('products.feedback.review', ['type' => $product->type->segment(), 'product' => $product->slug])" variant="outline">Avaliar produto</x-ui.button>
                    <x-ui.button :href="route('products.feedback.suggestion', ['type' => $product->type->segment(), 'product' => $product->slug])" variant="tonal">Sugerir melhoria</x-ui.button>
                </div>
            </div>

            @if (session('feedback_status'))
                <x-ui.alert tone="success" icon="check-circle" class="mt-6" role="status">
                    {{ session('feedback_status') }}
                </x-ui.alert>
            @endif

            @if ($reviews->isNotEmpty())
                <div class="mt-8 grid gap-4 md:grid-cols-2">
                    @foreach ($reviews as $review)
                        <blockquote class="rounded-control border border-line p-5">
                            <div class="flex items-center justify-between gap-3">
                                <span aria-label="{{ $review->rating }} de 5 estrelas" class="text-amber-500">
                                    <span aria-hidden="true">{{ str_repeat('★', $review->rating) }}{{ str_repeat('☆', 5 - $review->rating) }}</span>
                                </span>
                                <time datetime="{{ $review->approved_at?->toDateString() }}" class="text-xs text-subtle">{{ $review->approved_at?->format('d/m/Y') }}</time>
                            </div>
                            <p class="mt-4 whitespace-pre-line leading-relaxed text-ink">{{ $review->plainContent() }}</p>
                            <footer class="mt-4 text-sm font-semibold text-muted">{{ $review->display_name }} <span class="font-normal text-subtle">· compra verificada</span></footer>
                        </blockquote>
                    @endforeach
                </div>
                <div class="mt-6">{{ $reviews->links() }}</div>
            @endif
        </section>
        @endif

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
                @if ($sales)
                    <h2 id="final-cta-heading" class="font-display text-3xl font-bold tracking-tight text-on-primary-soft sm:text-4xl">{{ $sales['final_heading'] }}</h2>
                    <p class="mt-3 text-lg text-on-primary-soft/80">{{ $sales['promise'] }}</p>
                    <p class="mt-5 flex items-start gap-3 rounded-control bg-white/70 p-4 text-on-primary-soft">
                        @svg('heroicon-o-shield-check', 'size-8 shrink-0 text-primary', ['aria-hidden' => 'true'])
                        <span><strong class="block">Garantia de 7 dias</strong> Leia, veja se é para você. Se não gostar, peça o reembolso em até 7 dias e devolvo o valor pago.</span>
                    </p>
                @else
                    <h2 id="final-cta-heading" class="text-2xl font-bold tracking-tight text-on-primary-soft">{{ $product->title }}</h2>
                    <p class="mt-2 text-on-primary-soft/80">{{ $product->summary }}</p>
                @endif
                <p class="mt-4 text-xs text-on-primary-soft/70">
                    Ao comprar você concorda com a
                    <a href="{{ route('legal.license') }}" class="font-semibold underline">Licença de Uso</a>:
                    uso pessoal, sem revenda ou compartilhamento do arquivo.
                </p>
            </div>
            <x-site.buy-box :product="$product" :owns="$owns" :price-note="$sales['price_note'] ?? null" :cta="$sales['cta'] ?? null" />
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
        <div data-sticky-buy-bar class="fixed inset-x-0 bottom-[calc(4rem+env(safe-area-inset-bottom))] z-20 border-t border-line bg-surface/95 px-4 py-3 backdrop-blur md:hidden">
            <div class="mx-auto flex max-w-md items-center justify-between gap-3">
                <p class="text-lg font-bold text-ink">{{ (float) $product->price <= 0 ? 'Grátis' : $product->formattedPrice() }}</p>
                <x-ui.button :href="$checkoutUrl" variant="gradient" class="flex-1">
                    {{ (float) $product->price <= 0 ? 'Quero receber' : ($sales['cta'] ?? 'Comprar') }}
                </x-ui.button>
            </div>
        </div>
        {{-- Room for the sticky bar at the end of the page. --}}
        <div class="h-20 md:hidden" aria-hidden="true"></div>
    @endunless
</x-layouts.site>
