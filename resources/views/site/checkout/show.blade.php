<x-layouts.site :title="'Comprar ' . $product->title" :noindex="true">
    <div class="mx-auto max-w-6xl px-4 py-8">
        <h1 class="text-2xl font-bold tracking-tight text-ink">{{ $isFree ? 'Receber material' : 'Finalizar compra' }}</h1>
        <p class="mt-1 text-sm text-muted">
            {{ $isFree ? 'Confirme e o material vai direto para a sua biblioteca.' : 'Confira o pedido e complete seus dados para seguir ao pagamento.' }}
        </p>

        {{-- On phones the order summary comes first: people check what they are buying before typing. --}}
        <div class="mt-6 grid gap-6 lg:grid-cols-5">
            <aside class="h-fit rounded-card bg-surface p-6 shadow-card lg:order-2 lg:col-span-2">
                <div class="flex gap-4">
                    @if ($product->cover_thumb_url)
                        <img src="{{ $product->cover_thumb_url }}" alt="" width="72" height="96" class="aspect-[3/4] w-18 shrink-0 rounded-control object-cover">
                    @endif
                    <div class="min-w-0">
                        <p class="text-sm text-subtle">{{ $product->type->getLabel() }}</p>
                        <h2 class="mt-0.5 text-lg font-bold leading-snug text-ink">{{ $product->title }}</h2>
                    </div>
                </div>

                <div class="mt-4 border-t border-line pt-4">
                    @if ($isFree)
                        <p class="text-3xl font-bold leading-none text-success">Grátis</p>
                    @else
                        @if ($product->hasDiscount())
                            <p class="text-sm text-subtle line-through">{{ $product->formattedCompareAtPrice() }}</p>
                        @endif
                        <p class="text-3xl font-bold leading-none text-ink">{{ $product->formattedPrice() }}</p>
                        <p class="mt-2 text-sm text-muted">Pagamento único. Você escolhe Pix ou cartão no ambiente seguro do Mercado Pago.</p>
                    @endif
                </div>

                <ul class="mt-4 space-y-2 border-t border-line pt-4 text-sm text-muted">
                    <li class="flex items-start gap-2">
                        @svg('heroicon-o-check-circle', 'mt-0.5 size-5 shrink-0 text-success')
                        Fica na sua biblioteca para baixar quando precisar
                    </li>
                    @if ($product->hasOnlineReader())
                        <li class="flex items-start gap-2">
                            @svg('heroicon-o-check-circle', 'mt-0.5 size-5 shrink-0 text-success')
                            Inclui a versão digital para ler online
                        </li>
                    @endif
                </ul>
            </aside>

            <form
                method="POST"
                action="{{ route('checkout.store', ['product' => $product->slug]) }}"
                class="space-y-6 rounded-card bg-surface p-6 shadow-card lg:order-1 lg:col-span-3">
                @csrf

                @unless ($isFree)
                    <div>
                        <h2 class="text-lg font-semibold text-ink">Seus dados</h2>
                        <p class="mt-1 text-sm text-muted">Pedimos só o necessário para identificar seu pagamento.</p>
                    </div>

                    <x-ui.input
                        name="cpf"
                        label="CPF"
                        :value="$cpf"
                        data-mask="cpf"
                        inputmode="numeric"
                        autocomplete="off"
                        placeholder="000.000.000-00"
                        hint="Usado apenas para identificar seus pagamentos. Fica armazenado de forma criptografada."
                        required />

                    <x-ui.input
                        name="phone"
                        label="Telefone / WhatsApp"
                        type="tel"
                        :value="$phone"
                        data-mask="phone"
                        inputmode="numeric"
                        autocomplete="tel-national"
                        placeholder="(66) 99999-9999"
                        hint="Usado apenas para suporte da sua compra."
                        required />
                @endunless

                @error('payment')
                    <x-ui.alert tone="danger">{{ $message }}</x-ui.alert>
                @enderror

                @if ($needsTerms)
                    <div>
                        <label class="flex cursor-pointer items-start gap-3 text-sm text-ink">
                            <input type="checkbox" name="terms" value="1" @checked(old('terms')) required class="mt-0.5 size-5 shrink-0 rounded accent-primary">
                            <span>
                                Li e aceito os
                                <a href="{{ route('legal.terms') }}" target="_blank" rel="noopener" class="font-medium text-primary underline">Termos de Uso</a>,
                                a
                                <a href="{{ route('legal.license') }}" target="_blank" rel="noopener" class="font-medium text-primary underline">Licença de Uso</a>
                                e a
                                <a href="{{ route('legal.privacy') }}" target="_blank" rel="noopener" class="font-medium text-primary underline">Política de Privacidade</a>.
                            </span>
                        </label>

                        @error('terms')
                            <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                @else
                    <p class="text-xs text-subtle">
                        Ao continuar você concorda com a
                        <a href="{{ route('legal.license') }}" target="_blank" rel="noopener" class="font-medium underline">Licença de Uso</a>:
                        material de uso pessoal, sem revenda ou compartilhamento.
                    </p>
                @endif

                <x-ui.button size="lg" class="w-full">{{ $isFree ? 'Adicionar à minha biblioteca' : 'Ir para o pagamento' }}</x-ui.button>
            </form>
        </div>
    </div>
</x-layouts.site>
