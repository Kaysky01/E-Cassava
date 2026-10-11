@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination" class="flex flex-col items-center justify-between gap-3 px-5 py-4 sm:flex-row">
        <p class="text-xs text-gray-500">
            {!! __('pagination.showing', [
                'from' => '<span class="font-mono font-bold text-gray-700">' . $paginator->firstItem() . '</span>',
                'to' => '<span class="font-mono font-bold text-gray-700">' . $paginator->lastItem() . '</span>',
                'total' => '<span class="font-mono font-bold text-gray-700">' . $paginator->total() . '</span>',
            ]) !!}
        </p>
        <div class="flex items-center gap-1.5">
            {{-- Sebelumnya --}}
            @if ($paginator->onFirstPage())
                <span aria-disabled="true" class="flex h-9 w-9 cursor-not-allowed items-center justify-center rounded-xl bg-gray-100 text-gray-300">‹</span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Halaman sebelumnya"
                    class="flex h-9 w-9 items-center justify-center rounded-xl bg-white text-lg font-bold text-cassava-700 ring-1 ring-cassava-200 transition hover:bg-cassava-50 hover:ring-cassava-400">‹</a>
            @endif

            {{-- Nomor halaman (ringkas di HP) --}}
            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="hidden px-1 font-mono text-sm text-gray-400 sm:inline">{{ $element }}</span>
                @endif
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span aria-current="page" class="flex h-9 min-w-9 items-center justify-center rounded-xl bg-gradient-to-b from-cassava-500 to-cassava-700 px-2 font-mono text-sm font-bold text-white shadow">{{ $page }}</span>
                        @elseif (abs($page - $paginator->currentPage()) <= 1 || $page === 1 || $page === $paginator->lastPage())
                            <a href="{{ $url }}" aria-label="Halaman {{ $page }}"
                                class="hidden h-9 min-w-9 items-center justify-center rounded-xl bg-white px-2 font-mono text-sm font-semibold text-gray-600 ring-1 ring-gray-200 transition hover:bg-cassava-50 hover:text-cassava-800 hover:ring-cassava-300 sm:flex">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Berikutnya --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Halaman berikutnya"
                    class="flex h-9 w-9 items-center justify-center rounded-xl bg-white text-lg font-bold text-cassava-700 ring-1 ring-cassava-200 transition hover:bg-cassava-50 hover:ring-cassava-400">›</a>
            @else
                <span aria-disabled="true" class="flex h-9 w-9 cursor-not-allowed items-center justify-center rounded-xl bg-gray-100 text-gray-300">›</span>
            @endif
        </div>
    </nav>
@endif
