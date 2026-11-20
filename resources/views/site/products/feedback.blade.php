<x-layouts.site :title="($isReview ? 'Avaliar ' : 'Sugerir melhorias para ') . $product->title" :noindex="true">
    <div class="mx-auto max-w-3xl px-4 py-10 sm:py-14">
        <a href="{{ $product->url }}#avaliacoes" class="inline-flex min-h-11 items-center gap-2 text-sm font-semibold text-primary hover:underline">
            @svg('heroicon-o-arrow-left', 'size-4')
            Voltar ao produto
        </a>

        <div class="mt-5 rounded-card bg-surface p-6 shadow-card sm:p-9">
            <p class="text-sm font-semibold text-primary">{{ $product->title }}</p>
            <h1 class="mt-2 font-display text-3xl font-bold tracking-tight text-ink sm:text-4xl">
                {{ $isReview ? 'Conte como foi sua experiência' : 'Ajude a melhorar este material' }}
            </h1>
            <p class="mt-3 leading-relaxed text-muted">
                {{ $isReview
                    ? 'Sua avaliação ajuda outras pessoas a decidir. Ela será publicada nesta página depois da análise.'
                    : 'Sentiu falta de um assunto ou tem uma ideia para a próxima atualização? Envie sua sugestão diretamente para mim. Ela não será publicada.' }}
            </p>

            @if ($isReview && ! $canReview)
                <x-ui.alert tone="info" icon="information-circle" class="mt-7">
                    As avaliações são feitas por quem já adquiriu o material. Depois da compra, este formulário ficará disponível para você.
                </x-ui.alert>
                <x-ui.button :href="route('checkout.show', ['product' => $product->slug])" class="mt-6">Ver como adquirir</x-ui.button>
            @else
                @if ($isReview && $feedback)
                    <x-ui.alert tone="info" icon="information-circle" class="mt-7">
                        Você já enviou uma avaliação. Pode atualizá-la abaixo; uma nova análise será necessária antes da publicação.
                    </x-ui.alert>
                @endif

                <form method="POST" action="{{ $isReview
                    ? route('products.feedback.review.store', ['type' => $product->type->segment(), 'product' => $product->slug])
                    : route('products.feedback.suggestion.store', ['type' => $product->type->segment(), 'product' => $product->slug]) }}" class="mt-7 space-y-6">
                    @csrf

                    <x-ui.input
                        name="display_name"
                        label="Nome"
                        :value="$feedback?->display_name ?? auth()->user()->name"
                        maxlength="100"
                        autocomplete="name"
                        :hint="$isReview ? 'Este nome aparecerá junto da avaliação depois da aprovação.' : 'Usado apenas para identificar sua sugestão no painel.'"
                        required />

                    @if ($isReview)
                        <fieldset>
                            <legend class="mb-2 text-sm font-medium text-ink">Sua nota</legend>
                            <div class="grid grid-cols-5 gap-2">
                                @for ($rating = 1; $rating <= 5; $rating++)
                                    <label class="cursor-pointer">
                                        <input type="radio" name="rating" value="{{ $rating }}" @checked((int) old('rating', $feedback?->rating) === $rating) required class="peer sr-only">
                                        <span class="flex min-h-12 items-center justify-center gap-1 rounded-control border border-line text-sm font-semibold text-ink transition hover:border-amber-400 peer-checked:border-amber-500 peer-checked:bg-amber-50 peer-focus-visible:outline-2 peer-focus-visible:outline-offset-2 peer-focus-visible:outline-primary">
                                            <span aria-hidden="true" class="text-lg text-amber-500">★</span>{{ $rating }}
                                        </span>
                                        <span class="sr-only">{{ $rating }} {{ $rating === 1 ? 'estrela' : 'estrelas' }}</span>
                                    </label>
                                @endfor
                            </div>
                            @error('rating') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                        </fieldset>
                    @else
                        <x-ui.input name="subject" label="Assunto da sugestão" maxlength="120" placeholder="Ex.: mais exemplos de detalhamento de vigas" required />
                    @endif

                    <div>
                        <label for="content" class="mb-1.5 block text-sm font-medium text-ink">{{ $isReview ? 'Sua avaliação' : 'Descreva sua ideia' }}</label>
                        <textarea id="content" name="content" rows="6" minlength="20" maxlength="{{ $isReview ? 1500 : 2000 }}" required class="block w-full rounded-control border border-line bg-surface px-3.5 py-3 text-base text-ink shadow-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20">{{ old('content', $feedback?->content) }}</textarea>
                        <p class="mt-1.5 text-xs text-subtle">Escreva pelo menos 20 caracteres.</p>
                        @error('content') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <x-ui.button size="lg" class="w-full sm:w-auto">{{ $isReview ? 'Enviar avaliação' : 'Enviar sugestão' }}</x-ui.button>
                </form>
            @endif
        </div>
    </div>
</x-layouts.site>
