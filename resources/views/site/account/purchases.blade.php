<x-layouts.account title="Compras">
    <x-account.page-header
        title="Compras"
        description="Histórico dos seus pedidos e pagamentos." />

    @if ($purchases->isEmpty())
        <x-ui.empty-state
            icon="shopping-bag"
            title="Você ainda não fez compras"
            description="Seus pedidos de ebooks e planilhas aparecem aqui.">
            <x-ui.button :href="route('products.index', ['type' => 'ebooks'])">Ver ebooks</x-ui.button>
        </x-ui.empty-state>
    @else
        <ul class="space-y-3">
            @foreach ($purchases as $purchase)
                @php($product = $purchase->product)
                <li class="flex flex-col gap-3 rounded-card bg-surface p-4 shadow-card sm:flex-row sm:items-center">
                    <div class="min-w-0 flex-1">
                        <h2 class="truncate font-semibold text-ink">{{ $product?->title ?? 'Produto removido' }}</h2>
                        <p class="mt-1 text-sm text-muted">
                            {{ ($purchase->paid_at ?? $purchase->updated_at)->format('d/m/Y \à\s H:i') }}
                            · {{ $purchase->paymentMethodLabel() }}
                        </p>
                    </div>

                    <div class="flex items-center justify-between gap-4 sm:justify-end">
                        <div class="flex flex-col items-start gap-1 sm:items-end">
                            <span class="font-semibold text-ink">{{ (float) $purchase->amount > 0 ? \App\Models\Product::money($purchase->amount) : 'Grátis' }}</span>
                            <x-ui.badge :tone="$purchase->statusTone()">{{ $purchase->statusLabel() }}</x-ui.badge>
                        </div>

                        @if ($product)
                            @if ($purchase->isPaid())
                                <x-ui.button :href="route('account.library.show', ['product' => $product->slug])" variant="tonal">Abrir</x-ui.button>
                            @elseif ($purchase->status === 'pending' && $product->isPublished() && ! $purchase->isManual())
                                @if ($purchase->payment_id !== null)
                                    <x-ui.button :href="route('checkout.finish', ['purchase' => $purchase->id, 'payment_id' => $purchase->payment_id])" variant="outline">Acompanhar pagamento</x-ui.button>
                                @else
                                    <x-ui.button :href="route('checkout.show', ['product' => $product->slug])" variant="outline">Continuar pagamento</x-ui.button>
                                @endif
                            @elseif (in_array($purchase->status, ['failed', 'cancelled'], true) && $product->isPublished() && ! $purchase->isManual())
                                <x-ui.button :href="route('checkout.show', ['product' => $product->slug])" variant="outline">Pagar novamente</x-ui.button>
                            @endif
                        @endif
                    </div>
                </li>
            @endforeach
        </ul>

        {{ $purchases->links('components.site.pagination') }}
    @endif
</x-layouts.account>
