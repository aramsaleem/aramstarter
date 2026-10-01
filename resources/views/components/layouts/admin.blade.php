{{-- The admin panel shares the app shell; its pages add the "Administration" context through breadcrumbs. --}}
@props(['title' => null])

<x-layouts.shell :title="$title">
    {{ $slot }}
</x-layouts.shell>
