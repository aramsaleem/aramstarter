<?php

namespace App\Http\Middleware;

use App\Support\Impersonation;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Ends impersonation sessions that ran past Impersonation::MAX_MINUTES.
 */
class HandleImpersonation
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->hasSession() || ! Impersonation::active()) {
            return $next($request);
        }

        if (! $request->user()) {
            $request->session()->forget('impersonation');

            return $next($request);
        }

        if (Impersonation::expired()) {
            Impersonation::stop();

            // Livewire shows "This page has expired" for a 419 and reloads, now as the admin.
            abort_if($request->hasHeader('X-Livewire'), 419);

            return redirect()->route('dashboard')->with('alert', [
                'icon' => 'info',
                'title' => __('Your session as another user ended after :minutes minutes.', ['minutes' => Impersonation::MAX_MINUTES]),
            ]);
        }

        return $next($request);
    }
}
