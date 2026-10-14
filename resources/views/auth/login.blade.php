<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>{{ __('auth.login_title') }} – E-CASSAVA</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <meta name="csrf-token" content="{{ csrf_token() }}" />
</head>
<body class="min-h-screen font-sans antialiased">
    {{-- Background kebun singkong + overlay hijau gelap --}}
    <div class="fixed inset-0 -z-10 overflow-hidden bg-[#223a1c]">
        <img src="{{ asset('images/login-bg.jpg') }}"
            onerror="this.onerror=null;this.src='https://images.unsplash.com/photo-1500382017468-9049fed747ef?q=80&w=2070&auto=format&fit=crop';"
            alt="" aria-hidden="true" class="h-full w-full object-cover object-[42%_42%]" />
        <div class="absolute inset-0 bg-gradient-to-b from-[#2a4423]/70 via-[#1c3018]/75 to-[#101f0e]/85"></div>
        <div class="absolute inset-0 bg-[#1a2f16]/30"></div>
    </div>

    <main class="flex min-h-screen items-center justify-center p-4 sm:p-6">
        <div class="relative w-full max-w-[520px] overflow-hidden rounded-[26px] bg-white px-7 py-8 shadow-[0_24px_80px_-12px_rgba(0,0,0,0.55)] sm:px-9">
            {{-- Garis aksen atas --}}
            <div class="absolute inset-x-8 top-0 h-[3px] rounded-full bg-gradient-to-r from-[#1e4d1f] via-[#4a7c2e] to-[#7a3a1e]" aria-hidden="true"></div>

            {{-- Brand --}}
            <div class="flex items-center gap-3">
                <div class="h-14 w-14 shrink-0 overflow-hidden rounded-full bg-[#f2f6ea] shadow-md ring-1 ring-black/5">
                    <img src="{{ asset('images/logo.png') }}?v=2" alt="E-CASSAVA"
                        class="h-full w-full scale-[1.45] object-cover object-[50%_35%]"
                        onerror="this.outerHTML='<div class=&quot;flex h-full w-full items-center justify-center text-[30px]&quot;>🧑‍🌾</div>'" />
                </div>
                <div>
                    <p class="font-display text-[22px] font-extrabold leading-none tracking-tight text-[#2c4f22]">E-CASSAVA</p>
                    <p class="mt-1.5 text-[10.5px] font-semibold uppercase tracking-[0.22em] text-[#9db18d]">Electronic Nose System</p>
                </div>
            </div>

            {{-- Judul + toggle bahasa --}}
            <div class="mt-6 flex items-start justify-between gap-4">
                <div>
                    <h1 class="font-display text-[30px] font-extrabold leading-tight tracking-tight text-[#1e3a1a]">{{ __('auth.login_title') }}</h1>
                    <p class="mt-1 text-[14px] text-[#7a7a7a]">{{ __('auth.login_subtitle') }}</p>
                </div>
                <div class="flex shrink-0 items-center gap-0.5 rounded-full border border-[#e6e8d8] bg-[#fafaf4] p-1 text-[12px] font-bold" role="group" aria-label="{{ __('app.toggle_lang') }}">
                    @foreach (['id' => 'ID', 'en' => 'EN'] as $code => $short)
                        <a href="{{ route('locale.switch', $code) }}"
                            class="{{ app()->getLocale() === $code ? 'rounded-full bg-[#2f5d2a] px-3 py-1 text-white shadow-sm' : 'rounded-full px-3 py-1 text-[#8a8a8a] hover:text-[#2f5d2a]' }}"
                            aria-pressed="{{ app()->getLocale() === $code ? 'true' : 'false' }}">{{ $short }}</a>
                    @endforeach
                </div>
            </div>

            @if (session('status'))
                <div class="mt-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900" role="alert">{{ session('status') }}</div>
            @endif

            @if ($errors->any())
                <div class="mt-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800" role="alert">
                    <ul class="list-disc space-y-0.5 pl-5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="mt-6">
                @csrf

                <div>
                    <label for="email" class="mb-1.5 block text-[13px] font-bold text-gray-900">{{ __('auth.email') }}</label>
                    <div class="relative">
                        <span class="pointer-events-none absolute inset-y-0 left-4 flex items-center text-[#9a9a9a]" aria-hidden="true">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-[18px] w-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><rect x="3" y="5" width="18" height="14" rx="2" /><path d="m3 7 9 6 9-6" /></svg>
                        </span>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                            placeholder="petani@example.com"
                            class="h-[48px] w-full rounded-[12px] border border-[#dfe6d0] bg-white pl-11 pr-4 text-[14px] text-gray-900 placeholder-[#b5b5b5] outline-none transition focus:border-[#4a7c2e] focus:ring-2 focus:ring-[#4a7c2e]/20 @error('email') border-red-400 @enderror" />
                    </div>
                    @error('email')
                        <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mt-4">
                    <label for="password" class="mb-1.5 block text-[13px] font-bold text-gray-900">{{ __('auth.password') }}</label>
                    <div class="relative">
                        <span class="pointer-events-none absolute inset-y-0 left-4 flex items-center text-[#9a9a9a]" aria-hidden="true">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-[18px] w-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><rect x="4" y="10" width="16" height="10" rx="2" /><path d="M8 10V7a4 4 0 0 1 8 0v3" /></svg>
                        </span>
                        <input id="password" type="password" name="password" required autocomplete="current-password"
                            placeholder="••••••••"
                            class="h-[48px] w-full rounded-[12px] border border-[#dfe6d0] bg-white pl-11 pr-12 text-[14px] text-gray-900 placeholder-[#b5b5b5] outline-none transition focus:border-[#4a7c2e] focus:ring-2 focus:ring-[#4a7c2e]/20 @error('password') border-red-400 @enderror" />
                        <button type="button" id="togglePassword" class="absolute inset-y-0 right-3 flex items-center px-1 text-[#9a9a9a] transition hover:text-[#2f5d2a]" aria-label="Tampilkan kata sandi">
                            <svg id="eyeOpen" xmlns="http://www.w3.org/2000/svg" class="h-[18px] w-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z" /><circle cx="12" cy="12" r="3" /></svg>
                            <svg id="eyeClosed" xmlns="http://www.w3.org/2000/svg" class="hidden h-[18px] w-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path d="M3 3l18 18" /><path d="M10.5 5.2A10.8 10.8 0 0 1 12 5c6.5 0 10 7 10 7a17 17 0 0 1-3.2 3.9M6.6 6.6C4 8.2 2 12 2 12s3.5 7 10 7c1.4 0 2.7-.3 3.8-.8" /><path d="M9.9 9.9a3 3 0 0 0 4.2 4.2" /></svg>
                        </button>
                    </div>
                    @error('password')
                        <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mt-4 flex items-center justify-between text-[13px]">
                    <label class="flex cursor-pointer items-center gap-2 text-[#4a4a4a]">
                        <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }} class="h-4 w-4 rounded border-gray-300 accent-[#2f5d2a]" />
                        {{ __('auth.remember') }}
                    </label>
                    <a href="{{ route('password.request') }}" class="font-semibold text-[#234d1d] hover:underline">{{ __('auth.forgot') }}</a>
                </div>

                <button type="submit" class="mt-5 h-[50px] w-full rounded-full bg-[#36682f] text-[15px] font-medium text-white shadow-[0_10px_24px_-10px_rgba(54,104,47,0.8)] transition hover:bg-[#2a5425] active:scale-[0.99]">
                    {{ __('auth.login_button') }}
                </button>

                <div class="mt-6 border-t border-gray-100 pt-5 text-center text-[13px] text-[#6b6b6b]">
                    {{ __('auth.no_account') }}
                    <a href="{{ route('register') }}" class="font-bold text-[#234d1d] hover:underline">{{ __('auth.register_button') }}</a>
                </div>
            </form>
        </div>
    </main>

    <script>
        (function () {
            var btn = document.getElementById('togglePassword');
            var input = document.getElementById('password');
            var eyeOpen = document.getElementById('eyeOpen');
            var eyeClosed = document.getElementById('eyeClosed');
            if (!btn || !input) return;
            btn.addEventListener('click', function () {
                var show = input.type === 'password';
                input.type = show ? 'text' : 'password';
                eyeOpen.classList.toggle('hidden', show);
                eyeClosed.classList.toggle('hidden', !show);
                btn.setAttribute('aria-label', show ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi');
            });
        })();
    </script>
</body>
</html>
