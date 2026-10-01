{{-- Six digits typed in one by one, then verified. --}}
<div aria-hidden="true" class="flex justify-center" dir="ltr"
    x-data="{ digits: ['', '', '', '', '', ''], run() { const code = String(Math.floor(100000 + Math.random() * 900000)); this.digits = ['', '', '', '', '', '']; code.split('').forEach((d, i) => setTimeout(() => this.digits[i] = d, 250 * (i + 1))) } }"
    x-init="run(); setInterval(() => run(), 4500)">
    <div class="w-full max-w-sm rounded-3xl border border-zinc-200 bg-white p-5 shadow-[0_24px_60px_-24px_rgb(76_29_149/0.35)]">
        <div class="flex items-center gap-2 text-xs font-medium text-zinc-500"><x-heroicon-s-shield-check class="size-4 text-emerald-500" /> {{ config('app.name') }}</div>
        <div class="mt-4 flex justify-between gap-2">
            <template x-for="(digit, index) in digits" x-bind:key="index">
                <span class="flex h-14 flex-1 items-center justify-center rounded-xl border font-mono text-2xl font-semibold transition"
                    x-bind:class="digit ? 'border-primary-300 bg-primary-50 text-primary-700' : 'border-zinc-200 bg-zinc-50 text-zinc-300'"
                    x-text="digit || '•'"></span>
            </template>
        </div>
        <div class="mt-4 flex items-center justify-between text-xs">
            <span class="text-zinc-500">{{ __('Recovery codes') }}: 8</span>
            <span class="font-semibold text-emerald-600" x-show="digits[5]" x-transition>✓ {{ __('Verified') }}</span>
        </div>
    </div>
</div>
