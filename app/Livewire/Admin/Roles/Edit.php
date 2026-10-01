<?php

namespace App\Livewire\Admin\Roles;

use App\Enums\ActivityEvent;
use App\Livewire\Concerns\InteractsWithAlerts;
use App\Livewire\Concerns\SelectsPermissions;
use App\Livewire\Forms\RoleForm;
use App\Support\Audit;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Spatie\Permission\Models\Role;

#[Layout('components.layouts.admin')]
class Edit extends Component
{
    use InteractsWithAlerts, SelectsPermissions;

    #[Locked]
    public Role $role;

    public RoleForm $form;

    public function mount(Role $role): void
    {
        $this->authorize('update', $role);

        $this->role = $role;
        $this->form->setRole($role);
    }

    public function save(): void
    {
        $this->authorize('update', $this->role);

        $this->form->update($this->role);

        Audit::log(ActivityEvent::RoleUpdated, $this->role, ['permissions' => $this->role->permissions()->count()]);

        $this->toast(__('Role updated.'));
    }

    public function render(): View
    {
        return view('livewire.admin.roles.edit')->title(__('Edit role'));
    }
}
