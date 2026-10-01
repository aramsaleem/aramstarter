<?php

namespace App\Livewire\Admin\Roles;

use App\Enums\ActivityEvent;
use App\Livewire\Concerns\InteractsWithAlerts;
use App\Livewire\Concerns\SelectsPermissions;
use App\Livewire\Forms\RoleForm;
use App\Support\Audit;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Spatie\Permission\Models\Role;

#[Layout('components.layouts.admin')]
class Create extends Component
{
    use InteractsWithAlerts, SelectsPermissions;

    public RoleForm $form;

    public function mount(): void
    {
        $this->authorize('create', Role::class);
    }

    public function save(): void
    {
        $this->authorize('create', Role::class);

        $role = $this->form->store();

        Audit::log(ActivityEvent::RoleCreated, $role, ['permissions' => $role->permissions()->count()]);

        $this->flashToast(__('Role :role created.', ['role' => $role->name]));

        $this->redirectRoute('admin.roles.index', navigate: true);
    }

    public function render(): View
    {
        return view('livewire.admin.roles.create')->title(__('Create role'));
    }
}
