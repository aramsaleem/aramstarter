<?php

namespace App\Livewire\Concerns;

use App\Support\PermissionLabel;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Spatie\Permission\Models\Permission;

/**
 * Permission checkboxes grouped by prefix ("users.view" belongs to "users"),
 * shared by the create and edit role screens. Expects a RoleForm in $form.
 */
trait SelectsPermissions
{
    /**
     * Only permissions the signed-in admin may grant are offered.
     *
     * @return Collection<string, Collection<int, string>>
     */
    #[Computed]
    public function permissionGroups(): Collection
    {
        return Permission::orderBy('name')
            ->whereIn('name', $this->form->grantablePermissions())
            ->pluck('name')
            ->groupBy(fn (string $name) => PermissionLabel::group($name));
    }

    /**
     * Select every permission in the group, or clear them all if they already are.
     */
    public function toggleGroup(string $group): void
    {
        $permissions = $this->permissionGroups->get($group, collect())->all();
        $selected = $this->form->permissions;

        $this->form->permissions = array_diff($permissions, $selected) === []
            ? array_values(array_diff($selected, $permissions))
            : array_values(array_unique([...$selected, ...$permissions]));
    }
}
