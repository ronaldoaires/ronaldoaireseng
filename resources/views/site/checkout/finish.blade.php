@php($product = $purchase->product)

<x-layouts.site title="Sua compra" :noindex="true">
    <div class="mx-auto w-full max-w-md px-4 py-12">
        <div class="rounded-card bg-surface p-6 text-center shadow-card">
            @if ($purchase->isPaid())
                <span class="mx-auto flex size-14 items-center justify-center rounded-full bg-success-soft text-success">
                    @svg('heroicon-o-check-circle', 'size-8')
                </span>

                <h1 class="mt-4 text-2xl font-semibold">Compra confirmada!</h1>
                <p class="mt-3 text-sm text-muted">
                    <strong class="text-ink">{{ $product->title }}</strong> já está na sua biblioteca.
                </p>

                <div class="mt-6 flex flex-col gap-3">
                    <x-ui.button :href="route('account.library.show', ['product' => $product->slug])">Abrir na biblioteca</x-ui.button>
                    @if ($product->hasOnlineReader())
                        <x-ui.button :href="route('account.reader', ['product' => $product->slug])" variant="outline">Começar a ler</x-ui.button>
                    @endif
                </div>
            @elseif ($purchase->status === 'pending')
                <span class="mx-auto flex size-14 items-center justify-center rounded-full bg-primary-soft text-primary">
                    @svg('heroicon-o-clock', 'size-8')
                </span>

                <h1 class="mt-4 text-2xl font-semibold">Estamos confirmando seu pagamento</h1>
                <p class="mt-3 text-sm text-muted">
                    Se você pagou com Pix, pode levar alguns minutos. Assim que for confirmado, o material aparece na sua biblioteca.
                </p>

                <div class="mt-6 flex flex-col gap-3">
                    <x-ui.button :href="route('account.library')">Ir para a biblioteca</x-ui.button>
                    <x-ui.button :href="$product->url" variant="outline">Voltar para a página</x-ui.button>
                </div>
            @else
                <span class="mx-auto flex size-14 items-center justify-center rounded-full bg-red-100 text-red-700">
                    @svg('heroicon-o-exclamation-triangle', 'size-8')
                </span>

                <h1 class="mt-4 text-2xl font-semibold">Não foi possível concluir</h1>
                <p class="mt-3 text-sm text-muted">
                    O pagamento não foi aprovado e nada foi cobrado. Você pode tentar de novo.
                </p>

                <x-ui.button :href="route('checkout.show', ['product' => $product->slug])" class="mt-6">Tentar novamente</x-ui.button>
            @endif
        </div>
    </div>
</x-layouts.site>
