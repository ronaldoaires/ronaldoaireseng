@props([
    // Tailwind size classes for the mark (the logo image or the fallback monogram).
    'size' => 'size-10',
])

@php
// The logo is a white mark, so it must always sit on a dark (header) background.
// Drop the file in public/images/ as logo.svg or logo.png; the monogram is only a fallback.
$logo = collect(['images/logo.svg', 'images/logo.png', 'images/logo.webp'])
    ->first(fn ($path) => file_exists(public_path($path)));
@endphp

@if ($logo)
    <img src="{{ asset($logo) }}" alt="" {{ $attributes->merge(['class' => "{$size} shrink-0 object-contain"]) }}>
@else
    <span {{ $attributes->merge(['class' => "{$size} flex shrink-0 items-center justify-center rounded-control bg-on-header/15 text-sm font-extrabold text-on-header"]) }} aria-hidden="true">RA</span>
@endif
