@php
    if (! isset($scrollTo)) {
        $scrollTo = 'body';
    }

    $scrollIntoViewJsSnippet = ($scrollTo !== false)
        ? <<<JS
           (\$el.closest('{$scrollTo}') || document.querySelector('{$scrollTo}')).scrollIntoView()
        JS
        : '';

    $pageName = $paginator->getPageName();
    $link = 'inline-flex h-9 min-w-9 items-center justify-center rounded-xl px-3 text-sm font-medium transition-colors';
    $idle = 'text-zinc-600 hover:bg-zinc-900/5 hover:text-zinc-900 dark:text-zinc-300 dark:hover:bg-white/[0.07] dark:hover:text-white';
    $disabled = 'cursor-default text-zinc-300 dark:text-zinc-600';
@endphp

<div>
    @if ($paginator->hasPages())
        <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}" class="flex items-center justify-between gap-4">
            <p class="hidden text-sm text-zinc-500 sm:block dark:text-zinc-400">
                {{ __('Showing :first to :last of :total results', [
                    'first' => $paginator->firstItem(),
                    'last' => $paginator->lastItem(),
                    'total' => $paginator->total(),
                ]) }}
            </p>

            <div class="flex flex-1 items-center justify-between gap-1 sm:flex-none sm:justify-end">
                @if ($paginator->onFirstPage())
                    <span class="{{ $link }} {{ $disabled }}" aria-disabled="true">
                        <x-heroicon-o-chevron-left class="size-4 rtl:-scale-x-100" />
                        <span class="sr-only">{{ __('Previous') }}</span>
                    </span>
                @else
                    <button type="button" wire:click="previousPage('{{ $pageName }}')" x-on:click="{{ $scrollIntoViewJsSnippet }}" wire:loading.attr="disabled" class="{{ $link }} {{ $idle }}" aria-label="{{ __('Previous') }}">
                        <x-heroicon-o-chevron-left class="size-4 rtl:-scale-x-100" />
                    </button>
                @endif

                <div class="hidden items-center gap-1 sm:flex">
                    @foreach ($elements as $element)
                        @if (is_string($element))
                            <span class="{{ $link }} {{ $disabled }}">{{ $element }}</span>
                        @endif

                        @if (is_array($element))
                            @foreach ($element as $page => $url)
                                <span wire:key="paginator-{{ $pageName }}-page{{ $page }}">
                                    @if ($page == $paginator->currentPage())
                                        <span class="{{ $link }} bg-linear-to-b from-primary-500 to-primary-600 text-white shadow-md shadow-primary-600/25" aria-current="page">{{ $page }}</span>
                                    @else
                                        <button type="button" wire:click="gotoPage({{ $page }}, '{{ $pageName }}')" x-on:click="{{ $scrollIntoViewJsSnippet }}" class="{{ $link }} {{ $idle }}" aria-label="{{ __('Go to page :page', ['page' => $page]) }}">
                                            {{ $page }}
                                        </button>
                                    @endif
                                </span>
                            @endforeach
                        @endif
                    @endforeach
                </div>

                <span class="text-sm text-zinc-500 sm:hidden dark:text-zinc-400">
                    {{ $paginator->currentPage() }} / {{ $paginator->lastPage() }}
                </span>

                @if ($paginator->hasMorePages())
                    <button type="button" wire:click="nextPage('{{ $pageName }}')" x-on:click="{{ $scrollIntoViewJsSnippet }}" wire:loading.attr="disabled" class="{{ $link }} {{ $idle }}" aria-label="{{ __('Next') }}">
                        <x-heroicon-o-chevron-right class="size-4 rtl:-scale-x-100" />
                    </button>
                @else
                    <span class="{{ $link }} {{ $disabled }}" aria-disabled="true">
                        <x-heroicon-o-chevron-right class="size-4 rtl:-scale-x-100" />
                        <span class="sr-only">{{ __('Next') }}</span>
                    </span>
                @endif
            </div>
        </nav>
    @endif
</div>
