@props(['areas'])

@if ($areas->isNotEmpty())
{{-- Home: each service area as a row of image cards (ebook render or photo),
     with its two actions sized to their text, not stretched. --}}
<section id="servicos" class="bg-canvas" aria-labelledby="services-heading">
    <div class="mx-auto max-w-6xl px-4 py-20">
        <div class="max-w-2xl">
            <h2 id="services-heading" class="font-display text-4xl font-bold tracking-tight text-ink sm:text-5xl">Precisa de um projeto? Fale comigo.</h2>
            <p class="mt-3 text-lg text-muted">
                Projetos em 3D, compatibilizados antes de a obra começar, e sistemas feitos com tecnologia e dados para o seu negócio decidir melhor. Tudo online, para todo o Brasil.
            </p>
        </div>

        <div class="mt-14 space-y-16">
            @foreach ($areas as $area => $services)
            @php
            $enum  = \App\Enums\ServiceArea::from($area);
            $texts = config('showcase.areas.' . $area);
            @endphp

            <div>
                <div class="flex flex-col gap-5 border-b border-line pb-6 lg:flex-row lg:items-end lg:justify-between">
                    <div class="flex items-start gap-4">
                        <span class="flex size-12 shrink-0 items-center justify-center rounded-xl bg-brand-gradient text-white shadow-[0_6px_18px_-6px_rgb(91_92_240/0.55)]">
                            @svg('heroicon-o-' . $texts['icon'], 'size-6')
                        </span>
                        <div>
                            <h3 class="font-display text-2xl font-bold text-ink sm:text-3xl">
                                <a href="{{ route('services.area', ['area' => $enum->slug()]) }}" class="hover:text-primary">{{ $texts['heading'] }}</a>
                            </h3>
                            <p class="mt-1 max-w-xl text-muted">{{ $texts['headline'] }}</p>
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-3">
                        <x-ui.button :href="route('quotes.create')" variant="gradient">Solicitar orçamento</x-ui.button>
                        <x-ui.button :href="route('services.area', ['area' => $enum->slug()])" variant="outline" class="rounded-full!">
                            Ver {{ mb_strtolower($enum->getLabel()) }}
                        </x-ui.button>
                    </div>
                </div>

                {{-- Up to 4 per row; a shorter row (e.g. 3 tech services) stays centered. --}}
                <div class="mt-8 flex flex-wrap justify-center gap-6">
                    @foreach ($services as $service)
                        <x-site.showcase.service-card :service="$service" compact class="w-full sm:w-[calc(50%-0.75rem)] lg:w-[calc(25%-1.125rem)]" />
                    @endforeach
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>
@endif
