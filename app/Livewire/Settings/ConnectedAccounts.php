<?php

namespace App\Livewire\Settings;

use App\Enums\ActivityEvent;
use App\Enums\SocialProvider;
use App\Livewire\Concerns\InteractsWithAlerts;
use App\Models\SocialAccount;
use App\Models\User;
use App\Support\Audit;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;

class ConnectedAccounts extends Component
{
    use InteractsWithAlerts;

    /**
     * @return Collection<string, SocialAccount>
     */
    #[Computed]
    public function accounts(): Collection
    {
        return Auth::user()->socialAccounts()->get()->keyBy(fn (SocialAccount $account) => $account->provider->value);
    }

    /**
     * Providers users can connect, plus any that were connected before being switched off.
     *
     * @return list<SocialProvider>
     */
    #[Computed]
    public function providers(): array
    {
        return array_values(array_filter(
            SocialProvider::cases(),
            fn (SocialProvider $provider) => $provider->isEnabled() || $this->accounts->has($provider->value),
        ));
    }

    public function confirmDisconnect(string $provider): void
    {
        $provider = SocialProvider::from($provider);

        $this->askForConfirmation(
            __('Disconnect :provider?', ['provider' => $provider->label()]),
            __('You will no longer be able to log in with this :provider account.', ['provider' => $provider->label()]),
            'disconnect',
            ['provider' => $provider->value],
            __('Disconnect'),
        );
    }

    /**
     * @param  array{provider?: string}  $data
     */
    public function disconnect(array $data): void
    {
        /** @var User $user */
        $user = Auth::user();
        $provider = SocialProvider::tryFrom($data['provider'] ?? '');
        $account = $provider ? $this->accounts->get($provider->value) : null;

        if (! $account) {
            return;
        }

        if (! $user->hasPassword() && $this->accounts->count() === 1) {
            $this->toast(__('Set a password before disconnecting your only way to log in.'), 'error');

            return;
        }

        $account->delete();
        unset($this->accounts);

        Audit::log(ActivityEvent::SocialDisconnected, $user, ['provider' => $provider->value]);

        $this->toast(__(':provider disconnected.', ['provider' => $provider->label()]));
    }

    public function render(): View
    {
        return view('livewire.settings.connected-accounts')->title(__('Connected accounts'));
    }
}
