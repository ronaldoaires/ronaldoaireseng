@props(['products', 'owned' => []])

@if ($products->isNotEmpty())
<section id="materiais" class="bg-canvas" aria-labelledby="products-heading">
    <div class="mx-auto max-w-6xl px-4 py-14">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div class="max-w-2xl">
                <h2 id="products-heading" class="text-3xl font-bold tracking-tight text-ink">Ebooks e planilhas</h2>
                <p class="mt-2 text-lg text-muted">Material técnico escrito por quem projeta, para você aplicar na obra e no escritório.</p>
            </div>

            <div class="flex gap-2">
                <x-ui.button :href="route('products.index', ['type' => 'ebooks'])" variant="tonal">Ebooks</x-ui.button>
                <x-ui.button :href="route('products.index', ['type' => 'planilhas'])" variant="tonal">Planilhas</x-ui.button>
            </div>
        </div>

        <div class="mt-8 grid grid-cols-2 gap-4 sm:gap-6 lg:grid-cols-3">
            @foreach ($products as $product)
                <x-site.product-card :product="$product" :owned="in_array($product->id, $owned, true)" />
            @endforeach
        </div>
    </div>
</section>
@endif
