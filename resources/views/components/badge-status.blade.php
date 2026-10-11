@props(['label' => 'fresh', 'size' => 'md'])

@php
    $map = [
        'fresh' => ['bg-emerald-100 text-emerald-800 ring-emerald-200', __('app.fresh')],
        'stale' => ['bg-amber-100 text-amber-800 ring-amber-200', __('app.stale')],
        'rotten' => ['bg-red-100 text-red-800 ring-red-200', __('app.rotten')],
        'online' => ['bg-emerald-100 text-emerald-800 ring-emerald-200', __('app.online')],
        'offline' => ['bg-red-100 text-red-800 ring-red-200', __('app.offline')],
    ];
    $key = array_key_exists($label, $map) ? $label : 'fresh';
    [$cls, $text] = $map[$key];
    $pad = $size === 'lg' ? 'px-5 py-2.5 text-base' : 'px-3 py-1.5 text-xs';
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center rounded-full font-bold ring-1 ring-inset {$cls} {$pad}"]) }}>{{ $text }}</span>
