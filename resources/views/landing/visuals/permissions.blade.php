{{-- Permission switches flipping on and off. --}}
<div aria-hidden="true" class="space-y-2" x-data="{ on: [true, true, false] }" x-init="setInterval(() => on = on.map((v, i) => i === Math.floor(Math.random() * 3) ? ! v : v), 1600)">
    @foreach (['users.view', 'users.update', 'roles.delete'] as $index => $permission)
        <div class="flex items-center justify-between rounded-xl border border-zinc-200 bg-white px-3 py-2 shadow-xs">
            <code class="font-mono text-xs text-zinc-600" dir="ltr">{{ $permission }}</code>
            <span class="flex h-5 w-9 items-center rounded-full p-0.5 transition" x-bind:class="on[{{ $index }}] ? 'bg-primary-500' : 'bg-zinc-200'">
                <span class="size-4 rounded-full bg-white shadow transition" x-bind:class="on[{{ $index }}] ? 'translate-x-4' : ''"></span>
            </span>
        </div>
    @endforeach
</div>
