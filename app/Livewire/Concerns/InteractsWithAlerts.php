<?php

namespace App\Livewire\Concerns;

use Jantinnerezo\LivewireAlert\LivewireAlert;

/**
 * SweetAlert2 toasts and confirmation dialogs via jantinnerezo/livewire-alert.
 *
 * Titles often contain user input (names, role names), and SweetAlert renders the "title"
 * option as HTML. Always pass them as "titleText", which is rendered as plain text.
 */
trait InteractsWithAlerts
{
    /**
     * Show a toast on the current page.
     *
     * @param  'success'|'error'|'warning'|'info'  $icon
     */
    protected function toast(string $title, string $icon = 'success'): void
    {
        $this->newAlert()->withOptions(['titleText' => $title])->{$icon}()->asToast()->show();
    }

    /**
     * Show a toast on the next page - use this before redirecting.
     *
     * @param  'success'|'error'|'warning'|'info'  $icon
     */
    protected function flashToast(string $title, string $icon = 'success'): void
    {
        session()->flash('alert', ['icon' => $icon, 'title' => $title]);
    }

    /**
     * Ask for confirmation of a destructive action, then call the public $method with $data on this component.
     *
     * @param  array<string, mixed>  $data
     */
    protected function askForConfirmation(string $title, string $text, string $method, array $data = [], ?string $confirmButtonText = null): void
    {
        $this->newAlert()
            ->withOptions(['titleText' => $title])
            ->text($text)
            ->warning()
            ->withConfirmButton($confirmButtonText ?? __('Confirm'))
            ->confirmButtonColor('#dc2626')
            ->withCancelButton(__('Cancel'))
            ->timer(null)
            ->onConfirm($method, $data)
            ->show();
    }

    /**
     * The package facade caches the first instance (and the component it is bound to),
     * so resolve a fresh one from the container for every alert.
     */
    private function newAlert(): LivewireAlert
    {
        return app(LivewireAlert::class);
    }
}
