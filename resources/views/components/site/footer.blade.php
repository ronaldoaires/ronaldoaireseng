@php
$person   = config('showcase.person');
$profiles = config('showcase.profiles');
$areas    = \App\Support\ShowcaseMenu::areas();
$link     = 'block py-1.5 text-sm leading-snug text-muted transition hover:text-primary';
@endphp

<footer class="mt-20">
    {{-- Hire call: the one colored moment of the footer, on every page. --}}
    {{-- The card overlaps the white part below it by half its height. --}}
    <div class="relative z-10 mx-auto -mb-24 max-w-6xl px-4">
        <div class="relative overflow-hidden rounded-2xl bg-ink px-6 py-10 text-white sm:px-12 sm:py-12">
            {{-- Faint drawing axes, echoing the home hero. --}}
            <div class="pointer-events-none absolute inset-0 opacity-60" aria-hidden="true">
                <span class="axis-line-v left-[62%]" style="--axis-color: rgb(255 255 255 / 0.25)"></span>
                <span class="axis-line-v left-[84%]" style="--axis-color: rgb(255 255 255 / 0.25)"></span>
                <span class="axis-line-h top-[70%]" style="--axis-color: rgb(255 255 255 / 0.25)"></span>
            </div>

            <div class="relative flex flex-col gap-8 lg:flex-row lg:items-center lg:justify-between">
                <div class="max-w-xl">
                    <h2 class="font-display text-3xl font-bold tracking-tight sm:text-4xl">Vamos conversar sobre seu projeto.</h2>
                    <p class="mt-3 text-lg text-white/85">Conte o que você precisa. Analiso seu pedido e envio uma proposta com escopo, prazo e valor.</p>
                </div>
                <div class="flex flex-col gap-3 sm:flex-row">
                    <x-ui.button :href="route('quotes.create')" variant="light" size="lg">Solicitar orçamento</x-ui.button>
                    <a href="{{ route('contact.show') }}" class="inline-flex min-h-14 items-center justify-center rounded-full border border-white/40 px-6 font-semibold transition hover:bg-white/10">Fale comigo</a>
                </div>
            </div>
        </div>
    </div>

    <div class="border-t border-line bg-surface">
        <div class="mx-auto grid max-w-6xl gap-x-8 gap-y-10 px-4 pb-12 pt-40 sm:grid-cols-2 lg:grid-cols-12">
            {{-- Brand: logo, who, and the social profiles. --}}
            <div class="sm:col-span-2 lg:col-span-4">
                <a href="{{ route('home') }}" class="inline-flex items-center gap-3">
                    <x-site.brand-badge size="size-14" logo="size-10" />
                    <span class="leading-none">
                        <span class="block font-display text-2xl font-bold tracking-tight text-ink">{{ $person['name'] }}</span>
                        <span class="mt-1.5 block text-sm text-subtle">{{ $profiles['engineering']['title'] }}</span>
                    </span>
                </a>
                <p class="mt-5 max-w-sm text-sm leading-relaxed text-muted">
                    Projetos estruturais, ebooks e planilhas de cálculo de quem projeta, ensina e desenvolve ferramentas para engenharia.
                </p>

                {{-- Social profiles: generous tap targets and a visible focus state. --}}
                <ul class="mt-6 flex flex-wrap gap-3">
                    @foreach ($person['links'] as $label => $url)
                    <li>
                        <a href="{{ $url }}" target="_blank" rel="noopener me" aria-label="{{ $label }}" title="{{ $label }}"
                           class="flex size-12 items-center justify-center rounded-full bg-primary text-white transition hover:bg-primary-hover focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary">
                            <x-icons.social :name="$label" class="size-6" />
                        </a>
                    </li>
                    @endforeach
                </ul>
            </div>

            {{-- Site map: services side by side, so every visitor sees both areas. --}}
            @foreach ($areas as $area)
            <nav aria-label="{{ $area['heading'] }}" class="lg:col-span-3">
                <h2 class="font-display text-lg font-bold text-ink">
                    <a href="{{ $area['url'] }}" class="hover:text-primary">{{ $area['heading'] }}</a>
                </h2>
                <ul class="mt-3">
                    @foreach ($area['services'] as $service)
                    <li><a href="{{ $service['url'] }}" class="{{ $link }}">{{ $service['name'] }}</a></li>
                    @endforeach
                </ul>
            </nav>
            @endforeach

            <nav aria-label="Loja e contato" class="lg:col-span-2">
                <h2 class="font-display text-lg font-bold text-ink">Loja</h2>
                <ul class="mt-3">
                    <li><a href="{{ route('products.index', ['type' => 'ebooks']) }}" class="{{ $link }}">Ebooks</a></li>
                    <li><a href="{{ route('products.index', ['type' => 'planilhas']) }}" class="{{ $link }}">Planilhas</a></li>
                    <li><a href="{{ route('services.index') }}" class="{{ $link }}">Meus serviços</a></li>
                    <li><a href="{{ route('contact.show') }}" class="{{ $link }}">Fale comigo</a></li>
                </ul>
            </nav>
        </div>
    </div>

    <div class="border-t border-line bg-surface">
        <div class="mx-auto flex max-w-6xl flex-col gap-3 px-4 py-6 text-sm text-subtle sm:flex-row sm:items-center sm:justify-between">
            <p>© {{ date('Y') }} {{ config('app.name') }}. Todos os direitos reservados.</p>

            <nav aria-label="Legal" class="flex flex-wrap gap-x-4 gap-y-2">
                <a href="{{ route('legal.terms') }}" class="hover:underline">Termos de Uso</a>
                <a href="{{ route('legal.license') }}" class="hover:underline">Licença de Uso</a>
                <a href="{{ route('legal.privacy') }}" class="hover:underline">Política de Privacidade</a>
            </nav>
        </div>
    </div>
</footer>
