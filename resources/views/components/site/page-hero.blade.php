{{-- Light page header panel shared by inner pages: same drawing-sheet look as
     the home hero. Content goes in the slot with its own max-width wrapper. --}}
<section {{ $attributes->merge(['class' => 'px-3 pt-3 sm:px-4 sm:pt-4']) }}>
    <div class="relative mx-auto max-w-7xl overflow-hidden rounded-[2rem] bg-gradient-to-br from-white via-[#eef1ff] to-[#e9e4ff] text-ink ring-1 ring-line">
        <x-site.drawing-axes />
        <div class="relative px-2 pt-6 sm:px-6">
            {{ $slot }}
        </div>
    </div>
</section>
