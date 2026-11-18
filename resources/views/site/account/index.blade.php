<x-layouts.account title="Minha conta">
    <x-account.page-header
        :title="'Olá, ' . \Illuminate\Support\Str::before($user->name, ' ')"
        description="Seus ebooks, planilhas e dados da conta." />

    <div class="space-y-6">

        {{-- Alerts: only what needs the person's attention --}}
        @if ($deletionDate)
            <x-ui.alert>
                Sua conta será encerrada em <strong>{{ $deletionDate->format('d/m/Y') }}</strong>.
                <a href="{{ route('account.danger') }}" class="underline">Cancelar pedido</a>
            </x-ui.alert>
        @endif

        @if ($user->pending_email)
            <x-ui.alert>
                Troca de e-mail aguardando confirmação em <strong>{{ $user->pending_email }}</strong>.
                <a href="{{ route('account.email') }}" class="underline">Ver pedido</a>
            </x-ui.alert>
        @endif

        @if ($pendingCount > 0)
            <x-ui.alert tone="info" icon="clock">
                {{ $pendingCount === 1 ? 'Um pagamento está' : $pendingCount . ' pagamentos estão' }} aguardando confirmação.
                <a href="{{ route('account.purchases') }}" class="underline">Ver compras</a>
            </x-ui.alert>
        @endif

        @unless ($hasPassword)
            <div class="rounded-card border border-line bg-surface p-4 text-sm text-muted shadow-card">
                Sua conta entra pelo Google. Se quiser também entrar com e-mail e senha,
                <a href="{{ route('account.password') }}" class="font-medium text-primary underline">crie uma senha</a>.
            </div>
        @endunless

        {{-- Continue reading --}}
        @if ($reading)
            <a href="{{ route('account.reader', ['product' => $reading->product->slug, 'chapter' => $reading->lastChapter->slug]) }}"
               class="flex items-center gap-4 rounded-card bg-header p-5 text-on-header shadow-card transition hover:-translate-y-0.5">
                <span class="flex size-12 shrink-0 items-center justify-center rounded-full bg-on-header/15">
                    @svg('heroicon-o-book-open', 'size-6')
                </span>
                <span class="min-w-0 flex-1">
                    <span class="block text-sm text-on-header/75">Continuar lendo</span>
                    <span class="block truncate font-semibold">{{ $reading->product->title }}</span>
                    <span class="block truncate text-sm text-on-header/85">{{ $reading->lastChapter->title }}</span>
                </span>
                @svg('heroicon-o-arrow-right', 'size-5 shrink-0')
            </a>
        @endif

        {{-- Library --}}
        <section class="rounded-card bg-surface p-6 shadow-card">
            <div class="flex items-center justify-between gap-3">
                <h2 class="text-lg font-semibold text-ink">Minha biblioteca</h2>

                @if ($libraryCount > 0)
                    <a href="{{ route('account.library') }}" class="text-sm font-medium text-primary underline">Ver tudo ({{ $libraryCount }})</a>
                @endif
            </div>

            @if ($purchases->isEmpty())
                <p class="mt-3 text-sm text-muted">Você ainda não tem ebooks ou planilhas.</p>

                @if ($suggestions->isNotEmpty())
                    <div class="mt-4 grid grid-cols-2 gap-4 sm:grid-cols-3">
                        @foreach ($suggestions as $product)
                            <x-site.product-card :product="$product" />
                        @endforeach
                    </div>
                @else
                    <x-ui.button :href="route('home')" variant="tonal" class="mt-4">Conhecer os materiais</x-ui.button>
                @endif
            @else
                <ul class="mt-3 divide-y divide-line">
                    @foreach ($purchases as $purchase)
                        <li>
                            <a href="{{ route('account.library.show', ['product' => $purchase->product->slug]) }}" class="flex items-center gap-4 py-3 hover:bg-canvas">
                                <span class="relative aspect-[3/4] w-12 shrink-0 overflow-hidden rounded-control bg-canvas">
                                    @if ($purchase->product->cover_thumb_url)
                                        <img src="{{ $purchase->product->cover_thumb_url }}" alt="" width="48" height="64" loading="lazy" class="absolute inset-0 size-full object-cover">
                                    @else
                                        <span class="absolute inset-0 flex items-center justify-center text-subtle">@svg('heroicon-o-' . $purchase->product->type->iconName(), 'size-5')</span>
                                    @endif
                                </span>
                                <span class="min-w-0 flex-1">
                                    <span class="block truncate text-sm font-medium text-ink">{{ $purchase->product->title }}</span>
                                    <span class="block text-xs text-subtle">{{ $purchase->product->type->getLabel() }}</span>
                                </span>
                                @svg('heroicon-o-chevron-right', 'size-5 shrink-0 text-subtle')
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>

        {{-- Account data --}}
        <section class="rounded-card bg-surface p-6 shadow-card">
            <div class="flex items-center gap-4">
                <x-ui.avatar :user="$user" class="size-14 text-lg" />

                <div class="min-w-0">
                    <p class="truncate font-semibold text-ink">{{ $user->name }}</p>
                    <p class="truncate text-sm text-muted">{{ $user->email }}</p>
                </div>

                <a href="{{ route('account.profile') }}" class="ml-auto shrink-0 text-sm font-medium text-primary underline">Editar perfil</a>
            </div>
        </section>
    </div>
</x-layouts.account>
