@props([
'variant' => 'primary', // primary | gradient | light | tonal | outline | ghost | inverse | danger
'size' => 'md', // md | lg
])

@php
$base = 'inline-flex items-center justify-center gap-2 font-semibold transition focus:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2 disabled:opacity-60';

$variants = [
'primary' => 'rounded-control bg-primary text-on-primary hover:bg-primary-hover',
// Main call to action: pill with the blue-to-violet brand gradient.
'gradient' => 'rounded-full bg-brand-gradient text-white shadow-[0_8px_24px_-8px_rgb(91_92_240/0.6)] hover:brightness-110',
// White pill for use on top of the brand gradient.
'light' => 'rounded-full bg-white text-ink hover:bg-white/90',
'tonal' => 'rounded-control bg-primary-soft text-on-primary-soft hover:bg-primary-soft-hover',
'outline' => 'rounded-control border border-line bg-surface text-ink hover:bg-canvas',
'ghost' => 'rounded-control text-primary hover:bg-primary-soft',
// For use on top of the header color.
'inverse' => 'rounded-control bg-on-header text-header hover:bg-on-header/90',
'danger' => 'rounded-control bg-red-600 text-white hover:bg-red-700',
];

// 48px minimum tap target on touch screens.
$sizes = [
'md' => 'min-h-12 px-5 text-sm',
'lg' => 'min-h-14 px-6 text-base',
];

$classes = "{$base} {$variants[$variant]} {$sizes[$size]}";
@endphp

@if ($attributes->has('href'))
<a {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</a>
@else
<button {{ $attributes->merge(['type' => 'submit', 'class' => $classes]) }}>{{ $slot }}</button>
@endif