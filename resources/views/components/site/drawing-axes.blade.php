@props([
    // Column axes: label => horizontal position (Tailwind left-* class, may add visibility).
    'columns' => ['A' => 'left-[8%]', 'B' => 'left-[54%] hidden lg:flex', 'C' => 'left-[92%]'],
    // Row axes: label => vertical position (Tailwind top-* class).
    'rows' => ['1' => 'top-[18%]', '2' => 'top-[82%]'],
])

{{-- Column and row axes of a structural plan (dash-dot center lines with
     circled labels). Purely decorative: place inside a relative container. --}}
<div {{ $attributes->merge(['class' => 'pointer-events-none absolute inset-0']) }} aria-hidden="true">
    @foreach ($columns as $position)
        <span class="axis-line-v {{ str_replace('flex', 'block', $position) }}"></span>
    @endforeach
    @foreach ($rows as $position)
        <span class="axis-line-h {{ $position }}"></span>
    @endforeach

    @foreach ($columns as $label => $position)
        <span class="absolute top-4 flex size-8 -translate-x-1/2 items-center justify-center rounded-full border border-primary/30 bg-white/80 font-display text-sm font-semibold text-primary/70 {{ $position }}">{{ $label }}</span>
    @endforeach
    @foreach ($rows as $label => $position)
        <span class="absolute left-3 hidden size-8 -translate-y-1/2 items-center justify-center rounded-full border border-primary/30 bg-white/80 font-display text-sm font-semibold text-primary/70 sm:flex {{ $position }}">{{ $label }}</span>
    @endforeach
</div>
