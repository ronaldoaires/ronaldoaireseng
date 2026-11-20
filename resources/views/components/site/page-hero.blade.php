{{-- Light page header panel shared by inner pages: same drawing-sheet look as
     the home hero. Content goes in the slot with its own max-width wrapper. --}}
<section {{ $attributes->merge(['class' => 'px-3 pt-3 sm:px-4 sm:pt-4']) }}>
    <div class="relative mx-auto max-w-7xl overflow-hidden rounded-2xl border border-line bg-white text-ink lg:rounded-3xl">
        <x-site.drawing-axes class="opacity-65" />
        <div class="relative px-2 pt-6 sm:px-6">
            {{ $slot }}
        </div>
    </div>
</section>
