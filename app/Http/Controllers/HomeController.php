<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Faq;
use App\Models\Feature;
use App\Models\Plan;
use App\Models\User;
use App\Support\Localization;
use App\Support\SiteSettings;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Cache;
use Spatie\Permission\Models\Permission;

/**
 * The public website. Its content is edited in Admin > Website.
 */
class HomeController extends Controller
{
    public function __invoke(): View
    {
        $site = SiteSettings::for();

        return view('welcome', [
            'site' => $site,
            'features' => $site['show_features'] ? Feature::active()->ordered()->get() : collect(),
            'plans' => $site['show_pricing'] ? Plan::active()->ordered()->get() : collect(),
            'faqs' => $site['show_faq'] ? Faq::active()->ordered()->get() : collect(),
            'stats' => $site['show_stats'] ? $this->stats() : null,
        ]);
    }

    /**
     * Live numbers for the counters, cached briefly so busy traffic doesn't hit the database.
     *
     * @return array{users: int, languages: int, permissions: int, events: int}
     */
    private function stats(): array
    {
        return [
            ...Cache::remember('landing-stats', now()->addMinutes(10), fn () => [
                'users' => User::count(),
                'permissions' => Permission::count(),
                'events' => ActivityLog::count(),
            ]),
            'languages' => count(Localization::supported()),
        ];
    }
}
