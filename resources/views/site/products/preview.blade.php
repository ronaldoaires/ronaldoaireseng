<x-layouts.site
    :title="$chapter->title . ' · Amostra de ' . $product->title"
    :description="'Leia grátis um capítulo de ' . $product->title . '. ' . $product->summary"
    :image="$product->cover_url">

    <div class="mx-auto max-w-3xl px-4 py-8 sm:py-12">
        <x-site.breadcrumb :items="[
            ['name' => $product->type->pluralLabel(), 'url' => route('products.index', ['type' => $product->type->segment()])],
            ['name' => $product->title, 'url' => $product->url],
            ['name' => 'Amostra'],
        ]" />

        <x-ui.alert tone="info" icon="book-open" class="mt-6">
            Amostra grátis de <strong>{{ $product->title }}</strong>. Quem compra lê todos os capítulos aqui e baixa o PDF.
        </x-ui.alert>

        <article class="mt-6 rounded-card bg-surface p-6 shadow-card sm:p-10">
            <h1 class="text-balance text-3xl font-bold tracking-tight text-ink">{{ $chapter->title }}</h1>

            @if ($body = \App\Support\RichContent::render($chapter->body))
                <div class="rich-content reader-content mt-6">{{ $body }}</div>
            @endif
        </article>

        <section class="mt-8 rounded-card bg-primary-soft p-6 text-center sm:p-8" aria-labelledby="continue-heading">
            <h2 id="continue-heading" class="text-xl font-bold text-on-primary-soft">Gostou? Continue lendo.</h2>
            <p class="mt-2 text-on-primary-soft/80">
                São {{ $chapters->count() }} capítulos na versão digital, mais o PDF para baixar.
            </p>
            <x-ui.button :href="$product->url . '#comprar'" size="lg" class="mt-5">Ver {{ mb_strtolower($product->type->getLabel()) }} completo</x-ui.button>
        </section>

        <nav aria-label="Capítulos" class="mt-8 rounded-card bg-surface p-6 shadow-card">
            <h2 class="text-lg font-bold text-ink">Sumário</h2>
            <ol class="mt-3 space-y-1 text-sm">
                @foreach ($chapters as $item)
                    <li class="flex min-h-11 items-center justify-between gap-3 border-b border-line last:border-0">
                        @if ($item->is_preview)
                            <a href="{{ route('products.preview', ['type' => $product->type->segment(), 'product' => $product->slug, 'chapter' => $item->slug]) }}"
                               @if ($item->is($chapter)) aria-current="page" @endif
                               class="font-medium text-primary hover:underline">{{ $loop->iteration }}. {{ $item->title }}</a>
                            <x-ui.badge tone="success">Grátis</x-ui.badge>
                        @else
                            <span class="text-muted">{{ $loop->iteration }}. {{ $item->title }}</span>
                            @svg('heroicon-o-lock-closed', 'size-4 shrink-0 text-subtle')
                        @endif
                    </li>
                @endforeach
            </ol>
        </nav>
    </div>
</x-layouts.site>
