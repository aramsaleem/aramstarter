<?php

namespace App\Livewire\Admin\Users;

use App\Enums\ActivityEvent;
use App\Livewire\Concerns\ImpersonatesUsers;
use App\Livewire\Concerns\InteractsWithAlerts;
use App\Models\User;
use App\Support\Audit;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;

#[Layout('components.layouts.admin')]
class Index extends Component
{
    use ImpersonatesUsers, InteractsWithAlerts, WithPagination;

    private const SORTABLE = ['name', 'email', 'created_at'];

    #[Url(except: '')]
    public string $search = '';

    #[Url(except: '')]
    public string $role = '';

    /** One of: verified, unverified, two-factor */
    #[Url(except: '')]
    public string $status = '';

    #[Url(except: 'created_at')]
    public string $sortBy = 'created_at';

    #[Url(except: 'desc')]
    public string $sortDirection = 'desc';

    public function mount(): void
    {
        $this->authorize('viewAny', User::class);
    }

    public function updated(string $property): void
    {
        if (in_array($property, ['search', 'role', 'status'], true)) {
            $this->resetPage();
        }
    }

    public function sort(string $column): void
    {
        if (! in_array($column, self::SORTABLE, true)) {
            return;
        }

        $this->sortDirection = $this->sortBy === $column && $this->sortDirection === 'asc' ? 'desc' : 'asc';
        $this->sortBy = $column;
    }

    public function resetFilters(): void
    {
        $this->reset('search', 'role', 'status');
        $this->resetPage();
    }

    /**
     * @return LengthAwarePaginator<int, User>
     */
    #[Computed]
    public function users(): LengthAwarePaginator
    {
        return User::query()
            // Permissions are loaded up front: the row actions check them through UserPolicy.
            ->with(['roles.permissions', 'permissions'])
            ->when($this->search !== '', fn (Builder $query) => $query->where(fn (Builder $query) => $query
                ->where('name', 'like', "%{$this->search}%")
                ->orWhere('email', 'like', "%{$this->search}%")))
            ->when($this->role !== '', fn (Builder $query) => $query->whereHas('roles', fn (Builder $query) => $query->where('name', $this->role)))
            ->when($this->status === 'verified', fn (Builder $query) => $query->whereNotNull('email_verified_at'))
            ->when($this->status === 'unverified', fn (Builder $query) => $query->whereNull('email_verified_at'))
            ->when($this->status === 'two-factor', fn (Builder $query) => $query->whereNotNull('two_factor_confirmed_at'))
            ->orderBy(
                in_array($this->sortBy, self::SORTABLE, true) ? $this->sortBy : 'created_at',
                $this->sortDirection === 'asc' ? 'asc' : 'desc',
            )
            ->paginate(10);
    }

    /**
     * @return Collection<int, string>
     */
    #[Computed]
    public function roles(): Collection
    {
        return Role::orderBy('name')->pluck('name');
    }

    public function confirmDelete(int $userId): void
    {
        $user = User::findOrFail($userId);

        $this->authorize('delete', $user);

        $this->askForConfirmation(
            __('Delete :name?', ['name' => $user->name]),
            __('The account and all of its data will be permanently deleted.'),
            'delete',
            ['user' => $user->id],
            __('Delete'),
        );
    }

    /**
     * @param  array{user?: int}  $data
     */
    public function delete(array $data): void
    {
        $user = User::find($data['user'] ?? null);

        if (! $user) {
            return;
        }

        $this->authorize('delete', $user);

        Audit::log(ActivityEvent::UserDeleted, $user);

        $user->delete();

        $this->toast(__('User deleted.'));
    }

    public function render(): View
    {
        return view('livewire.admin.users.index')->title(__('Users'));
    }
}
