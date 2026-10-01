<?php

namespace App\Livewire\Auth;

use App\Enums\SystemRole;
use App\Livewire\Concerns\RateLimitsActions;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.auth')]
class Register extends Component
{
    use RateLimitsActions;

    public string $name = '';

    public string $email = '';

    public string $password = '';

    public string $password_confirmation = '';

    /**
     * Handle an incoming registration request.
     */
    public function register(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)],
            'password' => ['required', 'string', 'confirmed', Password::defaults()],
        ]);

        $this->rateLimit('register:'.request()->ip(), 5, 600, 'email');

        $user = User::create([
            ...$validated,
            'locale' => app()->getLocale(),
        ]);

        $user->assignRole(SystemRole::User->role());

        event(new Registered($user));

        Auth::login($user);
        Session::regenerate();

        $this->redirect(route('dashboard', absolute: false), navigate: true);
    }

    public function render(): View
    {
        return view('livewire.auth.register')->title(__('Register'));
    }
}
