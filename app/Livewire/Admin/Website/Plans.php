<?php

namespace App\Livewire\Admin\Website;

use App\Livewire\Admin\Website\Concerns\ManagesContent;
use App\Livewire\Concerns\InteractsWithAlerts;
use App\Models\Plan;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * The pricing plans on the public website.
 */
#[Layout('components.layouts.admin')]
class Plans extends Component
{
    use InteractsWithAlerts, ManagesContent;

    /**
     * Web addresses, site paths ("/register") or page anchors ("#faq"). Anything
     * else - javascript:, data:, protocol-relative "//evil.test" - is rejected.
     */
    public const LINK_PATTERN = '~^(https?://[^\s<>"\'`]+|/(?![/\\\\])[^\s<>"\'`]*|#[A-Za-z0-9_-]+)\z~';

    public bool $showModal = false;

    #[Locked]
    public ?int $editingId = null;

    /**
     * @var array<string, string>
     */
    public array $name = [];

    /**
     * @var array<string, string>
     */
    public array $description = [];

    /**
     * @var array<string, string>
     */
    public array $features = [];

    /**
     * @var array<string, string>
     */
    public array $cta_label = [];

    public string $cta_url = '';

    public string $price_monthly = '0';

    public string $price_yearly = '';

    public string $currency = 'USD';

    public bool $is_featured = false;

    public bool $is_active = true;

    public function mount(): void
    {
        $this->authorizeContent();
    }

    /**
     * @return Collection<int, Plan>
     */
    #[Computed]
    public function plans(): Collection
    {
        return Plan::ordered()->get();
    }

    public function create(): void
    {
        $this->authorizeContent();

        $this->reset('editingId', 'cta_url', 'price_monthly', 'price_yearly', 'currency', 'is_featured', 'is_active');

        foreach (['name', 'description', 'features', 'cta_label'] as $field) {
            $this->{$field} = $this->emptyTranslations();
        }

        $this->resetErrorBag();
        $this->showModal = true;
    }

    public function edit(int $planId): void
    {
        $this->authorizeContent();

        $plan = Plan::findOrFail($planId);

        $this->editingId = $plan->id;

        foreach (['name', 'description', 'features', 'cta_label'] as $field) {
            $this->{$field} = [...$this->emptyTranslations(), ...$plan->getTranslations($field)];
        }

        $this->cta_url = (string) $plan->cta_url;
        $this->price_monthly = self::formatAmount($plan->price_monthly);
        $this->price_yearly = $plan->price_yearly === null ? '' : self::formatAmount($plan->price_yearly);
        $this->currency = $plan->currency;
        $this->is_featured = $plan->is_featured;
        $this->is_active = $plan->is_active;
        $this->resetErrorBag();

        $this->showModal = true;
    }

    public function save(): void
    {
        $this->authorizeContent();

        $this->validate();

        $plan = $this->editingId ? Plan::findOrFail($this->editingId) : new Plan;

        $plan->fill([
            'cta_url' => filled($this->cta_url) ? trim($this->cta_url) : null,
            'price_monthly' => $this->price_monthly,
            'price_yearly' => filled($this->price_yearly) ? $this->price_yearly : null,
            'currency' => $this->currency,
            'is_featured' => $this->is_featured,
            'is_active' => $this->is_active,
        ]);

        foreach (['name', 'description', 'features', 'cta_label'] as $field) {
            $plan->replaceTranslations($field, $this->filledTranslations($this->{$field}));
        }

        $plan->save();

        $this->recordContentChange('pricing', $this->editingId ? 'updated' : 'created', $plan->getTranslation('name', config('app.locale')));

        $this->showModal = false;
        unset($this->plans);

        $this->toast($this->editingId ? __('Plan updated.') : __('Plan added.'));
    }

    public function toggle(int $planId): void
    {
        $this->authorizeContent();

        $plan = Plan::findOrFail($planId);
        $plan->update(['is_active' => ! $plan->is_active]);

        $this->recordContentChange('pricing', $plan->is_active ? 'shown' : 'hidden', $plan->getTranslation('name', config('app.locale')));
        unset($this->plans);
    }

    public function move(int $planId, int $direction): void
    {
        $this->authorizeContent();

        Plan::findOrFail($planId)->move($this->direction($direction));

        unset($this->plans);
    }

    public function confirmDelete(int $planId): void
    {
        $this->authorizeContent();

        $plan = Plan::findOrFail($planId);

        $this->askForConfirmation(
            __('Delete the :name plan?', ['name' => $plan->name]),
            __('The plan is removed from the pricing section in every language.'),
            'delete',
            ['plan' => $plan->id],
            __('Delete'),
        );
    }

    /**
     * @param  array{plan?: int}  $data
     */
    public function delete(array $data): void
    {
        $this->authorizeContent();

        $plan = Plan::find($data['plan'] ?? null);

        if (! $plan) {
            return;
        }

        $this->recordContentChange('pricing', 'deleted', $plan->getTranslation('name', config('app.locale')));

        $plan->delete();
        unset($this->plans);

        $this->toast(__('Plan deleted.'));
    }

    /**
     * @return array<string, list<mixed>>
     */
    protected function rules(): array
    {
        $amount = ['numeric', 'min:0', 'max:9999999', 'decimal:0,2'];

        return [
            ...$this->translatableRules('name', 40),
            ...$this->translatableRules('description', 160, required: false),
            ...$this->translatableRules('features', 1500, required: false),
            ...$this->translatableRules('cta_label', 30, required: false),
            'cta_url' => ['nullable', 'string', 'max:255', 'regex:'.self::LINK_PATTERN],
            'price_monthly' => ['required', ...$amount],
            'price_yearly' => ['nullable', ...$amount],
            'currency' => ['required', 'string', Rule::in(Plan::CURRENCIES)],
            'is_featured' => ['boolean'],
            'is_active' => ['boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function messages(): array
    {
        return [
            'cta_url.regex' => __('Use a web address (https://…), a page on this site (/register) or a section (#pricing).'),
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function validationAttributes(): array
    {
        return [
            ...$this->translatableAttributes('name', __('Name')),
            ...$this->translatableAttributes('description', __('Description')),
            ...$this->translatableAttributes('features', __('Included')),
            ...$this->translatableAttributes('cta_label', __('Button text')),
            'cta_url' => __('Button link'),
            'price_monthly' => __('Monthly price'),
            'price_yearly' => __('Yearly price'),
        ];
    }

    private static function formatAmount(float|string $amount): string
    {
        $amount = (float) $amount;

        return fmod($amount, 1.0) === 0.0 ? (string) (int) $amount : number_format($amount, 2, '.', '');
    }

    public function render(): View
    {
        return view('livewire.admin.website.plans')->title(__('Website pricing'));
    }
}
