@php
    $hasPassword = auth()->user()->hasPassword();
@endphp

<x-settings.layout
    :heading="$hasPassword ? __('Update password') : __('Set a password')"
    :subheading="__('Use a long, random password to keep your account secure.')"
>
    @unless ($hasPassword)
        <x-ui.callout class="mb-6">
            {{ __('You signed up with a social account. Set a password to also log in with your email address.') }}
        </x-ui.callout>
    @endunless

    <form wire:submit="updatePassword" class="space-y-6">
        @if ($hasPassword)
            <x-ui.input wire:model="current_password" :label="__('Current password')" autocomplete="current-password" viewable required />
        @endif

        <x-ui.input wire:model="password" :label="__('New password')" autocomplete="new-password" viewable required />

        <x-ui.input wire:model="password_confirmation" :label="__('Confirm password')" autocomplete="new-password" viewable required />

        <x-ui.button type="submit" loading="updatePassword">{{ __('Save') }}</x-ui.button>
    </form>
</x-settings.layout>
