{{-- Floating social login buttons. --}}
<div aria-hidden="true" class="flex gap-3">
    @foreach (['google', 'facebook', 'x'] as $index => $provider)
        <span class="animate-float flex size-12 items-center justify-center rounded-2xl border border-zinc-200 bg-white shadow-sm" style="animation-delay: -{{ $index * 2 }}s">
            <x-dynamic-component :component="'icons.'.$provider" class="size-5" />
        </span>
    @endforeach
</div>
