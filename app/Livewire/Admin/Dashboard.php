<?php

namespace App\Livewire\Admin;

use App\Enums\ActivityEvent;
use App\Enums\SystemPermission;
use App\Enums\SystemRole;
use App\Livewire\Concerns\ImpersonatesUsers;
use App\Livewire\Concerns\InteractsWithAlerts;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Spatie\Permission\Models\Role;

#[Layout('components.layouts.admin')]
class Dashboard extends Component
{
    use ImpersonatesUsers, InteractsWithAlerts;

    /**
     * Periods in days, with the number of days per chart column.
     */
    public const PERIODS = [7 => 1, 30 => 1, 90 => 7];

    #[Url(except: '30')]
    public string $period = '30';

    #[Url(except: '')]
    public string $search = '';

    #[Url(except: '')]
    public string $role = '';

    public ?int $selectedUserId = null;

    public function selectUser(int $userId): void
    {
        $this->selectedUserId = $userId;
    }

    public function days(): int
    {
        return array_key_exists((int) $this->period, self::PERIODS) ? (int) $this->period : 30;
    }

    /**
     * Headline numbers for the selected period, compared with the period before it.
     *
     * @return array<string, int|null>
     */
    #[Computed]
    public function stats(): array
    {
        $days = $this->days();
        $start = now()->subDays($days);
        $previousStart = now()->subDays($days * 2);

        // The current period has no upper bound, so records from this very second are included.
        $count = fn (Builder $query, Carbon $from, ?Carbon $to = null) => (clone $query)
            ->where('created_at', '>=', $from)
            ->when($to, fn (Builder $query) => $query->where('created_at', '<', $to))
            ->count();

        $users = User::query();
        $signIns = ActivityLog::query()->where('event', ActivityEvent::Login);
        $threats = ActivityLog::query()->whereIn('event', ActivityEvent::threats());

        return [
            'users' => User::count(),
            'verified' => User::whereNotNull('email_verified_at')->count(),
            'two_factor' => User::whereNotNull('two_factor_confirmed_at')->count(),
            'new' => $count($users, $start),
            'new_previous' => $count($users, $previousStart, $start),
            'sign_ins' => $count($signIns, $start),
            'sign_ins_previous' => $count($signIns, $previousStart, $start),
            'threats' => $count($threats, $start),
            'threats_previous' => $count($threats, $previousStart, $start),
            'threats_today' => (clone $threats)->where('created_at', '>=', now()->subDay())->count(),
            'online' => $this->onlineUsers(),
        ];
    }

    /**
     * Sign-ups, sign-ins and threats per chart column across the selected period.
     *
     * @return list<array{from: Carbon, to: Carbon, signups: int, sign_ins: int, threats: int}>
     */
    #[Computed]
    public function series(): array
    {
        $days = $this->days();
        $size = self::PERIODS[$days];
        $start = now()->subDays($days - 1)->startOfDay();

        $perDay = fn (Builder $query) => $query
            ->where('created_at', '>=', $start)
            ->selectRaw('DATE(created_at) as day, COUNT(*) as total')
            ->groupBy('day')
            ->pluck('total', 'day');

        $signups = $perDay(User::query());
        $signIns = $perDay(ActivityLog::query()->where('event', ActivityEvent::Login));
        $threats = $perDay(ActivityLog::query()->whereIn('event', ActivityEvent::threats()));

        $columns = [];

        for ($offset = 0; $offset < $days; $offset += $size) {
            $from = $start->copy()->addDays($offset);
            $to = $from->copy()->addDays(min($size, $days - $offset) - 1);
            $column = ['from' => $from, 'to' => $to, 'signups' => 0, 'sign_ins' => 0, 'threats' => 0];

            for ($date = $from->copy(); $date->lte($to); $date->addDay()) {
                $key = $date->toDateString();
                $column['signups'] += (int) ($signups[$key] ?? 0);
                $column['sign_ins'] += (int) ($signIns[$key] ?? 0);
                $column['threats'] += (int) ($threats[$key] ?? 0);
            }

            $columns[] = $column;
        }

        return $columns;
    }

    /**
     * @return Collection<int, Role>
     */
    #[Computed]
    public function roles(): Collection
    {
        return Role::withCount('users')->orderByDesc('users_count')->orderBy('name')->get();
    }

    /**
     * The people list, filtered by the search box and role menu.
     *
     * @return Collection<int, User>
     */
    #[Computed]
    public function people(): Collection
    {
        if (! Gate::allows(SystemPermission::ViewUsers->value)) {
            return new Collection;
        }

        $search = trim($this->search);

        return User::query()
            ->with('roles')
            ->when($search !== '', fn (Builder $query) => $query->where(fn (Builder $query) => $query
                ->where('name', 'like', '%'.addcslashes($search, '%_\\').'%')
                ->orWhere('email', 'like', '%'.addcslashes($search, '%_\\').'%')))
            ->when($this->role !== '', fn (Builder $query) => $query->whereHas('roles', fn (Builder $query) => $query->where('name', $this->role)))
            ->latest()
            ->limit(8)
            ->get();
    }

    /**
     * The user shown in the detail panel: the one picked from the list, or the first one.
     */
    #[Computed]
    public function selectedUser(): ?User
    {
        if (! Gate::allows(SystemPermission::ViewUsers->value)) {
            return null;
        }

        $id = $this->selectedUserId && $this->people->contains('id', $this->selectedUserId)
            ? $this->selectedUserId
            : $this->people->first()?->id;

        return $id ? User::with('roles', 'socialAccounts')->find($id) : null;
    }

    /**
     * @return Collection<int, ActivityLog>
     */
    #[Computed]
    public function selectedUserActivity(): Collection
    {
        if (! $this->selectedUser || ! Gate::allows(SystemPermission::ViewActivity->value)) {
            return new Collection;
        }

        return ActivityLog::query()
            ->where('user_id', $this->selectedUser->id)
            ->latest('id')
            ->limit(5)
            ->get();
    }

    #[Computed]
    public function selectedUserLastSignIn(): ?Carbon
    {
        if (! $this->selectedUser) {
            return null;
        }

        return ActivityLog::query()
            ->where('user_id', $this->selectedUser->id)
            ->where('event', ActivityEvent::Login)
            ->latest('id')
            ->value('created_at');
    }

    /**
     * @return Collection<int, ActivityLog>
     */
    #[Computed]
    public function activity(): Collection
    {
        if (! Gate::allows(SystemPermission::ViewActivity->value)) {
            return new Collection;
        }

        return ActivityLog::with('user')->latest('id')->limit(7)->get();
    }

    /**
     * Security checklist for the whole installation.
     *
     * @return list<array{label: string, detail: string, ok: bool}>
     */
    #[Computed]
    public function health(): array
    {
        $admins = User::role([SystemRole::SuperAdmin->value, SystemRole::Admin->value]);
        $adminCount = (clone $admins)->count();
        $adminsWithoutTwoFactor = (clone $admins)->whereNull('two_factor_confirmed_at')->count();
        $superAdmins = User::role(SystemRole::SuperAdmin->value)->count();
        $mailer = (string) config('mail.default');

        return [
            [
                'label' => __('Debug mode is off'),
                'detail' => config('app.debug') ? __('Set APP_DEBUG=false in production.') : __('Errors are hidden from visitors.'),
                'ok' => ! config('app.debug'),
            ],
            [
                'label' => __('Served over HTTPS'),
                'detail' => str_starts_with((string) config('app.url'), 'https://') ? __('APP_URL uses https.') : __('Set APP_URL to an https:// address.'),
                'ok' => str_starts_with((string) config('app.url'), 'https://'),
            ],
            [
                'label' => __('Admins use two-factor'),
                'detail' => $adminsWithoutTwoFactor === 0
                    ? __('Every admin has it enabled.')
                    : __('Admins without two-factor: :count', ['count' => $adminsWithoutTwoFactor]),
                'ok' => $adminCount > 0 && $adminsWithoutTwoFactor === 0,
            ],
            [
                'label' => __('A backup super admin'),
                'detail' => $superAdmins > 1 ? __(':count super admins.', ['count' => $superAdmins]) : __('Add a second super admin so you can never be locked out.'),
                'ok' => $superAdmins > 1,
            ],
            [
                'label' => __('Email is delivered'),
                'detail' => in_array($mailer, ['log', 'array'], true) ? __('The ":mailer" mailer does not send real email.', ['mailer' => $mailer]) : __('Using the ":mailer" mailer.', ['mailer' => $mailer]),
                'ok' => ! in_array($mailer, ['log', 'array'], true),
            ],
        ];
    }

    /**
     * Signed-in users active in the last five minutes (database sessions only).
     */
    protected function onlineUsers(): ?int
    {
        if (config('session.driver') !== 'database') {
            return null;
        }

        return DB::table(config('session.table', 'sessions'))
            ->whereNotNull('user_id')
            ->where('last_activity', '>=', now()->subMinutes(5)->getTimestamp())
            ->distinct()
            ->count('user_id');
    }

    public function render(): View
    {
        return view('livewire.admin.dashboard')->title(__('Admin dashboard'));
    }
}
