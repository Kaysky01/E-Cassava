<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" class="scroll-smooth">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>@yield('title', __('landing.meta_title'))</title>
    <meta name="description" content="@yield('meta_description', __('landing.meta_description'))" />
    <meta property="og:title" content="@yield('title', __('landing.meta_title'))" />
    <meta property="og:description" content="@yield('meta_description', __('landing.meta_description'))" />
    <meta property="og:type" content="website" />
    <meta name="theme-color" content="#2f5d2a" />
    <link rel="icon" href="{{ asset('images/logo.png') }}" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" />
    <script>
        // E-CASSAVA selalu terang: bersihkan sisa preferensi gelap lama.
        try {
            localStorage.removeItem('theme');
            document.documentElement.classList.remove('dark');
            document.documentElement.style.colorScheme = 'light';
        } catch (e) {}
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="flex min-h-screen flex-col bg-[#f1f4e8] font-sans text-gray-800 antialiased">

    {{-- Navbar pil mengambang --}}
    <div class="sticky top-3 z-50 mx-auto w-[calc(100%-1.5rem)] max-w-6xl" x-data="{ menu: false }">
        <header class="flex items-center justify-between gap-2 rounded-full bg-white/95 py-2 pl-3 pr-2 shadow-[0_10px_36px_-12px_rgba(30,58,26,0.35)] ring-1 ring-black/5 backdrop-blur sm:pl-4">
            <a href="/" class="flex min-w-0 items-center gap-2.5">
                <img src="{{ asset('images/logo.png') }}" alt="E-CASSAVA"
                    class="h-9 w-9 shrink-0 rounded-full object-cover ring-1 ring-black/5"
                    onerror="this.style.display='none'" />
                <span class="min-w-0 leading-none">
                    <span class="block truncate font-display text-[15px] font-extrabold tracking-tight text-[#234d1d]">E-CASSAVA</span>
                    <span class="mt-1 hidden text-[8.5px] font-semibold uppercase tracking-[0.22em] text-[#9db18d] min-[420px]:block">Electronic Nose System</span>
                </span>
            </a>
            <nav class="hidden items-center gap-1 text-[13px] font-bold lg:flex" aria-label="Landing">
                <a href="#fitur" class="rounded-full bg-[#2f5d2a] px-4 py-2 text-white">{{ __('landing.nav_features') }}</a>
                <a href="#cara-kerja" class="rounded-full px-4 py-2 text-gray-600 transition hover:bg-gray-100 hover:text-[#234d1d]">{{ __('landing.nav_how') }}</a>
                <a href="#demo" class="rounded-full px-4 py-2 text-gray-600 transition hover:bg-gray-100 hover:text-[#234d1d]">{{ __('landing.nav_demo') }}</a>
            </nav>
            <div class="flex shrink-0 items-center gap-1.5 sm:gap-2">
                <div class="hidden items-center gap-0.5 rounded-full border border-[#e6e8d8] bg-[#fafaf4] p-1 text-[11px] font-bold min-[420px]:flex" role="group" aria-label="{{ __('app.toggle_lang') }}">
                    @foreach (['id' => 'ID', 'en' => 'EN'] as $code => $short)
                        <a href="{{ route('locale.switch', $code) }}"
                            class="{{ app()->getLocale() === $code ? 'rounded-full bg-[#2f5d2a] px-2.5 py-1 text-white' : 'rounded-full px-2.5 py-1 text-gray-500 hover:text-[#2f5d2a]' }}">{{ $short }}</a>
                    @endforeach
                </div>
                <a href="{{ auth()->check() ? route('dashboard') : route('login') }}" class="relative flex h-9 w-9 items-center justify-center rounded-full text-gray-500 transition hover:bg-gray-100 hover:text-[#234d1d]" aria-label="Notifikasi">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path d="M15 17h5l-1.4-1.4A2 2 0 0 1 18 14.2V11a6 6 0 0 0-4-5.7V5a2 2 0 1 0-4 0v.3A6 6 0 0 0 6 11v3.2a2 2 0 0 1-.6 1.4L4 17h5" /><path d="M10 21a2 2 0 0 0 4 0" /></svg>
                    <span class="absolute right-1.5 top-1.5 flex h-3.5 w-3.5 items-center justify-center rounded-full bg-red-500 text-[8px] font-bold text-white ring-2 ring-white">1</span>
                </a>
                @auth
                    <a href="{{ route('dashboard') }}" class="hidden rounded-full bg-[#2f5d2a] px-5 py-2.5 text-[13px] font-bold text-white transition hover:bg-[#234d1d] sm:inline-block">Dashboard</a>
                @else
                    <a href="{{ route('register') }}" class="rounded-full bg-[#2f5d2a] px-3.5 py-2.5 text-[12px] font-bold text-white transition hover:bg-[#234d1d] min-[420px]:px-5 min-[420px]:text-[13px]">{{ __('landing.register') }}</a>
                @endauth
                <button type="button" @click="menu = !menu" :aria-expanded="menu" aria-label="Menu"
                    class="flex h-9 w-9 items-center justify-center rounded-full text-gray-600 transition hover:bg-gray-100 lg:hidden">
                    <svg x-show="!menu" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h16" /></svg>
                    <svg x-show="menu" x-cloak class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18" /></svg>
                </button>
            </div>
        </header>
        <div x-show="menu" x-cloak x-transition class="mt-2 rounded-3xl bg-white p-3 shadow-xl ring-1 ring-black/5 lg:hidden">
            <nav class="grid gap-1 text-sm font-bold" aria-label="Landing mobile">
                <a href="#fitur" @click="menu = false" class="rounded-2xl bg-[#2f5d2a] px-4 py-3 text-white">{{ __('landing.nav_features') }}</a>
                <a href="#cara-kerja" @click="menu = false" class="rounded-2xl px-4 py-3 text-gray-600 hover:bg-gray-100">{{ __('landing.nav_how') }}</a>
                <a href="#demo" @click="menu = false" class="rounded-2xl px-4 py-3 text-gray-600 hover:bg-gray-100">{{ __('landing.nav_demo') }}</a>
                <div class="flex items-center gap-0.5 rounded-2xl border border-[#e6e8d8] bg-[#fafaf4] p-1 text-xs font-bold min-[420px]:hidden">
                    @foreach (['id' => 'ID', 'en' => 'EN'] as $code => $short)
                        <a href="{{ route('locale.switch', $code) }}" class="flex-1 rounded-xl px-3 py-2 text-center {{ app()->getLocale() === $code ? 'bg-[#2f5d2a] text-white' : 'text-gray-500' }}">{{ $short }}</a>
                    @endforeach
                </div>
                @guest
                    <a href="{{ route('login') }}" class="rounded-2xl bg-gray-100 px-4 py-3 text-center text-gray-700">{{ __('landing.login') }}</a>
                @endguest
            </nav>
        </div>
    </div>

    <main class="w-full flex-grow">
        @yield('content')
    </main>

    <footer class="bg-white">
        <div class="mx-auto grid w-full gap-8 px-4 py-12 sm:px-6 md:grid-cols-3">
            <div>
                <div class="flex items-center gap-2.5">
                    <img src="{{ asset('images/logo.png') }}" alt="E-CASSAVA" class="h-9 w-9 rounded-full object-cover ring-1 ring-black/5" onerror="this.style.display='none'" />
                    <p class="font-display text-[15px] font-extrabold tracking-tight text-[#234d1d]">E-CASSAVA</p>
                </div>
                <p class="mt-3 max-w-xs text-[13px] leading-relaxed text-gray-500">{{ __('landing.footer_tagline') }}</p>
                <p class="mt-4 text-[10px] font-bold uppercase tracking-[0.16em] text-gray-400">{{ __('landing.footer_integration') }}</p>
                <div class="mt-2 flex flex-wrap gap-1.5">
                    @foreach (['ESP32', 'Laravel', 'SVM', 'FastAPI'] as $t)
                        <span class="rounded-md bg-gray-100 px-2 py-1 font-mono text-[10px] font-bold text-gray-500">{{ $t }}</span>
                    @endforeach
                </div>
            </div>
            <div>
                <p class="text-[11px] font-extrabold uppercase tracking-[0.16em] text-[#234d1d]">{{ __('landing.footer_nav') }}</p>
                <ul class="mt-3 space-y-2.5 text-[13px] font-medium text-gray-500">
                    <li><a href="#demo" class="transition hover:text-[#234d1d]">{{ __('landing.footer_l1') }}</a></li>
                    <li><a href="#cara-kerja" class="transition hover:text-[#234d1d]">{{ __('landing.footer_l2') }}</a></li>
                    <li><a href="#fitur" class="transition hover:text-[#234d1d]">{{ __('landing.footer_l3') }}</a></li>
                    <li><a href="{{ route('login') }}" class="transition hover:text-[#234d1d]">{{ __('landing.footer_l4') }}</a></li>
                </ul>
            </div>
            <div>
                <p class="text-[11px] font-extrabold uppercase tracking-[0.16em] text-[#234d1d]">{{ __('landing.footer_consortium') }}</p>
                <p class="mt-3 text-[13px] leading-relaxed text-gray-500">{{ __('landing.footer_consortium_desc') }}</p>
                <p class="mt-3 inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-3 py-1.5 text-[11px] font-bold text-emerald-700 ring-1 ring-emerald-100">
                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>{{ __('landing.footer_sensor_badge') }}
                </p>
            </div>
        </div>
        <div class="border-t border-gray-100">
            <div class="mx-auto flex w-full flex-col items-center justify-between gap-2 px-4 py-5 text-[12px] text-gray-400 sm:flex-row sm:px-6">
                <p>© {{ date('Y') }} E-Cassava System. {{ __('landing.footer_rights') }}</p>
                <div class="flex gap-5">
                    <a href="#" class="transition hover:text-[#234d1d]">{{ __('landing.footer_privacy') }}</a>
                    <a href="#" class="transition hover:text-[#234d1d]">{{ __('landing.footer_terms') }}</a>
                </div>
            </div>
        </div>
    </footer>
    @stack('scripts')
</body>
</html>
