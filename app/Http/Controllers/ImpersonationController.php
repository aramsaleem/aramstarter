<?php

namespace App\Http\Controllers;

use App\Support\Impersonation;
use Illuminate\Http\RedirectResponse;

class ImpersonationController extends Controller
{
    /**
     * Return to the admin's own account.
     */
    public function leave(): RedirectResponse
    {
        if (! Impersonation::active()) {
            return redirect()->route('dashboard');
        }

        $target = Impersonation::stop();

        if (! auth()->check()) {
            return redirect('/');
        }

        $alert = ['icon' => 'success', 'title' => __('Welcome back, :name.', ['name' => auth()->user()->name])];

        if ($target && auth()->user()->can('update', $target)) {
            return redirect()->route('admin.users.edit', $target)->with('alert', $alert);
        }

        return redirect()->route(auth()->user()->can('admin.access') ? 'admin.dashboard' : 'dashboard')->with('alert', $alert);
    }
}
