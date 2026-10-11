<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" class="scroll-smooth">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>@yield('title', 'E-CASSAVA')</title>
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><rect width='100' height='100' rx='24' fill='%23548035'/><text x='50' y='69' font-size='50' font-family='Arial,sans-serif' font-weight='bold' text-anchor='middle' fill='white'>EC</text></svg>" />
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
    <meta name="csrf-token" content="{{ csrf_token() }}" />
</head>
<body class="flex min-h-screen items-center justify-center bg-[#f7fbf4] p-4 font-sans">
    <div class="pointer-events-none fixed inset-0 -z-10 overflow-hidden" aria-hidden="true">
        <div class="absolute -top-32 -left-32 h-96 w-96 rounded-full bg-cassava-200/50 blur-3xl"></div>
        <div class="absolute -bottom-24 -right-24 h-80 w-80 rounded-full bg-emerald-100/60 blur-3xl"></div>
    </div>
    <div class="w-full max-w-md">
        <a href="/" class="mb-6 flex items-center justify-center gap-3">
            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-lime-300 via-cassava-400 to-cassava-600 shadow-lg" aria-hidden="true"><span class="font-display text-base font-extrabold tracking-tight text-white">EC</span></div>
            <span class="font-display text-2xl font-extrabold tracking-tight">E-CASSAVA</span>
        </a>
        <div class="card p-6 sm:p-8">
            <div class="mb-5 flex items-center justify-end gap-2">
                <x-locale-switcher :on-dark="false" />
            </div>
            @if (session('status'))
                <div class="mb-4 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900" role="alert">{{ session('status') }}</div>
            @endif
            @yield('content')
        </div>
        <p class="mt-4 text-center text-xs text-gray-400">© {{ date('Y') }} E-CASSAVA</p>
    </div>
    @stack('scripts')
</body>
</html>
