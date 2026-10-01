<?php

namespace App\Livewire\Admin\Website;

use App\Livewire\Admin\Website\Concerns\ManagesContent;
use App\Livewire\Concerns\InteractsWithAlerts;
use App\Models\Faq;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * The frequently asked questions on the public website.
 */
#[Layout('components.layouts.admin')]
class Faqs extends Component
{
    use InteractsWithAlerts, ManagesContent;

    public bool $showModal = false;

    #[Locked]
    public ?int $editingId = null;

    /**
     * @var array<string, string>
     */
    public array $question = [];

    /**
     * @var array<string, string>
     */
    public array $answer = [];

    public bool $is_active = true;

    public function mount(): void
    {
        $this->authorizeContent();
    }

    /**
     * @return Collection<int, Faq>
     */
    #[Computed]
    public function faqs(): Collection
    {
        return Faq::ordered()->get();
    }

    public function create(): void
    {
        $this->authorizeContent();

        $this->reset('editingId', 'is_active');
        $this->question = $this->emptyTranslations();
        $this->answer = $this->emptyTranslations();
        $this->resetErrorBag();

        $this->showModal = true;
    }

    public function edit(int $faqId): void
    {
        $this->authorizeContent();

        $faq = Faq::findOrFail($faqId);

        $this->editingId = $faq->id;
        $this->question = [...$this->emptyTranslations(), ...$faq->getTranslations('question')];
        $this->answer = [...$this->emptyTranslations(), ...$faq->getTranslations('answer')];
        $this->is_active = $faq->is_active;
        $this->resetErrorBag();

        $this->showModal = true;
    }

    public function save(): void
    {
        $this->authorizeContent();

        $this->validate();

        $faq = $this->editingId ? Faq::findOrFail($this->editingId) : new Faq;

        $faq->is_active = $this->is_active;
        $faq->replaceTranslations('question', $this->filledTranslations($this->question));
        $faq->replaceTranslations('answer', $this->filledTranslations($this->answer));
        $faq->save();

        $this->recordContentChange('faq', $this->editingId ? 'updated' : 'created', $faq->getTranslation('question', config('app.locale')));

        $this->showModal = false;
        unset($this->faqs);

        $this->toast($this->editingId ? __('Question updated.') : __('Question added.'));
    }

    public function toggle(int $faqId): void
    {
        $this->authorizeContent();

        $faq = Faq::findOrFail($faqId);
        $faq->update(['is_active' => ! $faq->is_active]);

        $this->recordContentChange('faq', $faq->is_active ? 'shown' : 'hidden', $faq->getTranslation('question', config('app.locale')));
        unset($this->faqs);
    }

    public function move(int $faqId, int $direction): void
    {
        $this->authorizeContent();

        Faq::findOrFail($faqId)->move($this->direction($direction));

        unset($this->faqs);
    }

    public function confirmDelete(int $faqId): void
    {
        $this->authorizeContent();

        $faq = Faq::findOrFail($faqId);

        $this->askForConfirmation(
            __('Delete this question?'),
            $faq->question,
            'delete',
            ['faq' => $faq->id],
            __('Delete'),
        );
    }

    /**
     * @param  array{faq?: int}  $data
     */
    public function delete(array $data): void
    {
        $this->authorizeContent();

        $faq = Faq::find($data['faq'] ?? null);

        if (! $faq) {
            return;
        }

        $this->recordContentChange('faq', 'deleted', $faq->getTranslation('question', config('app.locale')));

        $faq->delete();
        unset($this->faqs);

        $this->toast(__('Question deleted.'));
    }

    /**
     * @return array<string, list<mixed>>
     */
    protected function rules(): array
    {
        return [
            'is_active' => ['boolean'],
            ...$this->translatableRules('question', 200),
            ...$this->translatableRules('answer', 2000),
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function validationAttributes(): array
    {
        return [
            ...$this->translatableAttributes('question', __('Question')),
            ...$this->translatableAttributes('answer', __('Answer')),
        ];
    }

    public function render(): View
    {
        return view('livewire.admin.website.faqs')->title(__('Website FAQ'));
    }
}
