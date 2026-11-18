@props([
    // Tailwind size of the badge; the logo fills about 70% of it.
    'size' => 'size-11',
    'logo' => 'size-8',
])

{{-- The logo is a white mark: it always sits on the brand gradient. --}}
<span {{ $attributes->merge(['class' => "{$size} flex shrink-0 items-center justify-center rounded-xl bg-brand-gradient shadow-[0_6px_18px_-6px_rgb(91_92_240/0.55)]"]) }}>
    <x-site.logo :size="$logo" />
</span>
