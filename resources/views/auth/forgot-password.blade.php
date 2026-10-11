@extends('layouts.guest')

@section('title', __('auth.forgot_title'))

@section('content')
<h1 class="font-display text-2xl font-extrabold tracking-tight">{{ __('auth.forgot_title') }}</h1>
<p class="mt-1 text-sm text-gray-500">{{ __('auth.forgot_desc') }}</p>

<form method="POST" action="{{ route('password.email') }}" class="mt-6 space-y-4">
    @csrf

    <div>
        <label for="email" class="label-shell">{{ __('auth.email') }}</label>
        <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
            class="input-shell" placeholder="petani@example.com" />
    </div>

    <button type="submit" class="btn-primary w-full !py-3.5 !text-lg">{{ __('auth.send_link') }}</button>

    <p class="text-center text-sm">
        <a href="{{ route('login') }}" class="font-bold text-cassava-700 hover:underline">← {{ __('auth.login_button') }}</a>
    </p>
</form>
@endsection
