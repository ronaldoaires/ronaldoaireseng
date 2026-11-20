@guest
<div class="flex items-center gap-2">
    <span class="hidden sm:inline-flex"><x-ui.button :href="route('login')" variant="outline" class="rounded-full!">Entrar</x-ui.button></span>
    <x-ui.button :href="route('register')" variant="gradient">Criar conta</x-ui.button>
</div>
@endguest

@auth
{{-- Native popover: opens without JavaScript and closes on Esc or an outside click. --}}
<button
    type="button"
    popovertarget="user-menu"
    aria-controls="user-menu"
    aria-label="Menu da conta de {{ \Illuminate\Support\Str::before(auth()->user()->name, ' ') }}"
    class="menu-trigger-user flex min-h-12 items-center gap-2 rounded-control px-3 text-sm font-medium hover:bg-primary-soft hover:text-primary">
    <x-ui.avatar :user="auth()->user()" class="size-8 text-xs" aria-hidden="true" />
    <span class="hidden sm:inline">{{ \Illuminate\Support\Str::before(auth()->user()->name, ' ') }}</span>
</button>

<div id="user-menu" popover="auto" class="menu-popover menu-popover--end w-64 rounded-card bg-surface py-2 text-ink shadow-card ring-1 ring-line">
    <a href="{{ route('account.library') }}" class="flex min-h-12 items-center gap-3 px-4 text-sm hover:bg-canvas">
        @svg('heroicon-o-book-open', 'size-5 text-primary', ['aria-hidden' => 'true'])
        Minha biblioteca
    </a>
    <a href="{{ route('account.index') }}" class="flex min-h-12 items-center gap-3 px-4 text-sm hover:bg-canvas">
        @svg('heroicon-o-user', 'size-5 text-primary', ['aria-hidden' => 'true'])
        Minha conta
    </a>
    @if (auth()->user()->isAdmin())
    <a href="{{ url('/admin') }}" class="flex min-h-12 items-center gap-3 px-4 text-sm hover:bg-canvas">
        @svg('heroicon-o-cog-6-tooth', 'size-5 text-primary', ['aria-hidden' => 'true'])
        Painel administrativo
    </a>
    @endif

    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="flex min-h-12 w-full items-center gap-3 px-4 text-left text-sm hover:bg-canvas">
            @svg('heroicon-o-arrow-right-on-rectangle', 'size-5 text-primary', ['aria-hidden' => 'true'])
            Sair
        </button>
    </form>
</div>
@endauth
