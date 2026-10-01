{{-- Greetings in several languages, typed one after another. --}}
@php($greetings = ['Hello', 'مرحبًا', 'سڵاو', 'Bonjour', 'Hola'])
<p aria-hidden="true" class="flex h-10 items-center text-3xl font-semibold tracking-tight text-zinc-900"
    x-data="{ words: @js($greetings), index: 0 }" x-init="setInterval(() => index = (index + 1) % words.length, 1800)">
    <span x-text="words[index]">{{ $greetings[0] }}</span><span class="animate-blink ms-0.5 text-primary-500">|</span>
</p>
