<?php

namespace Database\Seeders;

use App\Models\Faq;
use App\Models\Feature;
use App\Models\Plan;
use App\Support\Localization;
use Illuminate\Database\Seeder;

/**
 * The public website's starting content: feature cards, pricing plans and FAQ.
 *
 * Each table is only filled while it is empty, so running this again never
 * overwrites what was edited in Admin > Website.
 */
class ContentSeeder extends Seeder
{
    public function run(): void
    {
        if (Feature::query()->doesntExist()) {
            foreach (self::features() as $feature) {
                Feature::create([
                    'icon' => $feature['icon'],
                    'visual' => $feature['visual'],
                    'title' => self::translate($feature['title']),
                    'description' => self::translate($feature['description']),
                ]);
            }
        }

        if (Plan::query()->doesntExist()) {
            foreach (self::plans() as $plan) {
                Plan::create([
                    'name' => self::translate($plan['name']),
                    'description' => self::translate($plan['description']),
                    'features' => self::translateLines($plan['features']),
                    'cta_label' => self::translate('Get started'),
                    'price_monthly' => $plan['monthly'],
                    'price_yearly' => $plan['yearly'],
                    'is_featured' => $plan['featured'],
                ]);
            }
        }

        if (Faq::query()->doesntExist()) {
            foreach (self::faqs() as [$question, $answer]) {
                Faq::create([
                    'question' => self::translate($question),
                    'answer' => self::translate($answer),
                ]);
            }
        }
    }

    /**
     * @return list<array{icon: string, visual: string, title: string, description: string}>
     */
    public static function features(): array
    {
        return [
            ['icon' => 'finger-print', 'visual' => 'two-factor', 'title' => 'Two-factor authentication', 'description' => 'Time-based one-time codes with QR setup and recovery codes.'],
            ['icon' => 'language', 'visual' => 'languages', 'title' => 'Localization', 'description' => 'English, Arabic and Kurdish out of the box, with full right-to-left support.'],
            ['icon' => 'identification', 'visual' => 'permissions', 'title' => 'Roles & permissions', 'description' => 'Fine-grained permissions with guards against privilege escalation.'],
            ['icon' => 'command-line', 'visual' => 'command-palette', 'title' => 'Command palette', 'description' => 'Jump to any page or action with Ctrl + K.'],
            ['icon' => 'link', 'visual' => 'social', 'title' => 'Social login', 'description' => 'Google, Facebook and X buttons appear as soon as you add the keys.'],
            ['icon' => 'chart-bar-square', 'visual' => 'analytics', 'title' => 'Admin dashboard', 'description' => 'Live charts, security insights and a full audit trail.'],
        ];
    }

    /**
     * @return list<array{name: string, description: string, features: list<string>, monthly: int, yearly: int, featured: bool}>
     */
    public static function plans(): array
    {
        return [
            ['name' => 'Starter', 'description' => 'For side projects and trying things out.', 'monthly' => 0, 'yearly' => 0, 'featured' => false,
                'features' => ['Up to 3 team members', 'Email and password login', 'Two-factor authentication', 'Community support']],
            ['name' => 'Pro', 'description' => 'For growing products with real customers.', 'monthly' => 29, 'yearly' => 278, 'featured' => true,
                'features' => ['Up to 25 team members', 'Social login', 'Roles and permissions', 'Admin dashboard', 'Priority support']],
            ['name' => 'Business', 'description' => 'For teams that need full control.', 'monthly' => 79, 'yearly' => 758, 'featured' => false,
                'features' => ['Unlimited team members', 'Everything in Pro', 'Custom permissions', 'Audit-ready security', 'Dedicated manager']],
        ];
    }

    /**
     * @return list<array{string, string}>
     */
    public static function faqs(): array
    {
        return [
            ['Is it ready for production?', 'Yes. It ships with a full test suite, strict models, rate limiting and security headers. Set APP_DEBUG=false and serve it over HTTPS.'],
            ['Can I add more languages?', 'Add the language to config/localization.php and translate one JSON file. A test tells you if anything is missing.'],
            ['Do I need a paid UI kit?', 'No. Every component is plain Tailwind CSS, so you own and can change all of it.'],
            ['Can I change the website without touching code?', 'Yes. Texts, features, pricing and these questions are edited in Admin > Website, in every language.'],
        ];
    }

    /**
     * Every English string this seeder translates. The localization test checks they all have translations.
     *
     * @return list<string>
     */
    public static function strings(): array
    {
        return [
            'Get started',
            ...array_merge(...array_map(fn (array $feature) => [$feature['title'], $feature['description']], self::features())),
            ...array_merge(...array_map(fn (array $plan) => [$plan['name'], $plan['description'], ...$plan['features']], self::plans())),
            ...array_merge(...self::faqs()),
        ];
    }

    /**
     * @return array<string, string>
     */
    private static function translate(string $text): array
    {
        $translations = [];

        foreach (Localization::codes() as $locale) {
            $translations[$locale] = __($text, [], $locale);
        }

        return $translations;
    }

    /**
     * @param  list<string>  $lines
     * @return array<string, string>
     */
    private static function translateLines(array $lines): array
    {
        $translations = [];

        foreach (Localization::codes() as $locale) {
            $translations[$locale] = implode("\n", array_map(fn (string $line) => __($line, [], $locale), $lines));
        }

        return $translations;
    }
}
