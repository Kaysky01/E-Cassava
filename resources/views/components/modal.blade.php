@props(['name' => 'modal', 'show' => false, 'maxWidth' => 'max-w-lg'])

<div x-data="{ open: @js($show) }" x-show="open" x-cloak
    @open-modal-{{ $name }}.window="open = true" @close-modal-{{ $name }}.window="open = false"
    class="fixed inset-0 z-[80] flex items-end justify-center p-4 sm:items-center" role="dialog" aria-modal="true">
    <div x-show="open" x-transition.opacity class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="open = false"></div>
    <div x-show="open" x-transition.scale.95
        {{ $attributes->merge(['class' => "relative w-full {$maxWidth} card p-6 sm:p-7"]) }}>
        {{ $slot }}
    </div>
</div>
