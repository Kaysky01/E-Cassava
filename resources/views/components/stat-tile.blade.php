@props(['label' => '', 'value' => '—', 'sub' => null, 'icon' => null])

<div {{ $attributes->merge(['class' => 'card card-hover min-w-0 p-4 sm:p-5']) }}>
    <div class="flex items-start justify-between gap-2 sm:gap-3">
        <div class="min-w-0">
            <p class="eyebrow break-words text-gray-400">{{ $label }}</p>
            <p class="mt-2 break-words font-display text-2xl font-extrabold tracking-tight tabular-nums sm:text-3xl" x-data="{ shown: false }" x-init="setTimeout(() => shown = true, 50)">
                {{ $value }}
            </p>
            @if ($sub)
                <p class="mt-1 text-xs text-gray-500">{{ $sub }}</p>
            @endif
        </div>
        @if ($icon)
            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-cassava-100 text-cassava-700">
                {!! $icon !!}
            </div>
        @endif
    </div>
</div>
