@php
if (! isset($scrollTo)) {
    $scrollTo = false;
}

$scrollIntoViewJsSnippet = ($scrollTo !== false)
    ? <<<JS
       (\$el.closest('{$scrollTo}') || document.querySelector('{$scrollTo}')).scrollIntoView({ behavior: 'smooth', block: 'start' })
    JS
    : '';
@endphp

@if ($paginator->hasPages())
<nav role="navigation" aria-label="Navigasi halaman" class="pagination-nav">

    {{-- Mobile: prev / info / next --}}
    <div class="flex sm:hidden items-center justify-between gap-3 w-full">
        @if ($paginator->onFirstPage())
            <span class="pagination-btn pagination-btn--disabled">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                <span>Sebelumnya</span>
            </span>
        @else
            <button type="button"
                    wire:click="previousPage('{{ $paginator->getPageName() }}')"
                    @if($scrollIntoViewJsSnippet) x-on:click="{{ $scrollIntoViewJsSnippet }}" @endif
                    wire:loading.attr="disabled"
                    class="pagination-btn">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                <span>Sebelumnya</span>
            </button>
        @endif

        <span class="text-xs font-semibold text-gray-500 whitespace-nowrap px-2">
            Hal. <span class="text-emerald-700">{{ $paginator->currentPage() }}</span> / {{ $paginator->lastPage() }}
        </span>

        @if ($paginator->hasMorePages())
            <button type="button"
                    wire:click="nextPage('{{ $paginator->getPageName() }}')"
                    @if($scrollIntoViewJsSnippet) x-on:click="{{ $scrollIntoViewJsSnippet }}" @endif
                    wire:loading.attr="disabled"
                    class="pagination-btn">
                <span>Selanjutnya</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </button>
        @else
            <span class="pagination-btn pagination-btn--disabled">
                <span>Selanjutnya</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </span>
        @endif
    </div>

    {{-- Desktop: tombol halaman --}}
    <div class="hidden sm:flex items-center gap-1">
        @if ($paginator->onFirstPage())
            <span class="pagination-page pagination-page--nav pagination-page--disabled" aria-disabled="true">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </span>
        @else
            <button type="button"
                    wire:click="previousPage('{{ $paginator->getPageName() }}')"
                    @if($scrollIntoViewJsSnippet) x-on:click="{{ $scrollIntoViewJsSnippet }}" @endif
                    wire:loading.attr="disabled"
                    class="pagination-page pagination-page--nav"
                    aria-label="Halaman sebelumnya">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </button>
        @endif

        @foreach ($elements as $element)
            @if (is_string($element))
                <span class="pagination-page pagination-page--dots" aria-hidden="true">{{ $element }}</span>
            @endif

            @if (is_array($element))
                @foreach ($element as $page => $url)
                    <span wire:key="paginator-{{ $paginator->getPageName() }}-page{{ $page }}">
                        @if ($page == $paginator->currentPage())
                            <span class="pagination-page pagination-page--active" aria-current="page">{{ $page }}</span>
                        @else
                            <button type="button"
                                    wire:click="gotoPage({{ $page }}, '{{ $paginator->getPageName() }}')"
                                    @if($scrollIntoViewJsSnippet) x-on:click="{{ $scrollIntoViewJsSnippet }}" @endif
                                    wire:loading.attr="disabled"
                                    class="pagination-page"
                                    aria-label="Ke halaman {{ $page }}">
                                {{ $page }}
                            </button>
                        @endif
                    </span>
                @endforeach
            @endif
        @endforeach

        @if ($paginator->hasMorePages())
            <button type="button"
                    wire:click="nextPage('{{ $paginator->getPageName() }}')"
                    @if($scrollIntoViewJsSnippet) x-on:click="{{ $scrollIntoViewJsSnippet }}" @endif
                    wire:loading.attr="disabled"
                    class="pagination-page pagination-page--nav"
                    aria-label="Halaman selanjutnya">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </button>
        @else
            <span class="pagination-page pagination-page--nav pagination-page--disabled" aria-disabled="true">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </span>
        @endif
    </div>
</nav>
@endif
