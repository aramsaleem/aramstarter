<?php

namespace App\Livewire\Admin\Roles;

use App\Enums\ActivityEvent;
use App\Livewire\Concerns\InteractsWithAlerts;
use App\Support\Audit;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Spatie\Permission\Models\Role;

#[Layout('components.layouts.admin')]
class Index extends Component
{
    use InteractsWithAlerts;

    public function mount(): void
    {
        $this->authorize('viewAny', Role::class);
    }

    /**
     * @return Collection<int, Role>
     */
    #[Computed]
    public function roles(): Collection
    {
        return Role::with('permissions')->withCount(['users', 'permissions'])->orderBy('name')->get();
    }

    public function confirmDelete(int $roleId): void
    {
        $role = Role::findOrFail($roleId);

        $this->authorize('delete', $role);

        $this->askForConfirmation(
            __('Delete the :role role?', ['role' => $role->name]),
            __('Users with this role will lose the permissions it grants.'),
            'delete',
            ['role' => $role->id],
            __('Delete'),
        );
    }

    /**
     * @param  array{role?: int}  $data
     */
    public function delete(array $data): void
    {
        $role = Role::find($data['role'] ?? null);

        if (! $role) {
            return;
        }

        $this->authorize('delete', $role);

        Audit::log(ActivityEvent::RoleDeleted, $role);

        $role->delete();
        unset($this->roles);

        $this->toast(__('Role deleted.'));
    }

    public function render(): View
    {
        return view('livewire.admin.roles.index')->title(__('Roles'));
    }
}
