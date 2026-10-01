{{-- Floating bar shown while an admin is signed in as another user. --}}
@php
    use App\Support\Impersonation;
@endphp

@if (Impersonation::active() && auth()->check())
    @php
        $endsAt = Impersonation::endsAt();
    @endphp

    {{-- Room at the end of the page, so the bar never covers the last bit of content --}}
    <div aria-hidden="true" class="h-24"></div>

    <div
        role="status"
        class="fixed inset-x-3 bottom-3 z-[70] mx-auto flex max-w-2xl flex-wrap items-center gap-x-4 gap-y-2 rounded-2xl bg-amber-50/95 p-2 ps-3 text-sm text-amber-950 shadow-[0_20px_50px_-15px_rgb(180_83_9/0.45)] ring-1 ring-amber-300 backdrop-blur-xl sm:flex-nowrap"
        x-data="{ endsAt: {{ $endsAt->getTimestamp() * 1000 }}, left: '' }"
        x-init="const tick = () => { const minutes = Math.max(0, Math.ceil((endsAt - Date.now()) / 60000)); left = minutes; }; tick(); setInterval(tick, 15000)"
    >
        <span class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-amber-200/70 text-amber-800">
            <x-heroicon-o-eye class="size-5" />
        </span>

        <p class="min-w-0 flex-1">
            <span class="block truncate font-semibold">{{ __('Signed in as :name', ['name' => auth()->user()->name]) }}</span>
            <span class="block truncate text-xs text-amber-800">
                {{ auth()->user()->email }}
                · <span x-text="@js(__('Ends in :minutes min')).replace(':minutes', left)">{{ __('Ends in :minutes min', ['minutes' => (int) ceil(now()->diffInSeconds($endsAt) / 60)]) }}</span>
            </span>
        </p>

        <form method="POST" action="{{ route('impersonation.leave') }}" class="shrink-0">
            @csrf
            <button type="submit" class="inline-flex h-9 items-center gap-2 rounded-xl bg-amber-900 px-4 text-sm font-semibold text-white shadow-sm transition hover:bg-amber-800 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-amber-700">
                <x-heroicon-o-arrow-uturn-left class="size-4" />
                {{ __('Return to my account') }}
            </button>
        </form>
    </div>
@endif
