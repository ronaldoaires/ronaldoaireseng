<x-layouts.site :title="$product->title . ' · Leitura online'" :noindex="true">
    <div class="mx-auto max-w-6xl px-4 py-6">
        <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
            <div>
                <a href="{{ route('account.library.show', ['product' => $product->slug]) }}" class="inline-flex items-center gap-2 text-sm font-semibold text-primary hover:underline">
                    @svg('heroicon-o-arrow-left', 'size-4')
                    Minha biblioteca
                </a>
                <h1 class="mt-2 font-display text-2xl font-bold text-ink">{{ $product->title }}</h1>
            </div>
            <a href="{{ route('account.reader.pdf', ['product' => $product->slug]) }}" target="_blank" rel="noopener" class="rounded-control border border-line px-4 py-2 text-sm font-semibold text-ink hover:bg-primary-soft">Abrir em outra aba</a>
        </div>

        <iframe src="{{ route('account.reader.pdf', ['product' => $product->slug]) }}#toolbar=1" title="Ler {{ $product->title }}" class="h-[78dvh] min-h-[560px] w-full rounded-card border border-line bg-white shadow-card"></iframe>
        <p class="mt-3 text-sm text-muted">Se o PDF não abrir neste navegador, use “Abrir em outra aba” ou baixe o arquivo na sua biblioteca.</p>
    </div>
</x-layouts.site>
