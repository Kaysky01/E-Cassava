@extends('layouts.guest')

@section('title', __('auth.reset_title'))

@section('content')
<h1 class="font-display text-2xl font-extrabold tracking-tight">{{ __('auth.reset_title') }}</h1>

<form method="POST" action="{{ route('password.store') }}" class="mt-6 space-y-4">
    @csrf

    <input type="hidden" name="token" value="{{ $request->route('token') }}" />

    <div>
        <label for="email" class="label-shell">{{ __('auth.email') }}</label>
        <input id="email" type="email" name="email" value="{{ old('email', $request->email) }}" required autofocus
            class="input-shell" />
    </div>

    <div>
        <label for="password" class="label-shell">{{ __('auth.password') }}</label>
        <input id="password" type="password" name="password" required autocomplete="new-password" class="input-shell" />
    </div>

    <div>
        <label for="password_confirmation" class="label-shell">{{ __('auth.password_confirm') }}</label>
        <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" class="input-shell" />
    </div>

    <button type="submit" class="btn-primary w-full !py-3.5 !text-lg">{{ __('auth.reset_button') }}</button>
</form>
@endsection
