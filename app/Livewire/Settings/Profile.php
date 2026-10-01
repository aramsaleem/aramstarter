<?php

namespace App\Livewire\Settings;

use App\Enums\ActivityEvent;
use App\Livewire\Concerns\InteractsWithAlerts;
use App\Livewire\Concerns\RateLimitsActions;
use App\Models\User;
use App\Support\Audit;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Component;

class Profile extends Component
{
    use InteractsWithAlerts, RateLimitsActions;

    public string $name = '';

    public string $email = '';

    public function mount(): void
    {
        $this->name = Auth::user()->name;
        $this->email = Auth::user()->email;
    }

    /**
     * Update the profile information for the currently authenticated user.
     */
    public function updateProfileInformation(): void
    {
        /** @var User $user */
        $user = Auth::user();

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)->ignore($user->id)],
        ]);

        $user->fill($validated);

        $emailChanged = $user->isDirty('email');
        $changed = array_keys($user->getDirty());

        if ($emailChanged) {
            $user->email_verified_at = null;
        }

        $user->save();

        if ($changed !== []) {
            Audit::log(ActivityEvent::ProfileUpdated, $user, ['changed' => $changed]);
        }

        if ($emailChanged) {
            $user->sendEmailVerificationNotification();
        }

        $this->toast(__('Profile updated.'));
    }

    /**
     * Send an email verification notification to the current user.
     */
    public function resendVerificationNotification(): void
    {
        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('dashboard', absolute: false));

            return;
        }

        $this->rateLimit('verification-email:'.$user->id, 3, 300, 'email');

        $user->sendEmailVerificationNotification();

        $this->toast(__('A new verification link has been sent to your email address.'));
    }

    public function render(): View
    {
        return view('livewire.settings.profile')->title(__('Profile'));
    }
}
