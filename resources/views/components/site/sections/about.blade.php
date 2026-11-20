@php
    $person   = config('showcase.person');
    $profiles = config('showcase.profiles');
@endphp

{{-- Who is behind the site: trust is what sells technical material. --}}
<section id="sobre" class="bg-surface" aria-labelledby="about-heading">
    <div class="mx-auto grid max-w-6xl items-center gap-10 px-4 py-16 lg:grid-cols-12 lg:gap-14">
        {{-- Portrait first on desktop: the face is what builds trust. --}}
        <div class="relative mx-auto w-full max-w-sm lg:col-span-5 lg:max-w-none">
            <figure class="relative overflow-hidden rounded-2xl">
                <img
                    src="{{ asset('images/ronaldo-aires-capacete.webp') }}"
                    alt="{{ $person['name'] }}, engenheiro civil, de capacete branco"
                    width="720" height="720"
                    loading="lazy" decoding="async"
                    class="aspect-square w-full object-cover">
                <figcaption class="absolute bottom-4 left-4 rounded-md bg-white px-4 py-2 text-sm font-semibold text-ink">
                    CREA-MT 62629
                </figcaption>
            </figure>
        </div>

        <div class="lg:col-span-7">
            <h2 id="about-heading" class="font-display text-4xl font-bold tracking-tight text-ink sm:text-5xl">{{ $person['name'] }}</h2>
            <p class="mt-2 text-lg font-medium text-primary">{{ $profiles['engineering']['title'] }}, autor dos ebooks e planilhas</p>

            <p class="mt-6 max-w-2xl text-lg leading-relaxed text-muted">
                Projeto estruturas, ensino engenharia e desenvolvo ferramentas de cálculo.
                Aqui reúno o que uso no dia a dia: ebooks diretos ao ponto, planilhas que seguem as normas
                brasileiras e calculadoras para quem projeta e para quem executa.
            </p>

            <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                <x-ui.button :href="route('services.index')" variant="gradient" size="lg">Conheça meus serviços</x-ui.button>
                <x-ui.button :href="route('contact.show')" variant="outline" size="lg" class="rounded-full!">Fale comigo</x-ui.button>
            </div>
        </div>
    </div>
</section>
