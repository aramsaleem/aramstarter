<?php

namespace App\Livewire\Settings;

use Illuminate\Contracts\View\View;
use Livewire\Component;

/**
 * The theme is stored in the browser (localStorage) and applied before the page paints,
 * see resources/views/partials/head.blade.php and resources/js/app.js.
 */
class Appearance extends Component
{
    public function render(): View
    {
        return view('livewire.settings.appearance')->title(__('Appearance'));
    }
}
