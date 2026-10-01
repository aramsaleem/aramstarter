@props(['title' => null])

<x-layouts.shell :title="$title">
    {{ $slot }}
</x-layouts.shell>
