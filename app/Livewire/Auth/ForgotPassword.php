<?php

namespace App\Livewire\Auth;

use App\Livewire\Concerns\RateLimitsActions;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Password;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.auth')]
class ForgotPassword extends Component
{
    use RateLimitsActions;

    public string $email = '';

    /**
     * Send a password reset link to the provided email address.
     */
    public function sendPasswordResetLink(): void
    {
        $this->validate([
            'email' => ['required', 'string', 'email'],
        ]);

        // Stops the form from being used to flood inboxes through the app's mail account.
        $this->rateLimit('password-reset:'.request()->ip(), 5, 60, 'email');

        Password::sendResetLink($this->only('email'));

        // The same message is shown either way so the form can't be used to discover accounts.
        session()->flash('status', __('A reset link will be sent if the account exists.'));
    }

    public function render(): View
    {
        return view('livewire.auth.forgot-password')->title(__('Forgot password'));
    }
}
