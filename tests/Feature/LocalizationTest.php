<?php

use App\Models\User;
use App\Support\SiteSettings;
use Database\Seeders\ContentSeeder;
use Illuminate\Support\Facades\File;

/*
 * Datasets are built before the application boots, so read the locales straight from the config file.
 */
dataset('translated locales', fn () => array_values(array_diff(
    array_keys((require dirname(__DIR__, 2).'/config/localization.php')['supported']),
    ['en'],
)));

/**
 * Every __('...') string used in app/ and resources/views, plus the keys built at runtime.
 *
 * @return list<string>
 */
function translationKeysUsedInCode(): array
{
    $files = collect([app_path(), resource_path('views')])
        ->flatMap(fn (string $directory) => File::allFiles($directory))
        ->filter(fn (SplFileInfo $file) => str_ends_with($file->getFilename(), '.php'));

    $keys = [];

    foreach ($files as $file) {
        $source = $file->getContents();

        preg_match_all('/__\(\s*\'((?:\\\\.|[^\'\\\\])*)\'/', $source, $single);
        preg_match_all('/__\(\s*"((?:\\\\.|[^"\\\\])*)"/', $source, $double);

        foreach ($single[1] as $key) {
            $keys[] = str_replace("\\'", "'", $key);
        }

        foreach ($double[1] as $key) {
            $keys[] = str_replace('\\"', '"', $key);
        }
    }

    $runtimeKeys = [
        // Localization::supported() names, PermissionLabel group and action labels
        'English', 'Arabic', 'Central Kurdish',
        'Admin', 'Users', 'Roles', 'Permissions', 'Content', 'Activity', 'Other',
        'Access', 'View', 'Create', 'Update', 'Delete', 'Manage', 'Impersonate',
        // Default website texts, translated when shown or seeded
        ...array_filter(array_column(SiteSettings::fields(), 'default'), 'is_string'),
        ...ContentSeeder::strings(),
    ];

    return collect([...$keys, ...$runtimeKeys])
        ->unique()
        // "group.key" strings live in lang/{locale}/{group}.php, e.g. auth.failed
        ->reject(fn (string $key) => preg_match('/^([a-z_-]+)\.[a-z0-9_.-]+$/i', $key, $match)
            && File::exists(lang_path("en/{$match[1]}.php")))
        ->values()
        ->all();
}

test('every string used in the interface is translated into every language', function (string $locale) {
    $translations = json_decode(File::get(lang_path("{$locale}.json")), true, flags: JSON_THROW_ON_ERROR);

    $missing = array_values(array_diff(translationKeysUsedInCode(), array_keys($translations)));

    expect($missing)->toBe([], "Missing {$locale} translations");
})->with('translated locales');

test('translations keep their :placeholders', function (string $locale) {
    $translations = json_decode(File::get(lang_path("{$locale}.json")), true, flags: JSON_THROW_ON_ERROR);

    foreach ($translations as $key => $translation) {
        preg_match_all('/:[a-zA-Z]+/', $key, $expected);
        preg_match_all('/:[a-zA-Z]+/', $translation, $actual);

        expect(array_unique($actual[0]))->toEqualCanonicalizing(array_unique($expected[0]), "Placeholder mismatch for \"{$key}\"");
    }
})->with('translated locales');

test('guests can switch the language', function () {
    $this->from('/')
        ->post(route('locale.update'), ['locale' => 'ar'])
        ->assertRedirect('/');

    $this->get('/')
        ->assertSee('lang="ar"', false)
        ->assertSee('dir="rtl"', false)
        ->assertSee('تسجيل الدخول', false);
});

test('signed in users keep their language on their profile', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post(route('locale.update'), ['locale' => 'ckb']);

    expect($user->refresh()->locale)->toBe('ckb');
});

test('the saved language wins over the browser language', function () {
    $user = User::factory()->create(['locale' => 'ckb']);

    $this->actingAs($user)
        ->withHeader('Accept-Language', 'ar')
        ->get(route('dashboard'))
        ->assertSee('lang="ckb"', false)
        ->assertSee('dir="rtl"', false);
});

test('the browser language is used for new visitors', function () {
    $this->withHeader('Accept-Language', 'ar-IQ,ar;q=0.9,en;q=0.8')
        ->get('/')
        ->assertSee('lang="ar"', false);
});

test('unsupported browser languages fall back to the default', function () {
    $this->withHeader('Accept-Language', 'fr-FR,fr;q=0.9')
        ->get('/')
        ->assertSee('lang="en"', false)
        ->assertSee('dir="ltr"', false);
});

test('unsupported languages are rejected', function () {
    $this->post(route('locale.update'), ['locale' => 'xx'])
        ->assertSessionHasErrors('locale');
});

test('validation messages are translated', function () {
    app()->setLocale('ar');

    expect(__('validation.required', ['attribute' => __('validation.attributes.email')]))
        ->toBe('حقل البريد الإلكتروني مطلوب.');
});
