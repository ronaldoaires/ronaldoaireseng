<x-layouts.account title="Minha biblioteca">
    <x-account.page-header
        title="Minha biblioteca"
        description="Tudo o que você comprou: leia online e baixe os arquivos quando precisar." />

    @if ($purchases->isEmpty())
        <x-ui.empty-state
            icon="book-open"
            title="Sua biblioteca está vazia"
            description="Os ebooks e planilhas que você comprar aparecem aqui na hora.">
            <div class="flex flex-col justify-center gap-3 sm:flex-row">
                <x-ui.button :href="route('products.index', ['type' => 'ebooks'])">Ver ebooks</x-ui.button>
                <x-ui.button :href="route('products.index', ['type' => 'planilhas'])" variant="tonal">Ver planilhas</x-ui.button>
            </div>
        </x-ui.empty-state>
    @else
        <ul class="grid gap-4 sm:grid-cols-2">
            @foreach ($purchases as $purchase)
                @php($product = $purchase->product)
                <li class="flex gap-4 rounded-card bg-surface p-4 shadow-card">
                    <div class="relative aspect-[3/4] w-20 shrink-0 overflow-hidden rounded-control bg-canvas">
                        @if ($product->cover_thumb_url)
                            <img src="{{ $product->cover_thumb_url }}" alt="" width="80" height="107" loading="lazy" class="absolute inset-0 size-full object-cover">
                        @else
                            <span class="absolute inset-0 flex items-center justify-center text-subtle">@svg('heroicon-o-' . $product->type->iconName(), 'size-7')</span>
                        @endif
                    </div>

                    <div class="flex min-w-0 flex-1 flex-col">
                        <p class="text-xs font-medium uppercase tracking-wide text-subtle">{{ $product->type->getLabel() }}</p>
                        <h2 class="mt-0.5 font-semibold leading-snug text-ink">{{ $product->title }}</h2>

                        @if ($purchase->lastChapter)
                            <p class="mt-1 truncate text-xs text-muted">Parou em: {{ $purchase->lastChapter->title }}</p>
                        @endif

                        <div class="mt-auto flex flex-wrap gap-2 pt-3">
                            @if ($product->hasOnlineReader() && $product->chapters_count > 0)
                                <x-ui.button :href="route('account.reader', ['product' => $product->slug])" class="min-h-11 px-4">
                                    @svg('heroicon-o-book-open', 'size-4')
                                    {{ $purchase->lastChapter ? 'Continuar' : 'Ler' }}
                                </x-ui.button>
                            @endif
                            @if ($product->files_count > 0)
                                <x-ui.button :href="route('account.library.show', ['product' => $product->slug])" variant="tonal" class="min-h-11 px-4">
                                    @svg('heroicon-o-arrow-down-tray', 'size-4')
                                    Arquivos
                                </x-ui.button>
                            @endif
                        </div>
                    </div>
                </li>
            @endforeach
        </ul>
    @endif
</x-layouts.account>
