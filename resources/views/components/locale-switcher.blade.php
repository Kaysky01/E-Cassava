@props(['onDark' => true])

<div @class([
    'flex items-center gap-1 rounded-2xl p-1 text-sm ring-1',
    'bg-white/10 ring-white/15' => $onDark,
    'bg-gray-100 ring-gray-200' => ! $onDark,
]) role="group" aria-label="{{ __('app.toggle_lang') }}">
    @foreach (['id' => 'ID', 'en' => 'EN'] as $code => $short)
        <a href="{{ route('locale.switch', $code) }}"
            @class([
                'rounded-xl px-2.5 py-1.5 font-bold transition',
                'bg-white text-cassava-900 shadow' => $onDark && app()->getLocale() === $code,
                'text-cassava-100 hover:bg-white/10 hover:text-white' => $onDark && app()->getLocale() !== $code,
                'bg-cassava-600 text-white shadow' => ! $onDark && app()->getLocale() === $code,
                'text-gray-500 hover:bg-white hover:text-cassava-800' => ! $onDark && app()->getLocale() !== $code,
            ])
            aria-pressed="{{ app()->getLocale() === $code ? 'true' : 'false' }}">{{ $short }}</a>
    @endforeach
</div>
