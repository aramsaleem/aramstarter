<?php

namespace App\Livewire\Admin\Users;

use App\Enums\ActivityEvent;
use App\Livewire\Concerns\InteractsWithAlerts;
use App\Livewire\Forms\UserForm;
use App\Models\User;
use App\Support\Audit;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.admin')]
class Create extends Component
{
    use InteractsWithAlerts;

    public UserForm $form;

    public function mount(): void
    {
        $this->authorize('create', User::class);
    }

    public function save(): void
    {
        $this->authorize('create', User::class);

        $user = $this->form->store();

        Audit::log(ActivityEvent::UserCreated, $user, ['roles' => $user->getRoleNames()->all()]);

        $this->flashToast(__('User :name created.', ['name' => $user->name]));

        $this->redirectRoute('admin.users.index', navigate: true);
    }

    public function render(): View
    {
        return view('livewire.admin.users.create', [
            'assignableRoles' => $this->form->assignableRoles(),
        ])->title(__('Create user'));
    }
}
