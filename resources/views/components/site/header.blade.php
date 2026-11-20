@php($items = \App\Support\SiteNav::items())

{{-- Light, sticky bar: the brand mark on the left, the hire action on the right. --}}
<header class="sticky top-0 z-40 border-b border-line/80 bg-white/85 text-ink backdrop-blur-md">
    <div class="mx-auto flex h-18 max-w-6xl items-center justify-between gap-2 px-3 sm:gap-6 sm:px-4">
        <a href="{{ route('home') }}" class="group flex items-center gap-3 rounded-xl focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-primary">
            <x-site.brand-badge size="size-13" logo="size-10" />
            <span class="min-w-0 leading-none">
                <span class="block whitespace-nowrap font-display text-base font-bold tracking-tight sm:text-2xl">Ronaldo Aires</span>
                <span class="mt-1 block whitespace-nowrap text-[10px] font-medium text-subtle sm:text-xs">Engenharia e Tecnologia</span>
            </span>
        </a>

        {{-- Desktop navigation. On mobile the bottom bar takes over. --}}
        <nav class="hidden flex-1 items-center justify-center gap-1 md:flex" aria-label="Principal">
            @foreach ($items as $item)
                @if (($item['submenu'] ?? null) === 'services' && ($menuAreas ??= \App\Support\ShowcaseMenu::areas()))
                    <x-site.services-menu :label="$item['label']" :areas="$menuAreas" :active="$item['is_active']" />
                    @continue
                @endif

                <a
                    href="{{ $item['url'] }}"
                    @class([
                        'relative rounded-control px-3 py-2 text-sm font-medium transition hover:bg-primary-soft hover:text-primary',
                        'text-primary after:absolute after:inset-x-3 after:-bottom-0.5 after:h-0.5 after:rounded-full after:bg-brand-gradient' => $item['is_active'],
                    ])
                    @if ($item['is_active']) aria-current="page" @endif
                >
                    {{ $item['label'] }}
                </a>
            @endforeach
        </nav>

        <div class="flex items-center gap-2">
            <x-site.user-menu />
        </div>
    </div>
</header>
