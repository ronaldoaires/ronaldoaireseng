{{-- Distraction-free reader: the site layout without the account sidebar,
     a narrow text column and big previous/next buttons for the thumb. --}}
<x-layouts.site :title="$chapter->title . ' · ' . $product->title" :noindex="true">
    <div class="sticky top-0 z-20 border-b border-line bg-surface/95 backdrop-blur">
        <div class="mx-auto flex max-w-3xl items-center gap-3 px-4 py-2">
            <a href="{{ route('account.library.show', ['product' => $product->slug]) }}"
               class="inline-flex size-11 shrink-0 items-center justify-center rounded-control hover:bg-canvas"
               aria-label="Voltar para {{ $product->title }}">
                @svg('heroicon-o-arrow-left', 'size-5')
            </a>

            <div class="min-w-0 flex-1">
                <p class="truncate text-sm font-semibold text-ink">{{ $product->title }}</p>
                <p class="text-xs text-subtle">Capítulo {{ $position }} de {{ $chapters->count() }}</p>
            </div>

            {{-- Table of contents in a native popover: no JavaScript needed. --}}
            <button type="button" popovertarget="reader-toc" aria-controls="reader-toc"
                    class="inline-flex size-11 items-center justify-center rounded-control hover:bg-canvas" aria-label="Sumário">
                @svg('heroicon-o-list-bullet', 'size-5', ['aria-hidden' => 'true'])
            </button>
            <nav id="reader-toc" popover="auto" aria-label="Sumário" class="menu-popover menu-popover--reader max-h-[70dvh] w-72 overflow-y-auto rounded-card bg-surface p-2 shadow-card ring-1 ring-line">
                <ol class="text-sm">
                    @foreach ($chapters as $item)
                        <li>
                            <a href="{{ route('account.reader', ['product' => $product->slug, 'chapter' => $item->slug]) }}"
                               @if ($item->is($chapter)) aria-current="page" @endif
                               @class([
                                   'flex min-h-11 items-center rounded-control px-3',
                                   'bg-primary-soft font-semibold text-on-primary-soft' => $item->is($chapter),
                                   'text-ink hover:bg-canvas' => ! $item->is($chapter),
                               ])>
                                {{ $loop->iteration }}. {{ $item->title }}
                            </a>
                        </li>
                    @endforeach
                </ol>
            </nav>
        </div>

        {{-- Reading progress through the book. --}}
        <div class="h-1 bg-canvas" role="progressbar" aria-label="Progresso no livro" aria-valuemin="1" aria-valuemax="{{ $chapters->count() }}" aria-valuenow="{{ $position }}">
            <div class="h-full bg-primary" style="width: {{ (int) round($position / max(1, $chapters->count()) * 100) }}%"></div>
        </div>
    </div>

    <article class="mx-auto max-w-3xl px-4 py-8 sm:py-12">
        <p class="text-sm font-semibold uppercase tracking-wide text-primary">Capítulo {{ $position }}</p>
        <h1 class="mt-2 text-balance text-3xl font-bold tracking-tight text-ink sm:text-4xl">{{ $chapter->title }}</h1>

        @if ($body = \App\Support\RichContent::render($chapter->body))
            <div class="rich-content reader-content mt-8">{{ $body }}</div>
        @endif

        {{-- Buyer's name in the footer of every chapter: discourages screenshots being shared. --}}
        <p class="mt-12 border-t border-line pt-4 text-center text-xs text-subtle">
            Licenciado para {{ auth()->user()->name }} · uso pessoal
        </p>
    </article>

    <nav aria-label="Navegação entre capítulos" class="mx-auto grid max-w-3xl grid-cols-2 gap-3 px-4 pb-12">
        @if ($previous)
            <a href="{{ route('account.reader', ['product' => $product->slug, 'chapter' => $previous->slug]) }}" rel="prev"
               class="flex min-h-16 flex-col justify-center rounded-card border border-line bg-surface px-4 py-3 hover:border-primary">
                <span class="text-xs text-subtle">Anterior</span>
                <span class="line-clamp-1 text-sm font-semibold text-ink">{{ $previous->title }}</span>
            </a>
        @else
            <span></span>
        @endif

        @if ($next)
            <a href="{{ route('account.reader', ['product' => $product->slug, 'chapter' => $next->slug]) }}" rel="next"
               class="flex min-h-16 flex-col items-end justify-center rounded-card bg-primary px-4 py-3 text-right text-on-primary hover:bg-primary-hover">
                <span class="text-xs text-on-primary/80">Próximo</span>
                <span class="line-clamp-1 text-sm font-semibold">{{ $next->title }}</span>
            </a>
        @else
            <a href="{{ route('account.library.show', ['product' => $product->slug]) }}"
               class="flex min-h-16 flex-col items-end justify-center rounded-card bg-success px-4 py-3 text-right text-white">
                <span class="text-xs text-white/80">Fim</span>
                <span class="text-sm font-semibold">Voltar à biblioteca</span>
            </a>
        @endif
    </nav>
</x-layouts.site>
