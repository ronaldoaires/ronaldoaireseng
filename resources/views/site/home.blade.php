<x-layouts.site
    title="Engenheiro Civil · Ebooks, planilhas e projetos"
    description="Ebooks e planilhas de engenharia civil, calculadoras e projetos estruturais com o engenheiro Ronaldo Aires (CREA-MT 62629)."
    :json-ld="$jsonLd">

    <section class="px-3 pt-3 sm:px-4 sm:pt-4" aria-labelledby="home-heading">
        <div class="relative mx-auto max-w-7xl overflow-hidden rounded-2xl border border-line bg-white lg:rounded-3xl">
            <x-site.drawing-axes class="opacity-65" />

            <div class="relative mx-auto grid max-w-7xl gap-6 px-6 pt-14 sm:px-10 sm:pt-18 lg:min-h-[40rem] lg:grid-cols-12 lg:gap-4 lg:pt-0">
                <div class="lg:col-span-5 lg:self-center lg:py-20">
                    <h1 id="home-heading" class="font-display text-[clamp(2rem,5vw,3.75rem)] font-bold leading-[1.05] tracking-tight text-[#233865]">
                        <span class="block">Precisa de um</span>
                        <span class="block whitespace-nowrap">Projeto estrutural?</span>
                    </h1>
                    <p class="mt-6 max-w-sm text-lg leading-relaxed text-muted">
                        Projetos completos, detalhados e otimizados para facilitar a execução no canteiro e valorizar o seu investimento.
                    </p>

                    <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                        <x-ui.button :href="route('quotes.create')" variant="gradient" size="lg">Solicitar orçamento</x-ui.button>
                        <x-ui.button :href="route('contact.show')" size="lg" variant="outline" class="rounded-full!">Fale comigo</x-ui.button>
                    </div>
                    <p class="mt-7 text-sm font-medium text-subtle">CREA-MT 62629 <span aria-hidden="true" class="mx-2 text-line">/</span> Atendimento online para todo o Brasil</p>
                </div>

                <div class="flex justify-center lg:col-span-7 lg:items-center lg:justify-end">
                    <div data-structure-hero data-structure-model="{{ asset('images/estrutura-residencial-escadas.glb') }}" class="relative w-full max-w-2xl" aria-label="Modelo interativo de um projeto estrutural real">
                        <div data-structure-stage class="relative h-[22rem] w-full sm:h-[30rem] lg:h-[36rem]">
                            <img
                                data-structure-poster
                                src="{{ asset('images/estrutura-residencial-escadas.webp') }}"
                                alt="Estrutura residencial de um projeto real, com escadas, vigas, pilares, lajes e fundações"
                                width="665" height="576"
                                fetchpriority="high"
                                class="absolute inset-0 h-full w-full object-contain">
                        </div>
                        <div data-structure-controls hidden class="mt-1 flex flex-wrap items-center justify-center gap-2 rounded-xl border border-line bg-white/95 px-2 py-2 shadow-sm sm:ml-auto sm:w-fit sm:gap-3">
                            <span class="hidden text-xs font-medium text-muted sm:inline">Arraste para girar</span>
                            <div class="flex flex-wrap items-center justify-center gap-1">
                                <label class="sr-only" for="structure-view">Vista da estrutura</label>
                                <select id="structure-view" data-structure-view class="h-9 rounded-lg border border-line bg-white px-2 text-xs font-semibold text-ink focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary">
                                    <option value="perspective">Perspectiva</option>
                                    <option value="front">Frente</option>
                                    <option value="side">Lateral</option>
                                    <option value="top">Superior</option>
                                </select>
                                <button type="button" data-structure-zoom-out class="flex size-9 items-center justify-center rounded-lg text-lg text-ink hover:bg-brand-50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary" aria-label="Afastar a estrutura">−</button>
                                <button type="button" data-structure-zoom-in class="flex size-9 items-center justify-center rounded-lg text-lg text-ink hover:bg-brand-50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary" aria-label="Aproximar a estrutura">+</button>
                                <button type="button" data-structure-rotate aria-pressed="false" class="flex h-9 items-center justify-center rounded-lg px-2 text-xs font-semibold text-primary hover:bg-brand-50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary" aria-label="Iniciar rotação automática">Girar</button>
                                <button type="button" data-structure-fullscreen aria-pressed="false" class="flex h-9 items-center justify-center rounded-lg px-2 text-xs font-semibold text-primary hover:bg-brand-50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary">Tela cheia</button>
                                <button type="button" data-structure-reset class="flex h-9 items-center justify-center rounded-lg px-2 text-xs font-semibold text-primary hover:bg-brand-50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary">Reiniciar</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Section order: change it here; each section hides itself when empty. --}}
    <x-site.sections.products :products="$products" :owned="$owned" />

    <x-site.sections.about />

</x-layouts.site>
