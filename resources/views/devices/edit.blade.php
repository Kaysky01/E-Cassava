@extends('layouts.app')
@section('title', __('devices.edit_title'))

@section('content')
<div class="mx-auto max-w-xl space-y-6">
    <div class="card p-6 sm:p-7">
        <p class="eyebrow text-cassava-600">{{ $device->device_id }}</p>
        <h2 class="mt-1 font-display text-2xl font-extrabold">{{ __('devices.edit_title') }}</h2>

        <form method="POST" action="{{ route('devices.update', $device) }}" class="mt-6 space-y-4">
            @csrf
            @method('PATCH')
            <div>
                <label for="name" class="label-shell">{{ __('devices.name') }}</label>
                <input id="name" name="name" required maxlength="100" class="input-shell" value="{{ old('name', $device->name) }}" />
            </div>
            <div>
                <label for="location" class="label-shell">{{ __('devices.location') }}</label>
                <input id="location" name="location" maxlength="150" class="input-shell" value="{{ old('location', $device->location) }}" placeholder="{{ __('devices.location_ph') }}" />
            </div>
            <label class="flex items-center gap-3 rounded-2xl bg-gray-50 p-4 ring-1 ring-gray-100">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $device->is_active)) class="h-6 w-6 rounded text-cassava-600 focus:ring-cassava-500" />
                <span class="text-sm font-semibold">{{ $device->is_active ? __('devices.status_active') : __('devices.status_inactive') }}</span>
            </label>
            <div class="flex gap-2">
                <button type="submit" class="btn-primary flex-1">{{ __('devices.update') }}</button>
                <a href="{{ route('devices.index') }}" class="btn-ghost">{{ __('app.back') }}</a>
            </div>
        </form>
    </div>

    <div class="card border-red-200 p-6">
        <h3 class="font-bold text-red-600">{{ __('app.delete') }}</h3>
        <form method="POST" action="{{ route('devices.destroy', $device) }}" onsubmit="return confirm(@js(__('devices.delete_confirm')))" class="mt-3">
            @csrf
            @method('DELETE')
            <button class="inline-flex items-center gap-2 rounded-2xl bg-red-500 px-5 py-3 text-base font-bold text-white transition hover:bg-red-600">{{ __('app.delete') }} {{ $device->device_id }}</button>
        </form>
    </div>
</div>
@endsection
