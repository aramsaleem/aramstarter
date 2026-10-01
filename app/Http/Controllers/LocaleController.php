<?php

namespace App\Http\Controllers;

use App\Support\Localization;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LocaleController extends Controller
{
    /**
     * Switch the interface language (used by the language switcher).
     */
    public function __invoke(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'locale' => ['required', 'string', Rule::in(Localization::codes())],
        ]);

        $request->session()->put('locale', $validated['locale']);

        $request->user()?->update(['locale' => $validated['locale']]);

        return back();
    }
}
