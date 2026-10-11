@props(['title' => '', 'desc' => null, 'action' => null])

<div {{ $attributes->merge(['class' => 'card p-10 text-center']) }}>
    <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-3xl bg-cassava-100 text-cassava-600">
        <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M11 20A7 7 0 0 1 4 13c0-4 3-8 8-10 5-2 8-1 8-1s-1 5-3 9c-2 4-4 7-6 9Z"/><path d="M4 21c4-6 8-9 12-11"/></svg>
    </div>
    <h3 class="text-lg font-bold">{{ $title }}</h3>
    @if ($desc)
        <p class="mx-auto mt-2 max-w-md text-sm text-gray-500">{{ $desc }}</p>
    @endif
    @if ($action)
        <div class="mt-5">{{ $action }}</div>
    @endif
    {{ $slot }}
</div>
