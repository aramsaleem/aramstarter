<?php

namespace App\Livewire\Admin\Permissions;

use App\Enums\ActivityEvent;
use App\Livewire\Concerns\InteractsWithAlerts;
use App\Support\Audit;
use App\Support\PermissionLabel;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Url;
use Livewire\Component;
use Spatie\Permission\Models\Permission;

#[Layout('components.layouts.admin')]
class Index extends Component
{
    use InteractsWithAlerts;

    #[Url(except: '')]
    public string $search = '';

    public bool $showModal = false;

    #[Locked]
    public ?int $editingId = null;

    public string $name = '';

    public function mount(): void
    {
        $this->authorize('viewAny', Permission::class);
    }

    /**
     * @return Collection<string, \Illuminate\Database\Eloquent\Collection<int, Permission>>
     */
    #[Computed]
    public function groups(): Collection
    {
        return Permission::query()
            ->with('roles')
            ->when($this->search !== '', fn (Builder $query) => $query->where('name', 'like', "%{$this->search}%"))
            ->orderBy('name')
            ->get()
            ->groupBy(fn (Permission $permission) => PermissionLabel::group($permission->name));
    }

    public function create(): void
    {
        $this->authorize('create', Permission::class);

        $this->reset('name', 'editingId');
        $this->resetErrorBag();

        $this->showModal = true;
    }

    public function edit(int $permissionId): void
    {
        $permission = Permission::findOrFail($permissionId);

        $this->authorize('update', $permission);

        $this->editingId = $permission->id;
        $this->name = $permission->name;
        $this->resetErrorBag();

        $this->showModal = true;
    }

    public function save(): void
    {
        $permission = $this->editingId ? Permission::findOrFail($this->editingId) : null;

        $this->authorize($permission ? 'update' : 'create', $permission ?? Permission::class);

        $validated = $this->validate([
            'name' => [
                'required', 'string', 'max:255',
                'regex:/^[a-z0-9_-]+(\.[a-z0-9_-]+)*$/',
                Rule::unique(config('permission.table_names.permissions'), 'name')
                    ->where('guard_name', 'web')
                    ->ignore($this->editingId),
            ],
        ], [
            'name.regex' => __('Use lowercase letters, numbers, dashes and dots, e.g. posts.publish'),
        ]);

        if ($permission) {
            $previous = $permission->name;
            $permission->update(['name' => $validated['name']]);

            Audit::log(ActivityEvent::PermissionUpdated, $permission, ['previous' => $previous]);
        } else {
            Audit::log(ActivityEvent::PermissionCreated, Permission::create(['name' => $validated['name'], 'guard_name' => 'web']));
        }

        $this->showModal = false;
        $this->reset('name', 'editingId');
        unset($this->groups);

        $this->toast($permission ? __('Permission updated.') : __('Permission created.'));
    }

    public function confirmDelete(int $permissionId): void
    {
        $permission = Permission::findOrFail($permissionId);

        $this->authorize('delete', $permission);

        $this->askForConfirmation(
            __('Delete the :permission permission?', ['permission' => $permission->name]),
            __('It will be removed from every role that has it.'),
            'delete',
            ['permission' => $permission->id],
            __('Delete'),
        );
    }

    /**
     * @param  array{permission?: int}  $data
     */
    public function delete(array $data): void
    {
        $permission = Permission::find($data['permission'] ?? null);

        if (! $permission) {
            return;
        }

        $this->authorize('delete', $permission);

        Audit::log(ActivityEvent::PermissionDeleted, $permission);

        $permission->delete();
        unset($this->groups);

        $this->toast(__('Permission deleted.'));
    }

    public function render(): View
    {
        return view('livewire.admin.permissions.index')->title(__('Permissions'));
    }
}
