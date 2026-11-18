@props(['product', 'owns' => false])

{{-- Price + main call to action. Used in the hero and again at the end of the page. --}}
<div {{ $attributes->merge(['class' => 'rounded-card bg-surface p-5 text-ink shadow-card sm:p-6']) }}>
    @if ($owns)
        <p class="flex items-center gap-2 font-semibold text-success">
            @svg('heroicon-o-check-badge', 'size-6')
            Você já tem este material
        </p>
        <x-ui.button :href="route('account.library.show', ['product' => $product->slug])" size="lg" class="mt-4 w-full">
            Abrir na biblioteca
        </x-ui.button>
    @else
        @if ((float) $product->price <= 0)
            <p class="text-3xl font-bold leading-none text-success">Grátis</p>
            <p class="mt-2 text-sm text-muted">Só é preciso criar uma conta.</p>
        @else
            @if ($product->hasDiscount())
                <p class="text-sm text-subtle">
                    De <span class="line-through">{{ $product->formattedCompareAtPrice() }}</span>
                    <span class="ml-1 rounded-full bg-accent px-2 py-0.5 text-xs font-bold text-brand-950">-{{ $product->discountPercent() }}%</span>
                </p>
            @endif
            <p class="mt-1 text-4xl font-bold leading-none tracking-tight">{{ $product->formattedPrice() }}</p>
            <p class="mt-2 text-sm text-muted">Pagamento único no Pix ou cartão. Acesso imediato e vitalício.</p>
        @endif

        <x-ui.button :href="route('checkout.show', ['product' => $product->slug])" size="lg" class="mt-5 w-full">
            {{ (float) $product->price <= 0 ? 'Quero receber' : 'Comprar agora' }}
        </x-ui.button>

        <ul class="mt-4 space-y-2 text-sm text-muted">
            @if ($product->hasOnlineReader())
                <li class="flex items-start gap-2">
                    @svg('heroicon-o-device-phone-mobile', 'mt-0.5 size-5 shrink-0 text-primary')
                    PDF para baixar + versão digital para ler no celular
                </li>
            @else
                <li class="flex items-start gap-2">
                    @svg('heroicon-o-arrow-down-tray', 'mt-0.5 size-5 shrink-0 text-primary')
                    Download na sua conta, quantas vezes precisar
                </li>
            @endif
            <li class="flex items-start gap-2">
                @svg('heroicon-o-arrow-path', 'mt-0.5 size-5 shrink-0 text-primary')
                Atualizações desta versão sem custo
            </li>
            <li class="flex items-start gap-2">
                @svg('heroicon-o-lock-closed', 'mt-0.5 size-5 shrink-0 text-primary')
                Pagamento seguro pelo Mercado Pago
            </li>
        </ul>
    @endif
</div>
