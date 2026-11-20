<x-layouts.site
    :title="$service->meta_title"
    :description="$service->meta_description"
    :image="$service->display_image_url"
    :json-ld="$jsonLd">

    @php($sales = config('showcase.service_sales.' . $service->slug))

    {{-- Hero: promise + the two ways to get in touch, above the fold. --}}
    <x-site.page-hero>
        <div class="mx-auto grid max-w-6xl gap-10 px-4 py-12 sm:py-16 lg:grid-cols-2 lg:items-center">
            <div>
                <x-site.breadcrumb :items="$breadcrumbs" />

                <h1 class="mt-6 font-display text-4xl font-bold leading-[1.05] tracking-tight text-ink sm:text-6xl">{{ $service->name }}</h1>

                <p class="mt-4 text-lg text-muted">{{ $service->headline ?: $service->summary }}</p>

                <x-site.showcase.cta-buttons :service="$service" class="mt-8" />

                @php($profile = config('showcase.profiles.' . $service->area->value))
                <p class="mt-6 flex items-center gap-2 text-sm text-subtle">
                    @svg('heroicon-o-shield-check', 'size-5 text-primary')
                    {{ $profile['title'] }} {{ config('showcase.person.name') }} · {{ $profile['badge'] }}
                </p>
            </div>

            @if ($service->display_image_url)
            <figure @class(['overflow-hidden rounded-[1.5rem]' => $service->slug !== 'projeto-estrutural', 'bg-white ring-1 ring-line' => $service->slug !== 'projeto-estrutural'])>
                <img
                    src="{{ $service->display_image_url }}"
                    alt="{{ $service->slug === 'projeto-estrutural' ? 'Ilustração de uma estrutura com cores conceituais de esforços em vigas e pilares' : $service->name }}"
                    width="800"
                    height="600"
                    fetchpriority="high"
                    @class(['aspect-[4/3] w-full', 'object-contain' => $service->slug === 'projeto-estrutural', 'object-cover' => $service->slug !== 'projeto-estrutural'])>
            </figure>
            @endif
        </div>
    </x-site.page-hero>

    <div class="mx-auto max-w-6xl space-y-14 px-4 py-14">
        @if (filled($sales['benefits'] ?? null))
        <section aria-labelledby="service-benefits-heading">
            <h2 id="service-benefits-heading" class="font-display text-2xl font-bold tracking-tight text-ink sm:text-3xl">O que muda com um projeto bem definido</h2>
            <div class="mt-6 grid gap-4 md:grid-cols-3">
                @foreach ($sales['benefits'] as $benefit)
                <div class="rounded-card border border-line bg-surface p-6 shadow-card">
                    <h3 class="font-display text-lg font-bold text-ink">{{ $benefit['title'] }}</h3>
                    <p class="mt-2 leading-relaxed text-muted">{{ $benefit['text'] }}</p>
                </div>
                @endforeach
            </div>
        </section>
        @endif

        <div class="grid gap-8 lg:grid-cols-3">
            @if ($body = \App\Support\RichContent::render($service->body))
            <article class="rich-content rounded-card bg-surface p-6 shadow-card sm:p-8 lg:col-span-2">
                {{ $body }}
            </article>
            @endif

            @if (filled($service->deliverables))
            <aside @class(['h-fit rounded-card bg-surface p-6 shadow-card', 'lg:col-span-3' => ! $body])>
                <h2 class="text-lg font-bold text-ink">O que está incluso</h2>

                <ul class="mt-4 space-y-3">
                    @foreach ($service->deliverables as $item)
                    <li class="flex items-start gap-3 text-sm text-muted">
                        @svg('heroicon-o-check-circle', 'mt-0.5 size-5 shrink-0 text-success')
                        {{ $item }}
                    </li>
                    @endforeach
                </ul>

                <x-site.showcase.cta-buttons :service="$service" stacked class="mt-6" />
            </aside>
            @endif
        </div>

        <x-site.showcase.steps />

        @if ($projects->isNotEmpty())
        <section aria-labelledby="portfolio-heading">
            <div class="flex items-end justify-between gap-4">
                <h2 id="portfolio-heading" class="text-2xl font-bold tracking-tight text-ink">Projetos de {{ mb_strtolower($service->name) }}</h2>
                <a href="{{ route('portfolio.index', ['servico' => $service->slug]) }}" class="inline-flex min-h-12 items-center gap-1.5 text-sm font-semibold text-primary hover:underline">
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

        <x-site.showcase.responsible :area="$service->area" />

        @if (filled($service->faq))
        <section aria-labelledby="faq-heading">
            <h2 id="faq-heading" class="text-2xl font-bold tracking-tight text-ink">Perguntas frequentes</h2>

            {{-- Native <details>: accessible and works without JavaScript. --}}
            <div class="mt-6 divide-y divide-line overflow-hidden rounded-card bg-surface shadow-card">
                @foreach ($service->faq as $item)
                <details class="group">
                    <summary class="flex min-h-14 cursor-pointer list-none items-center justify-between gap-4 px-6 py-4 font-semibold text-ink [&::-webkit-details-marker]:hidden">
                        {{ $item['question'] }}
                        @svg('heroicon-o-chevron-down', 'size-5 shrink-0 text-subtle transition group-open:rotate-180')
                    </summary>
                    <p class="whitespace-pre-line px-6 pb-5 leading-relaxed text-muted">{{ $item['answer'] }}</p>
                </details>
                @endforeach
            </div>
        </section>
        @endif

        @if ($sales)
        <section aria-labelledby="service-cta-heading" class="rounded-card bg-ink px-6 py-9 text-white sm:px-10 sm:py-12">
            <h2 id="service-cta-heading" class="font-display text-2xl font-bold sm:text-3xl">{{ $sales['cta_heading'] }}</h2>
            <p class="mt-3 max-w-2xl text-white/80">{{ $sales['cta_text'] }}</p>
            <x-site.showcase.cta-buttons :service="$service" inverse class="mt-7" />
        </section>
        @endif

        @if ($related->isNotEmpty())
        <section aria-labelledby="related-heading">
            <h2 id="related-heading" class="text-xl font-bold tracking-tight text-ink">Outros serviços de {{ mb_strtolower($service->area->getLabel()) }}</h2>

            <div class="mt-6 grid gap-6 sm:grid-cols-2">
                @foreach ($related as $item)
                <x-site.showcase.service-card :service="$item" />
                @endforeach
            </div>
        </section>
        @endif
    </div>
</x-layouts.site>
