<?php

namespace App\Livewire\Forms;

use App\Enums\SystemRole;
use App\Models\User;
use App\Support\Localization;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Livewire\Form;
use Spatie\Permission\Models\Role;

/**
 * Only plain values live in this form: the user being edited stays on the component
 * in a #[Locked] property, so the browser can never swap it out.
 */
class UserForm extends Form
{
    public string $name = '';

    public string $email = '';

    public string $password = '';

    public string $password_confirmation = '';

    /** @var list<string> */
    public array $roles = [];

    public string $locale = '';

    public bool $verified = true;

    public function setUser(User $user): void
    {
        $this->name = $user->name;
        $this->email = $user->email;
        $this->roles = $user->roles->pluck('name')->all();
        $this->locale = $user->locale ?? '';
        $this->verified = $user->hasVerifiedEmail();
    }

    /**
     * Roles the signed-in admin may hand out: every role for Super Admins, otherwise only
     * roles whose permissions they hold themselves - never Super Admin.
     *
     * @return list<string>
     */
    public function assignableRoles(): array
    {
        $actor = Auth::user();

        return Role::with('permissions')
            ->orderBy('name')
            ->get()
            ->filter(fn (Role $role) => $actor->isSuperAdmin() || (
                $role->name !== SystemRole::SuperAdmin->value
                && $actor->canGrantPermissions($role->permissions->pluck('name'))
            ))
            ->pluck('name')
            ->values()
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        $editing = $this->editing();

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)->ignore($editing?->id)],
            'password' => [$editing ? 'nullable' : 'required', 'string', 'confirmed', Password::defaults()],
            'roles' => ['array'],
            'roles.*' => ['string', 'distinct', Rule::in($this->assignableRoles())],
            'locale' => ['nullable', 'string', Rule::in(Localization::codes())],
            'verified' => ['boolean'],
        ];
    }

    public function store(): User
    {
        $validated = $this->validate();

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'locale' => $validated['locale'] ?: null,
        ]);

        if ($this->verified) {
            $user->markEmailAsVerified();
        }

        $user->syncRoles($validated['roles']);

        return $user;
    }

    /**
     * @return list<string> The names of the fields that changed (never their values).
     */
    public function update(User $user): array
    {
        $validated = $this->validate();

        if ($user->isSuperAdmin()
            && ! in_array(SystemRole::SuperAdmin->value, $validated['roles'], true)
            && User::role(SystemRole::SuperAdmin)->count() === 1) {
            throw ValidationException::withMessages([
                $this->getPropertyName().'.roles' => __('This is the only super admin. Make another user a super admin first.'),
            ]);
        }

        $user->fill([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'locale' => $validated['locale'] ?: null,
        ]);

        if (filled($validated['password'])) {
            $user->password = $validated['password'];
            // Sign the user out of "remember me" sessions on other devices.
            $user->setRememberToken(Str::random(60));
        }

        $user->email_verified_at = $this->verified ? ($user->email_verified_at ?? now()) : null;

        $changed = array_values(array_intersect(
            ['name', 'email', 'locale', 'password', 'email_verified_at'],
            array_keys($user->getDirty()),
        ));

        $user->save();

        $rolesBefore = $user->roles()->pluck('name')->sort()->values()->all();
        $user->syncRoles($validated['roles']);

        if ($rolesBefore !== collect($validated['roles'])->sort()->values()->all()) {
            $changed[] = 'roles';
        }

        $this->reset('password', 'password_confirmation');

        return $changed;
    }

    /**
     * The user being edited, taken from the component's locked property.
     */
    private function editing(): ?User
    {
        $component = $this->getComponent();

        return property_exists($component, 'user') && $component->user instanceof User ? $component->user : null;
    }
}
