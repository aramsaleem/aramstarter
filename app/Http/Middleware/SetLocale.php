<?php

namespace App\Http\Middleware;

use App\Support\Localization;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Use the user's saved language, then the one picked this session,
     * then the best match for the browser's Accept-Language header.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = collect([
            $request->user()?->locale,
            $request->hasSession() ? $request->session()->get('locale') : null,
        ])->first(fn (?string $locale) => Localization::isSupported($locale))
            ?? $request->getPreferredLanguage(Localization::codes())
            ?? config('app.locale');

        App::setLocale($locale);
        Carbon::setLocale($locale);

        return $next($request);
    }
}
