@extends('layouts.guest')

@section('title', __('auth.login_title'))

@section('content')
<h1 class="font-display text-2xl font-extrabold tracking-tight">{{ __('auth.login_title') }}</h1>
<p class="mt-1 text-sm text-gray-500">{{ __('auth.login_subtitle') }}</p>

<form method="POST" action="{{ route('login') }}" class="mt-6 space-y-4">
    @csrf

    <div>
        <label for="email" class="label-shell">{{ __('auth.email') }}</label>
        <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username"
            class="input-shell" placeholder="petani@example.com" />
    </div>

    <div>
        <label for="password" class="label-shell">{{ __('auth.password') }}</label>
        <input id="password" type="password" name="password" required autocomplete="current-password"
            class="input-shell" placeholder="••••••••" />
    </div>

    <div class="flex items-center justify-between text-sm">
        <label class="flex items-center gap-2 font-medium text-gray-600">
            <input type="checkbox" name="remember" class="h-5 w-5 rounded border-gray-300 text-cassava-600 focus:ring-cassava-500" />
            {{ __('auth.remember') }}
        </label>
        <a href="{{ route('password.request') }}" class="font-semibold text-cassava-700 hover:underline">{{ __('auth.forgot') }}</a>
    </div>

    <button type="submit" class="btn-primary w-full !py-3.5 !text-lg">{{ __('auth.login_button') }}</button>

    <p class="text-center text-sm text-gray-500">
        {{ __('auth.no_account') }}
        <a href="{{ route('register') }}" class="font-bold text-cassava-700 hover:underline">{{ __('auth.register_button') }}</a>
    </p>
</form>
@endsection
