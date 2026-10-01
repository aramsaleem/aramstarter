{{-- The keyboard shortcut, with a mini search box. --}}
<div aria-hidden="true" class="space-y-3" dir="ltr">
    <div class="flex h-10 items-center gap-2 rounded-xl border border-zinc-200 bg-white px-3 text-sm text-zinc-400 shadow-xs">
        <x-heroicon-o-magnifying-glass class="size-4" />
        <span class="flex-1">{{ __('Search…') }}</span>
        <kbd class="rounded-md border border-zinc-200 bg-zinc-50 px-1.5 font-mono text-[0.7rem] text-zinc-500">⌘K</kbd>
    </div>
    <div class="flex gap-2">
        <kbd class="rounded-lg border border-b-4 border-zinc-300 bg-white px-3 py-1.5 font-mono text-sm text-zinc-700 shadow-xs">Ctrl</kbd>
        <kbd class="rounded-lg border border-b-4 border-zinc-300 bg-white px-3 py-1.5 font-mono text-sm text-zinc-700 shadow-xs">K</kbd>
    </div>
</div>
