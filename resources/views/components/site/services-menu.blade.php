@props(['label', 'areas', 'active' => false])

{{-- Desktop "Serviços" submenu: native popover, so Esc, outside click and
     focus return work with no JavaScript (see .menu-popover in app.css). --}}
<div class="relative">
    <button
        type="button"
        popovertarget="services-menu"
        aria-controls="services-menu"
        @class([
            'menu-trigger-services flex min-h-11 items-center gap-1 rounded-control px-3 text-sm font-medium transition hover:bg-primary-soft hover:text-primary',
            'bg-primary-soft text-primary' => $active,
        ])>
        {{ $label }}
        @svg('heroicon-o-chevron-down', 'size-4', ['aria-hidden' => 'true'])
    </button>

    <div id="services-menu" popover="auto" class="menu-popover menu-popover--services w-[min(40rem,calc(100dvw-2rem))] rounded-card bg-surface p-6 text-ink shadow-lg ring-1 ring-line">
        <div @class(["grid gap-6", "sm:grid-cols-2" => count($areas) > 1])>
            @foreach ($areas as $area)
            <div>
                <a href="{{ $area['url'] }}" class="text-xs font-bold uppercase tracking-wide text-primary hover:underline">{{ $area['heading'] }}</a>

                <ul class="mt-3 space-y-1">
                    @foreach ($area['services'] as $service)
                    <li>
                        <a href="{{ $service['url'] }}" class="flex min-h-11 items-center gap-2 rounded-control px-2 text-sm font-medium text-ink hover:bg-canvas hover:text-primary">
                            @svg('heroicon-o-chevron-right', 'size-4 text-subtle', ['aria-hidden' => 'true'])
                            {{ $service['name'] }}
                        </a>
                    </li>
                    @endforeach
                </ul>
            </div>
            @endforeach
        </div>

        <div class="mt-5 flex flex-wrap items-center justify-between gap-3 border-t border-line pt-4 text-sm">
            <a href="{{ route('services.index') }}" class="font-semibold text-primary hover:underline">Ver todos os meus serviços</a>
            <x-ui.button :href="route('quotes.create')" class="min-h-11! px-4">Solicitar orçamento</x-ui.button>
        </div>
    </div>
</div>
