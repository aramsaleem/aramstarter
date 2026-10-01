<?php

namespace App\Livewire\Admin\Users;

use App\Enums\ActivityEvent;
use App\Livewire\Concerns\ImpersonatesUsers;
use App\Livewire\Concerns\InteractsWithAlerts;
use App\Livewire\Forms\UserForm;
use App\Models\User;
use App\Support\Audit;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;

#[Layout('components.layouts.admin')]
class Edit extends Component
{
    use ImpersonatesUsers, InteractsWithAlerts;

    #[Locked]
    public User $user;

    public UserForm $form;

    public function mount(User $user): void
    {
        $this->authorize('update', $user);

        $this->user = $user->load('roles', 'socialAccounts');
        $this->form->setUser($user);
    }

    public function save(): void
    {
        $this->authorize('update', $this->user);

        $changed = $this->form->update($this->user);

        if ($changed !== []) {
            Audit::log(ActivityEvent::UserUpdated, $this->user, ['changed' => $changed]);
        }

        $this->toast(__('User updated.'));
    }

    public function confirmResetTwoFactor(): void
    {
        $this->authorize('update', $this->user);

        $this->askForConfirmation(
            __('Reset two-factor authentication?'),
            __(':name will be able to log in with only their password and can set up two-factor authentication again.', ['name' => $this->user->name]),
            'resetTwoFactor',
            confirmButtonText: __('Reset'),
        );
    }

    /**
     * Helps users who lost their authenticator device and their recovery codes.
     */
    public function resetTwoFactor(): void
    {
        $this->authorize('update', $this->user);

        $this->user->disableTwoFactorAuthentication();

        Audit::log(ActivityEvent::UserTwoFactorReset, $this->user);

        $this->toast(__('Two-factor authentication has been reset.'));
    }

    public function confirmDelete(): void
    {
        $this->authorize('delete', $this->user);

        $this->askForConfirmation(
            __('Delete :name?', ['name' => $this->user->name]),
            __('The account and all of its data will be permanently deleted.'),
            'delete',
            confirmButtonText: __('Delete'),
        );
    }

    public function delete(): void
    {
        $this->authorize('delete', $this->user);

        Audit::log(ActivityEvent::UserDeleted, $this->user);

        $this->user->delete();

        $this->flashToast(__('User deleted.'));

        $this->redirectRoute('admin.users.index', navigate: true);
    }

    public function render(): View
    {
        return view('livewire.admin.users.edit', [
            'assignableRoles' => $this->form->assignableRoles(),
        ])->title(__('Edit user'));
    }
}
