<x-layouts.account :title="$product->title">
    <a href="{{ route('account.library') }}" class="mb-4 inline-flex min-h-11 items-center gap-1.5 text-sm font-medium text-primary hover:underline">
        @svg('heroicon-o-arrow-left', 'size-4')
        Minha biblioteca
    </a>

    <div class="flex gap-4">
        @if ($product->cover_thumb_url)
            <img src="{{ $product->cover_thumb_url }}" alt="" width="80" height="107" class="aspect-[3/4] w-20 shrink-0 rounded-control object-cover shadow-card">
        @endif
        <x-account.page-header :title="$product->title" :description="$product->subtitle" />
    </div>

    <div class="space-y-6">
        @if ($product->hasOnlineReader() && $product->chapters->isNotEmpty())
            <section class="rounded-card bg-surface p-6 shadow-card" aria-labelledby="reader-heading">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h2 id="reader-heading" class="text-lg font-semibold text-ink">Versão digital</h2>
                        <p class="text-sm text-muted">{{ $product->chapters->count() }} capítulos para ler no celular ou no computador.</p>
                    </div>
                    <x-ui.button :href="route('account.reader', ['product' => $product->slug])">
                        @svg('heroicon-o-book-open', 'size-5')
                        {{ $purchase->lastChapter ? 'Continuar lendo' : 'Começar a ler' }}
                    </x-ui.button>
                </div>

                <ol class="mt-4 divide-y divide-line text-sm">
                    @foreach ($product->chapters as $chapter)
                        <li>
                            <a href="{{ route('account.reader', ['product' => $product->slug, 'chapter' => $chapter->slug]) }}"
                               class="flex min-h-12 items-center justify-between gap-3 hover:text-primary">
                                <span><span class="text-subtle">{{ $loop->iteration }}.</span> {{ $chapter->title }}</span>
                                @if ($purchase->last_chapter_id === $chapter->id)
                                    <x-ui.badge tone="info">Você parou aqui</x-ui.badge>
                                @endif
                            </a>
                        </li>
                    @endforeach
                </ol>
            </section>
        @endif

        <section class="rounded-card bg-surface p-6 shadow-card" aria-labelledby="files-heading">
            <h2 id="files-heading" class="text-lg font-semibold text-ink">Arquivos</h2>

            @if ($product->files->isEmpty())
                <p class="mt-2 text-sm text-muted">Os arquivos ainda estão sendo preparados. Você recebe um aviso quando estiverem prontos.</p>
            @else
                <ul class="mt-3 space-y-3">
                    @foreach ($product->files as $file)
                        <li class="flex flex-col gap-3 rounded-control border border-line p-4 sm:flex-row sm:items-center">
                            <span class="flex size-11 shrink-0 items-center justify-center rounded-control bg-primary-soft text-primary">
                                @svg('heroicon-o-' . $file->iconName(), 'size-6')
                            </span>
                            <span class="min-w-0 flex-1">
                                <span class="block font-medium text-ink">{{ $file->label }}</span>
                                <span class="block text-xs text-subtle">
                                    <span class="uppercase">{{ $file->extension() }}</span> · {{ $file->formattedSize() }}
                                    @if ($file->version) · versão {{ $file->version }} @endif
                                    @if ($last = $lastDownloads[$file->id] ?? null)
                                        · baixado em {{ \Illuminate\Support\Carbon::parse($last)->format('d/m/Y') }}
                                    @endif
                                </span>
                            </span>
                            {{-- Plain link (no form): the browser shows the native download progress. --}}
                            <x-ui.button :href="route('account.files.download', ['file' => $file->id])" variant="tonal" download>
                                @svg('heroicon-o-arrow-down-tray', 'size-5')
                                Baixar
                            </x-ui.button>
                        </li>
                    @endforeach
                </ul>

                <p class="mt-4 text-xs text-subtle">
                    Material de uso pessoal, conforme a <a href="{{ route('legal.license') }}" class="underline">Licença de Uso</a>.
                    Os downloads ficam registrados na sua conta.
                </p>
            @endif
        </section>

        @unless ($product->isPublished())
            <x-ui.alert tone="info" icon="information-circle">
                Este material não está mais à venda, mas continua seu para sempre.
            </x-ui.alert>
        @endunless
    </div>
</x-layouts.account>
