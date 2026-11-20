@props(['id', 'title', 'size' => 'wide'])

@php
    $width = match ($size) {
        'reading' => 'w-[min(96vw,78rem)]',
        'image' => 'w-[min(96vw,58rem)]',
        default => 'w-[min(96vw,42rem)]',
    };
@endphp

<dialog id="{{ $id }}" aria-labelledby="{{ $id }}-title" data-modal-backdrop class="site-modal fixed inset-0 m-auto max-h-[92dvh] max-w-none overflow-y-auto rounded-card border border-line bg-white p-0 text-ink shadow-2xl {{ $width }}">
    <div class="sticky top-0 z-10 flex items-center justify-between gap-4 border-b border-line bg-white px-4 py-3 sm:px-6">
        <h2 id="{{ $id }}-title" class="font-display text-lg font-bold sm:text-xl">{{ $title }}</h2>
        <button type="button" data-modal-close aria-label="Fechar {{ $title }}" class="flex size-11 shrink-0 items-center justify-center rounded-control text-ink hover:bg-primary-soft focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary">
            @svg('heroicon-o-x-mark', 'size-6', ['aria-hidden' => 'true'])
        </button>
    </div>
    <div {{ $attributes->class(['p-4 sm:p-6']) }}>
        {{ $slot }}
    </div>
</dialog>
