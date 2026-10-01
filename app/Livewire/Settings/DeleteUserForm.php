<?php

namespace App\Livewire\Settings;

use App\Enums\ActivityEvent;
use App\Enums\SystemRole;
use App\Livewire\Actions\Logout;
use App\Models\User;
use App\Support\Audit;
use App\Support\Impersonation;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class DeleteUserForm extends Component
{
    public string $password = '';

    public bool $confirmingDeletion = false;

    public function confirmUserDeletion(): void
    {
        $this->resetErrorBag();
        $this->reset('password');

        $this->confirmingDeletion = true;
    }

    /**
     * Delete the currently authenticated user.
     */
    public function deleteUser(Logout $logout): void
    {
        /** @var User $user */
        $user = Auth::user();

        // An admin signed in as this user can't delete their account.
        abort_if(Impersonation::active(), 403);

        $this->validate([
            'password' => ['required', 'string', 'current_password'],
        ]);

        if ($user->isSuperAdmin() && User::role(SystemRole::SuperAdmin)->count() === 1) {
            $this->addError('password', __('You are the only super admin. Make another user a super admin before deleting your account.'));

            return;
        }

        Audit::log(ActivityEvent::AccountDeleted, $user);

        $logout();
        $user->delete();

        $this->redirect('/', navigate: true);
    }

    public function render(): View
    {
        return view('livewire.settings.delete-user-form');
    }
}
