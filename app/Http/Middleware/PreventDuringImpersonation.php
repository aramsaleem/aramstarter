<?php

namespace App\Http\Middleware;

use App\Support\Impersonation;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Keeps credentials out of reach while an admin is signed in as someone else:
 * password, two-factor, connected accounts and password confirmation.
 */
class PreventDuringImpersonation
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_if(Impersonation::active(), 403, __('This is not available while you are signed in as another user.'));

        return $next($request);
    }
}
