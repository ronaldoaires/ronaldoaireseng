@props([
    // Tailwind size of the badge; the logo fills about 70% of it.
    'size' => 'size-11',
    'logo' => 'size-8',
])

{{-- The logo is a white mark on the brand color. --}}
<span {{ $attributes->merge(['class' => "{$size} flex shrink-0 items-center justify-center rounded-xl bg-brand-gradient"]) }}>
    <x-site.logo :size="$logo" />
</span>
