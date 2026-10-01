<?php

namespace App\Livewire\Forms;

use App\Enums\SystemRole;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Form;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * Only plain values live in this form: the role being edited stays on the component
 * in a #[Locked] property, so the browser can never swap it out.
 */
class RoleForm extends Form
{
    public string $name = '';

    /** @var list<string> */
    public array $permissions = [];

    public function setRole(Role $role): void
    {
        $this->name = $role->name;
        $this->permissions = $role->permissions->pluck('name')->all();
    }

    /**
     * Permissions the signed-in admin may put into a role: all of them for Super Admins,
     * otherwise only the ones they hold themselves.
     *
     * @return list<string>
     */
    public function grantablePermissions(): array
    {
        $actor = Auth::user();

        return $actor->isSuperAdmin()
            ? Permission::pluck('name')->all()
            : $actor->getAllPermissions()->pluck('name')->values()->all();
    }

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        return [
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique(config('permission.table_names.roles'), 'name')
                    ->where('guard_name', 'web')
                    ->ignore($this->editing()?->id),
            ],
            'permissions' => ['array'],
            'permissions.*' => ['string', 'distinct', Rule::in($this->grantablePermissions())],
        ];
    }

    public function store(): Role
    {
        $validated = $this->validate();

        $role = Role::create(['name' => $validated['name'], 'guard_name' => 'web']);
        $role->syncPermissions($validated['permissions']);

        return $role;
    }

    public function update(Role $role): void
    {
        $validated = $this->validate();

        if (SystemRole::isProtectedName($role->name) && $validated['name'] !== $role->name) {
            throw ValidationException::withMessages([
                $this->getPropertyName().'.name' => __('This role is used by the application and cannot be renamed.'),
            ]);
        }

        $role->update(['name' => $validated['name']]);
        $role->syncPermissions($validated['permissions']);
    }

    /**
     * The role being edited, taken from the component's locked property.
     */
    private function editing(): ?Role
    {
        $component = $this->getComponent();

        return property_exists($component, 'role') && $component->role instanceof Role ? $component->role : null;
    }
}
