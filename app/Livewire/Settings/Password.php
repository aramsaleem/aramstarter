<?php

namespace App\Livewire\Settings;

use App\Enums\ActivityEvent;
use App\Livewire\Concerns\InteractsWithAlerts;
use App\Models\User;
use App\Support\Audit;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class Password extends Component
{
    use InteractsWithAlerts;

    public string $current_password = '';

    public string $password = '';

    public string $password_confirmation = '';

    /**
     * Update (or, for social login accounts, set) the user's password.
     */
    public function updatePassword(): void
    {
        /** @var User $user */
        $user = Auth::user();
        $hadPassword = $user->hasPassword();

        try {
            $validated = $this->validate([
                'current_password' => $hadPassword ? ['required', 'string', 'current_password'] : ['nullable'],
                'password' => ['required', 'string', PasswordRule::defaults(), 'confirmed'],
            ]);
        } catch (ValidationException $e) {
            $this->reset('current_password', 'password', 'password_confirmation');

            throw $e;
        }

        $user->update(['password' => $validated['password']]);

        Audit::log(ActivityEvent::PasswordChanged, $user, ['first_password' => ! $hadPassword]);

        $this->reset('current_password', 'password', 'password_confirmation');

        $this->toast($hadPassword ? __('Password updated.') : __('Password set. You can now log in with your email address.'));
    }

    public function render(): View
    {
        return view('livewire.settings.password')->title(__('Password'));
    }
}
