<?php

namespace App\Livewire\Admin\Activity;

use App\Enums\ActivityEvent;
use App\Enums\SystemPermission;
use App\Models\ActivityLog;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * The security audit trail: sign-ins, failures, account and admin changes.
 */
#[Layout('components.layouts.admin')]
class Index extends Component
{
    use WithPagination;

    public const PERIODS = ['1', '7', '30', '90', 'all'];

    #[Url(except: '')]
    public string $search = '';

    /**
     * A group ("auth", "admin", ...), "threats", or empty for everything.
     */
    #[Url(except: '')]
    public string $group = '';

    #[Url(except: '')]
    public string $event = '';

    #[Url(except: '30')]
    public string $period = '30';

    public function mount(): void
    {
        $this->authorizeView();
    }

    public function updated(string $property): void
    {
        if (in_array($property, ['search', 'group', 'event', 'period'], true)) {
            $this->resetPage();
        }
    }

    public function setGroup(string $group): void
    {
        $this->group = $group === 'threats' || array_key_exists($group, ActivityEvent::groups()) ? $group : '';
        $this->event = '';
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->reset('search', 'group', 'event', 'period');
        $this->resetPage();
    }

    /**
     * @return LengthAwarePaginator<int, ActivityLog>
     */
    #[Computed]
    public function logs(): LengthAwarePaginator
    {
        $this->authorizeView();

        return $this->query()
            ->with('user')
            ->latest('id')
            ->paginate(20);
    }

    /**
     * Counters for the selected period, independent of the other filters.
     *
     * @return array{total: int, signins: int, threats: int, admin: int, ips: int}
     */
    #[Computed]
    public function summary(): array
    {
        $base = ActivityLog::query()->when($this->since(), fn (Builder $query, $since) => $query->where('created_at', '>=', $since));

        return [
            'total' => (clone $base)->count(),
            'signins' => (clone $base)->where('event', ActivityEvent::Login)->count(),
            'threats' => (clone $base)->whereIn('event', ActivityEvent::threats())->count(),
            'admin' => (clone $base)->whereIn('event', ActivityEvent::inGroup('admin'))->count(),
            'ips' => (clone $base)->whereNotNull('ip_address')->distinct()->count('ip_address'),
        ];
    }

    /**
     * @return Builder<ActivityLog>
     */
    protected function query(): Builder
    {
        $event = ActivityEvent::tryFrom($this->event);

        return ActivityLog::query()
            ->when($this->since(), fn (Builder $query, $since) => $query->where('created_at', '>=', $since))
            ->when($event, fn (Builder $query) => $query->where('event', $event))
            ->when(! $event && $this->group === 'threats', fn (Builder $query) => $query->whereIn('event', ActivityEvent::threats()))
            ->when(! $event && array_key_exists($this->group, ActivityEvent::groups()), fn (Builder $query) => $query->whereIn('event', ActivityEvent::inGroup($this->group)))
            ->when(trim($this->search) !== '', function (Builder $query) {
                $term = '%'.addcslashes(trim($this->search), '%_\\').'%';

                $query->where(fn (Builder $query) => $query
                    ->where('ip_address', 'like', $term)
                    ->orWhere('properties->label', 'like', $term)
                    ->orWhere('properties->email', 'like', $term)
                    ->orWhereHas('user', fn (Builder $query) => $query->where('name', 'like', $term)->orWhere('email', 'like', $term)));
            });
    }

    protected function since(): ?Carbon
    {
        return match ($this->period) {
            '1' => now()->subDay(),
            '7' => now()->subDays(7),
            '90' => now()->subDays(90),
            'all' => null,
            default => now()->subDays(30),
        };
    }

    protected function authorizeView(): void
    {
        $this->authorize(SystemPermission::ViewActivity->value);
    }

    public function render(): View
    {
        return view('livewire.admin.activity.index')->title(__('Activity log'));
    }
}
