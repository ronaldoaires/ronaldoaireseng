<x-layouts.site
    :title="$texts['heading']"
    :description="$texts['description']"
    :json-ld="$jsonLd">

    <x-site.page-hero>
        <div class="mx-auto max-w-6xl px-4 py-14 sm:py-20">
            <x-site.breadcrumb :items="$breadcrumbs" />

            <h1 class="mt-6 max-w-3xl font-display text-4xl font-bold leading-[1.05] tracking-tight text-ink sm:text-6xl">{{ $texts['heading'] }}</h1>
            <p class="mt-4 max-w-2xl text-lg text-muted">{{ $texts['headline'] }}</p>

            <x-site.showcase.cta-buttons class="mt-8" />
        </div>
    </x-site.page-hero>

    <div class="mx-auto max-w-6xl space-y-16 px-4 py-14">
        <section aria-labelledby="services-heading">
            <h2 id="services-heading" class="text-2xl font-bold tracking-tight text-ink sm:text-3xl">O que eu faço</h2>
            <p class="mt-2 max-w-2xl text-muted">{{ $texts['description'] }}</p>

            <div class="mt-6 grid gap-6 sm:grid-cols-2">
                @foreach ($services as $service)
                <x-site.showcase.service-card :service="$service" />
                @endforeach
            </div>
        </section>

        @if ($projects->isNotEmpty())
        <section aria-labelledby="portfolio-heading">
            <div class="flex items-end justify-between gap-4">
                <h2 id="portfolio-heading" class="text-2xl font-bold tracking-tight text-ink sm:text-3xl">Projetos recentes</h2>
                <a href="{{ route('portfolio.index', ['area' => $area->slug()]) }}" class="inline-flex min-h-12 items-center gap-1.5 text-sm font-semibold text-primary hover:underline">
                    Ver todos
                    @svg('heroicon-o-arrow-right', 'size-4')
                </a>
            </div>

            <div class="mt-6 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($projects as $project)
                <x-site.showcase.project-card :project="$project" />
                @endforeach
            </div>
        </section>
        @endif

        <x-site.showcase.steps />

        <x-site.showcase.responsible :area="$area" />
    </div>
</x-layouts.site>
