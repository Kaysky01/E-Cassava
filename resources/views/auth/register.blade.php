@extends('layouts.guest')

@section('title', __('auth.register_title'))

@section('content')
<h1 class="font-display text-2xl font-extrabold tracking-tight">{{ __('auth.register_title') }}</h1>
<p class="mt-1 text-sm text-gray-500">{{ __('auth.register_subtitle') }}</p>

<form method="POST" action="{{ route('register') }}" class="mt-6 space-y-4">
    @csrf

    <div>
        <label for="name" class="label-shell">{{ __('auth.name') }}</label>
        <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name"
            class="input-shell" placeholder="Pak Tani" />
    </div>

    <div>
        <label for="email" class="label-shell">{{ __('auth.email') }}</label>
        <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="username"
            class="input-shell" placeholder="petani@example.com" />
    </div>

    <div>
        <label for="password" class="label-shell">{{ __('auth.password') }}</label>
        <input id="password" type="password" name="password" required autocomplete="new-password"
            class="input-shell" placeholder="••••••••" />
    </div>

    <div>
        <label for="password_confirmation" class="label-shell">{{ __('auth.password_confirm') }}</label>
        <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password"
            class="input-shell" placeholder="••••••••" />
    </div>

    <button type="submit" class="btn-primary w-full !py-3.5 !text-lg">{{ __('auth.register_button') }}</button>

    <p class="text-center text-sm text-gray-500">
        {{ __('auth.have_account') }}
        <a href="{{ route('login') }}" class="font-bold text-cassava-700 hover:underline">{{ __('auth.login_button') }}</a>
    </p>
</form>
@endsection
