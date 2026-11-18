<x-layouts.site
    title="Engenheiro Civil · Ebooks, planilhas e projetos"
    description="Ebooks e planilhas de engenharia civil, calculadoras e projetos estruturais com o engenheiro Ronaldo Aires (CREA-MT 62629)."
    :json-ld="$jsonLd">

    {{-- Hero: a light panel drawn like a structural plan (column axes A-C / 1-2).
         Text on the left; the rebar render from the ebook cover on the right,
         hanging from just under the top edge and running into the bottom edge. --}}
    <section class="px-3 pt-3 sm:px-4 sm:pt-4">
        <div class="relative mx-auto max-w-7xl overflow-hidden rounded-[2rem] bg-gradient-to-br from-white via-[#eef1ff] to-[#e9e4ff] ring-1 ring-line">
            <x-site.drawing-axes />

            <div class="relative mx-auto grid max-w-6xl gap-8 px-6 pt-16 sm:px-10 sm:pt-20 lg:grid-cols-12 lg:gap-6 lg:pt-0">
                <div class="lg:col-span-6 lg:self-center lg:py-24">
                    <h1 class="font-display text-4xl font-bold leading-[1.05] tracking-tight text-ink sm:text-6xl">
                        Engenharia civil explicada por quem projeta.
                    </h1>
                    <p class="mt-5 max-w-xl text-lg leading-relaxed text-muted">
                        Ebooks com versão digital, planilhas de cálculo seguindo as normas brasileiras e projetos estruturais para todo o Brasil.
                    </p>

                    <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                        <x-ui.button :href="route('products.index', ['type' => 'ebooks'])" variant="gradient" size="lg">Ver ebooks</x-ui.button>
                        <x-ui.button :href="route('products.index', ['type' => 'planilhas'])" size="lg" variant="outline" class="rounded-full!">Ver planilhas</x-ui.button>
                    </div>
                </div>

                <div class="flex justify-center lg:col-span-6 lg:justify-end lg:pt-6">
                    <img
                        src="{{ asset('images/estrutura-armaduras.webp') }}"
                        alt="Armaduras de laje, vigas e pilar em concreto armado"
                        width="1400" height="1456"
                        fetchpriority="high"
                        class="pointer-events-none -mb-2 h-auto w-full max-w-md object-contain object-top sm:max-w-lg lg:max-w-[34rem]">
                </div>
            </div>
        </div>
    </section>

    {{-- Section order: change it here; each section hides itself when empty. --}}
    <x-site.sections.products :products="$products" :owned="$owned" />

    <x-site.sections.ebook-inside />

    <x-site.sections.about />

    <x-site.sections.services :areas="$areas" />

    <x-site.sections.portfolio :projects="$projects" />

</x-layouts.site>
