@props(['align' => 'end', 'position' => 'bottom'])

@php
    use App\Support\Localization;
@endphp

<x-ui.dropdown :align="$align" :position="$position" width="w-48">
    <x-slot:trigger
        aria-label="{{ __('Change language') }}"
        class="inline-flex h-9 items-center gap-1.5 rounded-xl px-2.5 text-sm font-medium text-zinc-600 transition-colors hover:bg-zinc-900/5 hover:text-zinc-900 dark:text-zinc-300 dark:hover:bg-white/[0.07] dark:hover:text-white"
    >
        <x-heroicon-o-language class="size-4.5" />
        <span class="hidden sm:inline">{{ Localization::nativeName() }}</span>
    </x-slot:trigger>

    @foreach (Localization::supported() as $code => $locale)
        <form method="POST" action="{{ route('locale.update') }}">
            @csrf
            <input type="hidden" name="locale" value="{{ $code }}">

            <x-ui.dropdown-item type="submit" lang="{{ $code }}">
                <span class="flex-1">{{ $locale['native'] }}</span>
                <span class="text-xs text-zinc-400 uppercase">{{ $code }}</span>

                @if ($code === app()->getLocale())
                    <x-heroicon-s-check-circle class="size-4 text-primary-500" />
                @endif
            </x-ui.dropdown-item>
        </form>
    @endforeach
</x-ui.dropdown>
