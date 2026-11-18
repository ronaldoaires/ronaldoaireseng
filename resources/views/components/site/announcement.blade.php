{{-- Thin bar above the header (config site.announcement.enabled / SITE_ANNOUNCEMENT). --}}
@if (config('site.announcement.enabled'))
<div class="bg-brand-900 text-white">
    <div class="mx-auto flex max-w-6xl flex-col items-center gap-2 px-4 py-2.5 text-center text-sm sm:flex-row sm:justify-center sm:gap-4 sm:text-left">
        <p class="leading-snug">
            <span class="mr-1 inline-flex items-center rounded-full bg-accent px-2 py-0.5 text-[11px] font-bold uppercase tracking-wide text-brand-950">Novo</span>
            Ebooks e planilhas de engenharia civil, com versão digital para ler no celular.
        </p>
        <a
            href="{{ route('products.index', ['type' => 'ebooks']) }}"
            class="inline-flex shrink-0 items-center gap-1.5 rounded-full bg-white px-3 py-1 text-xs font-semibold text-brand-700 transition hover:bg-brand-50">
            Conhecer
            @svg('heroicon-o-arrow-right', 'size-4')
        </a>
    </div>
</div>
@endif
