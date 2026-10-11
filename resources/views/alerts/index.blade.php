@extends('layouts.app')
@section('title', __('alerts.title'))

@section('content')
<div class="space-y-6">
    <div class="card card-hover p-6 sm:p-7">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="eyebrow text-cassava-600">{{ __('alerts.unread', ['n' => $unreadCount]) }}</p>
                <h2 class="mt-1 font-display text-xl font-extrabold sm:text-2xl">{{ __('alerts.title') }}</h2>
                <p class="mt-1 text-[13px] text-gray-500">{{ __('alerts.subtitle') }}</p>
            </div>
            @if ($unreadCount > 0)
                <form method="POST" action="{{ route('alerts.read-all') }}">
                    @csrf
                    <button class="btn-ghost">{{ __('alerts.mark_all') }}</button>
                </form>
            @endif
        </div>
    </div>

    @if ($alerts->isEmpty())
        <x-empty-state :title="__('alerts.empty_title')" :desc="__('alerts.empty_desc')" />
    @else
        <div class="space-y-3">
            @foreach ($alerts as $alert)
                @php
                    $style = match ($alert->type) {
                        'rotten' => ['bg-red-500', 'border-red-200 bg-red-50/60'],
                        'stale' => ['bg-amber-400', 'border-amber-200 bg-amber-50/60'],
                        'offline' => ['bg-gray-400', 'border-gray-200 bg-gray-50/60'],
                        default => ['bg-cassava-500', 'border-gray-200 bg-white'],
                    };
                @endphp
                <div class="card flex flex-col gap-3 border p-5 sm:flex-row sm:items-center {{ $style[1] }} {{ $alert->isRead() ? 'opacity-70' : '' }}">
                    <span class="mt-1.5 h-3 w-3 shrink-0 rounded-full {{ $style[0] }}" aria-hidden="true"></span>
                    <div class="flex-grow">
                        <p class="font-bold">{{ $alert->title() }}
                            @unless ($alert->isRead())
                                <span class="ml-1 rounded-full bg-red-500 px-2 py-0.5 text-[10px] font-bold uppercase text-white">new</span>
                            @endunless
                        </p>
                        <p class="mt-0.5 text-sm text-gray-600">{{ $alert->body() }}</p>
                        <p class="mt-1 font-mono text-xs text-gray-400">{{ $alert->device?->device_id }} • {{ $alert->created_at->translatedFormat('d M Y H:i') }}</p>
                    </div>
                    @unless ($alert->isRead())
                        <form method="POST" action="{{ route('alerts.read', $alert) }}" class="shrink-0">
                            @csrf
                            @method('PATCH')
                            <button class="btn-ghost !px-4 !py-2.5 !text-sm">{{ __('alerts.mark_read') }}</button>
                        </form>
                    @endunless
                </div>
            @endforeach
        </div>
        @if ($alerts->hasPages())
            <div class="card px-5 py-4">{{ $alerts->links() }}</div>
        @endif
    @endif
</div>
@endsection
