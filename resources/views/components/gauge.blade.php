@props(['value' => 0, 'label' => null])

@php
    $pct = max(0, min(100, (float) $value));
    $circ = 2 * pi() * 54;
    $offset = $circ * (1 - $pct / 100);
    $color = $pct >= 70 ? '#16a34a' : ($pct >= 40 ? '#f59e0b' : '#ef4444');
@endphp

<div {{ $attributes->merge(['class' => 'flex items-center gap-4']) }} role="img" aria-label="{{ $label ?? __('dashboard.confidence') }}: {{ number_format($pct, 1) }}%">
    <div class="relative h-28 w-28 shrink-0">
        <svg viewBox="0 0 120 120" class="h-28 w-28 -rotate-90">
            <circle cx="60" cy="60" r="54" fill="none" stroke-width="12" class="stroke-gray-200" />
            <circle cx="60" cy="60" r="54" fill="none" stroke="{{ $color }}" stroke-width="12" stroke-linecap="round"
                stroke-dasharray="{{ $circ }}" stroke-dashoffset="{{ $offset }}"
                style="transition: stroke-dashoffset 0.8s ease;" />
        </svg>
        <div class="absolute inset-0 flex flex-col items-center justify-center">
            <span class="font-display text-xl font-extrabold tabular-nums">{{ number_format($pct, 1) }}%</span>
        </div>
    </div>
    @if ($label)
        <p class="text-sm font-semibold text-gray-600">{{ $label }}</p>
    @endif
</div>
