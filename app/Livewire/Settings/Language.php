<?php

namespace App\Livewire\Settings;

use App\Livewire\Concerns\InteractsWithAlerts;
use App\Support\Localization;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Component;

class Language extends Component
{
    use InteractsWithAlerts;

    public string $locale = '';

    public function mount(): void
    {
        $this->locale = Auth::user()->locale ?? app()->getLocale();
    }

    public function updateLocale(): void
    {
        $this->validate([
            'locale' => ['required', 'string', Rule::in(Localization::codes())],
        ]);

        Auth::user()->update(['locale' => $this->locale]);
        session()->put('locale', $this->locale);

        $this->flashToast(__('Language updated.', locale: $this->locale));

        // Full reload so the layout picks up the new language and text direction.
        $this->redirectRoute('settings.language');
    }

    public function render(): View
    {
        return view('livewire.settings.language')->title(__('Language'));
    }
}
