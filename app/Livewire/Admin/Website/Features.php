<?php

namespace App\Livewire\Admin\Website;

use App\Livewire\Admin\Website\Concerns\ManagesContent;
use App\Livewire\Concerns\InteractsWithAlerts;
use App\Models\Feature;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * The feature cards on the public website.
 */
#[Layout('components.layouts.admin')]
class Features extends Component
{
    use InteractsWithAlerts, ManagesContent;

    public bool $showModal = false;

    #[Locked]
    public ?int $editingId = null;

    public string $icon = 'sparkles';

    public string $visual = '';

    /**
     * @var array<string, string>
     */
    public array $title = [];

    /**
     * @var array<string, string>
     */
    public array $description = [];

    public bool $is_active = true;

    public function mount(): void
    {
        $this->authorizeContent();
    }

    /**
     * @return Collection<int, Feature>
     */
    #[Computed]
    public function features(): Collection
    {
        return Feature::ordered()->get();
    }

    public function create(): void
    {
        $this->authorizeContent();

        $this->reset('editingId', 'icon', 'visual', 'is_active');
        $this->title = $this->emptyTranslations();
        $this->description = $this->emptyTranslations();
        $this->resetErrorBag();

        $this->showModal = true;
    }

    public function edit(int $featureId): void
    {
        $this->authorizeContent();

        $feature = Feature::findOrFail($featureId);

        $this->editingId = $feature->id;
        $this->icon = $feature->icon;
        $this->visual = (string) $feature->visual;
        $this->title = [...$this->emptyTranslations(), ...$feature->getTranslations('title')];
        $this->description = [...$this->emptyTranslations(), ...$feature->getTranslations('description')];
        $this->is_active = $feature->is_active;
        $this->resetErrorBag();

        $this->showModal = true;
    }

    public function save(): void
    {
        $this->authorizeContent();

        $this->validate();

        $feature = $this->editingId ? Feature::findOrFail($this->editingId) : new Feature;

        $feature->fill([
            'icon' => $this->icon,
            'visual' => $this->visual ?: null,
            'is_active' => $this->is_active,
        ]);
        $feature->replaceTranslations('title', $this->filledTranslations($this->title));
        $feature->replaceTranslations('description', $this->filledTranslations($this->description));
        $feature->save();

        $this->recordContentChange('features', $this->editingId ? 'updated' : 'created', $feature->getTranslation('title', config('app.locale')));

        $this->showModal = false;
        unset($this->features);

        $this->toast($this->editingId ? __('Feature updated.') : __('Feature added.'));
    }

    public function toggle(int $featureId): void
    {
        $this->authorizeContent();

        $feature = Feature::findOrFail($featureId);
        $feature->update(['is_active' => ! $feature->is_active]);

        $this->recordContentChange('features', $feature->is_active ? 'shown' : 'hidden', $feature->getTranslation('title', config('app.locale')));
        unset($this->features);
    }

    public function move(int $featureId, int $direction): void
    {
        $this->authorizeContent();

        Feature::findOrFail($featureId)->move($this->direction($direction));

        unset($this->features);
    }

    public function confirmDelete(int $featureId): void
    {
        $this->authorizeContent();

        $feature = Feature::findOrFail($featureId);

        $this->askForConfirmation(
            __('Delete ":title"?', ['title' => $feature->title]),
            __('The card is removed from the website in every language.'),
            'delete',
            ['feature' => $feature->id],
            __('Delete'),
        );
    }

    /**
     * @param  array{feature?: int}  $data
     */
    public function delete(array $data): void
    {
        $this->authorizeContent();

        $feature = Feature::find($data['feature'] ?? null);

        if (! $feature) {
            return;
        }

        $this->recordContentChange('features', 'deleted', $feature->getTranslation('title', config('app.locale')));

        $feature->delete();
        unset($this->features);

        $this->toast(__('Feature deleted.'));
    }

    /**
     * @return array<string, list<mixed>>
     */
    protected function rules(): array
    {
        return [
            'icon' => ['required', 'string', Rule::in(Feature::ICONS)],
            'visual' => ['nullable', 'string', Rule::in(Feature::VISUALS)],
            'is_active' => ['boolean'],
            ...$this->translatableRules('title', 80),
            ...$this->translatableRules('description', 240),
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function validationAttributes(): array
    {
        return [
            ...$this->translatableAttributes('title', __('Title')),
            ...$this->translatableAttributes('description', __('Description')),
        ];
    }

    public function render(): View
    {
        return view('livewire.admin.website.features')->title(__('Website features'));
    }
}
