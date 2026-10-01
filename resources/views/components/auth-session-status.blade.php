@props(['status'])

@if ($status)
    <x-ui.callout variant="success" {{ $attributes }}>{{ $status }}</x-ui.callout>
@endif
