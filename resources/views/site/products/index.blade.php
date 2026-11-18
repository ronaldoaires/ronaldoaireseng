<x-layouts.site
    :title="$type->pluralLabel() . ' de engenharia civil'"
    :description="$type->headline()"
    :json-ld="$jsonLd">

    <section class="border-b border-line bg-surface">
        <div class="mx-auto max-w-6xl px-4 py-10 sm:py-14">
            <x-site.breadcrumb :items="[['name' => 'Início', 'url' => route('home')], ['name' => $type->pluralLabel()]]" />

            <h1 class="mt-4 text-3xl font-bold tracking-tight text-ink sm:text-4xl">{{ $type->pluralLabel() }}</h1>
            <p class="mt-3 max-w-2xl text-lg text-muted">{{ $type->headline() }}</p>
        </div>
    </section>

    <div class="mx-auto max-w-6xl px-4 py-10">
        @if ($products->isEmpty())
            <x-ui.empty-state
                :icon="$type->iconName()"
                title="Em breve"
                description="Os primeiros materiais estão sendo finalizados. Crie sua conta para ser avisado no lançamento.">
                @guest
                    <x-ui.button :href="route('register')">Criar conta grátis</x-ui.button>
                @else
                    <x-ui.button :href="route('home')" variant="tonal">Voltar para o início</x-ui.button>
                @endguest
            </x-ui.empty-state>
        @else
            <p class="text-sm text-muted">
                {{ $products->count() }} {{ $products->count() === 1 ? mb_strtolower($type->getLabel()) : mb_strtolower($type->pluralLabel()) }}
            </p>

            <div class="mt-4 grid grid-cols-2 gap-4 sm:gap-6 lg:grid-cols-3">
                @foreach ($products as $product)
                    <x-site.product-card :product="$product" :owned="in_array($product->id, $owned, true)" heading-level="h2" />
                @endforeach
            </div>
        @endif
    </div>
</x-layouts.site>
