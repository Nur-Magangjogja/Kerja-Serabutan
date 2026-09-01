@if ($paginator->hasPages())
@php
    $currentPage = $paginator->currentPage();
    $lastPage = $paginator->lastPage();
    $pageName = $paginator->getPageName();
    $maxPages = 5;

    if ($lastPage <= $maxPages) {
        $startPage = 1;
        $endPage = $lastPage;
    } else {
        $half = (int) floor($maxPages / 2);
        if ($currentPage <= $half + 1) {
            $startPage = 1;
            $endPage = $maxPages;
        } elseif ($currentPage >= $lastPage - $half) {
            $startPage = $lastPage - $maxPages + 1;
            $endPage = $lastPage;
        } else {
            $startPage = $currentPage - $half;
            $endPage = $currentPage + $half;
        }
    }
@endphp
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 select-none">

    {{-- Info Counter Kiri --}}
    <div class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-gray-100 dark:bg-gray-700/60 border border-gray-200/60 dark:border-gray-600/60 font-medium">
            <svg class="w-3.5 h-3.5 text-primary-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
            </svg>
            <span>
                Menampilkan
                <strong class="text-gray-800 dark:text-gray-200">{{ number_format($paginator->firstItem()) }}</strong>
                &ndash;
                <strong class="text-gray-800 dark:text-gray-200">{{ number_format($paginator->lastItem()) }}</strong>
                dari
                <strong class="text-gray-800 dark:text-gray-200">{{ number_format($paginator->total()) }}</strong>
                data
            </span>
        </span>
    </div>

    {{-- Navigasi Halaman --}}
    <nav role="navigation" aria-label="Navigasi Halaman" class="inline-flex items-center gap-1 flex-wrap justify-center">

        {{-- Tombol Halaman Pertama (⏮) & Sebelumnya (◀) --}}
        @if ($paginator->onFirstPage())
            <span aria-disabled="true" class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-100 dark:bg-gray-800/60 text-gray-300 dark:text-gray-600 cursor-not-allowed" title="Halaman Pertama">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7M4 12h16"/></svg>
            </span>
            <span aria-disabled="true" class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-100 dark:bg-gray-800/60 text-gray-300 dark:text-gray-600 cursor-not-allowed" title="Halaman Sebelumnya">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </span>
        @else
            <button type="button" wire:key="paginator-{{ $pageName }}-first" wire:click="gotoPage(1, '{{ $pageName }}')" wire:loading.attr="disabled"
               class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-primary-50 dark:hover:bg-primary-900/20 hover:text-primary-600 dark:hover:text-primary-400 hover:border-primary-300 dark:hover:border-primary-700 transition-colors cursor-pointer"
               title="Halaman Pertama">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7M4 12h16"/></svg>
            </button>
            <button type="button" wire:key="paginator-{{ $pageName }}-prev" wire:click="previousPage('{{ $pageName }}')" wire:loading.attr="disabled"
               class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-primary-50 dark:hover:bg-primary-900/20 hover:text-primary-600 dark:hover:text-primary-400 hover:border-primary-300 dark:hover:border-primary-700 transition-colors cursor-pointer"
               title="Halaman Sebelumnya">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </button>
        @endif

        {{-- 5 Nomor Halaman Terhitung Maksimal --}}
        <div class="inline-flex items-center gap-1 mx-0.5">
            @for ($page = $startPage; $page <= $endPage; $page++)
                @if ($page == $currentPage)
                    <span wire:key="paginator-{{ $pageName }}-page-{{ $page }}-active" aria-current="page"
                          class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-primary-600 text-white font-bold text-xs shadow-xs shadow-primary-500/30 border border-primary-600">
                        {{ $page }}
                    </span>
                @else
                    <button type="button" wire:key="paginator-{{ $pageName }}-page-{{ $page }}" wire:click="gotoPage({{ $page }}, '{{ $pageName }}')" wire:loading.attr="disabled"
                       class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-primary-50 dark:hover:bg-primary-900/20 hover:text-primary-600 dark:hover:text-primary-400 hover:border-primary-300 dark:hover:border-primary-700 text-xs font-semibold transition-colors cursor-pointer">
                        {{ $page }}
                    </button>
                @endif
            @endfor
        </div>

        {{-- Tombol Halaman Berikutnya (▶) & Terakhir (⏭) --}}
        @if ($paginator->hasMorePages())
            <button type="button" wire:key="paginator-{{ $pageName }}-next" wire:click="nextPage('{{ $pageName }}')" wire:loading.attr="disabled"
               class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-primary-50 dark:hover:bg-primary-900/20 hover:text-primary-600 dark:hover:text-primary-400 hover:border-primary-300 dark:hover:border-primary-700 transition-colors cursor-pointer"
               title="Halaman Berikutnya">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </button>
            <button type="button" wire:key="paginator-{{ $pageName }}-last" wire:click="gotoPage({{ $lastPage }}, '{{ $pageName }}')" wire:loading.attr="disabled"
               class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-primary-50 dark:hover:bg-primary-900/20 hover:text-primary-600 dark:hover:text-primary-400 hover:border-primary-300 dark:hover:border-primary-700 transition-colors cursor-pointer"
               title="Halaman Terakhir">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M20 12H4"/></svg>
            </button>
        @else
            <span aria-disabled="true" class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-100 dark:bg-gray-800/60 text-gray-300 dark:text-gray-600 cursor-not-allowed" title="Halaman Berikutnya">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </span>
            <span aria-disabled="true" class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-100 dark:bg-gray-800/60 text-gray-300 dark:text-gray-600 cursor-not-allowed" title="Halaman Terakhir">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M20 12H4"/></svg>
            </span>
        @endif

    </nav>
</div>
@endif
