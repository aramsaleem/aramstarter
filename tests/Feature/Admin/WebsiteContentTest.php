<?php

use App\Enums\ActivityEvent;
use App\Enums\SystemPermission;
use App\Livewire\Admin\Website;
use App\Models\ActivityLog;
use App\Models\Faq;
use App\Models\Feature;
use App\Models\Plan;
use App\Models\User;
use App\Support\SiteSettings;
use Database\Seeders\ContentSeeder;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $this->seed(ContentSeeder::class);
});

describe('access', function () {
    test('the website pages need the content.manage permission', function (string $route) {
        $role = Role::create(['name' => 'Support', 'guard_name' => 'web']);
        $role->givePermissionTo(SystemPermission::AccessAdminPanel->value);

        $this->actingAs(User::factory()->create()->assignRole($role))
            ->get(route($route))
            ->assertForbidden();

        $this->actingAs(User::factory()->admin()->create())
            ->get(route($route))
            ->assertOk();
    })->with(['admin.website.settings', 'admin.website.features', 'admin.website.plans', 'admin.website.faqs']);

    test('actions are re-checked when the permission is revoked while the page is open', function () {
        $role = Role::create(['name' => 'Editor', 'guard_name' => 'web']);
        $role->givePermissionTo([SystemPermission::AccessAdminPanel->value, SystemPermission::ManageContent->value]);
        $editor = User::factory()->create()->assignRole($role);

        $component = Livewire::actingAs($editor)->test(Website\Faqs::class);

        $role->revokePermissionTo(SystemPermission::ManageContent->value);
        $editor->refresh();

        $component->call('toggle', Faq::first()->id)->assertForbidden();
    });
});

describe('texts', function () {
    beforeEach(function () {
        $this->actingAs(User::factory()->superAdmin()->create());
    });

    test('edited texts appear on the website, per language', function () {
        Livewire::test(Website\Settings::class)
            ->set('values.hero_title.en', 'Build faster')
            ->set('values.hero_title.ar', 'ابنِ أسرع')
            ->call('save')
            ->assertHasNoErrors();

        $this->get('/')->assertOk()->assertSee('Build faster');
        $this->withSession(['locale' => 'ar'])->get('/')->assertSee('ابنِ أسرع');
    });

    test('empty languages fall back to the default text', function () {
        Livewire::test(Website\Settings::class)
            ->set('values.hero_title.en', 'Build faster')
            ->call('save');

        // Kurdish was left empty, so it shows the English text the admin entered.
        expect(SiteSettings::for('ckb')['hero_title'])->toBe('Build faster')
            ->and(SiteSettings::for('ckb')['hero_highlight'])->toBe(__('in days, not months', [], 'ckb'));
    });

    test('only supported languages and known settings are accepted', function () {
        Livewire::test(Website\Settings::class)
            ->set('values.hero_title.xx', 'Nope')
            ->call('save')
            ->assertHasErrors('values.hero_title');

        Livewire::test(Website\Settings::class)
            ->set('values.admin_password', 'secret')
            ->call('save')
            ->assertHasErrors('values');

        expect(SiteSettings::stored())->not->toHaveKey('admin_password');
    });

    test('texts are length limited and the contact email must be an email', function () {
        Livewire::test(Website\Settings::class)
            ->set('values.hero_badge.en', str_repeat('a', 81))
            ->set('values.contact_email', 'not-an-email')
            ->call('save')
            ->assertHasErrors(['values.hero_badge.en' => 'max', 'values.contact_email' => 'email']);
    });

    test('sections can be hidden', function () {
        Livewire::test(Website\Settings::class)
            ->set('values.show_pricing', false)
            ->set('values.show_faq', false)
            ->call('save');

        $this->get('/')->assertOk()
            ->assertDontSee('id="pricing"', false)
            ->assertDontSee('id="faq"', false)
            ->assertSee('id="features"', false);
    });

    test('the default text can be restored', function () {
        SiteSettings::save(['hero_title' => ['en' => 'Custom']]);

        Livewire::test(Website\Settings::class)
            ->assertSet('values.hero_title.en', 'Custom')
            ->call('restoreDefaults')
            ->assertSet('values.hero_title.en', '');

        expect(SiteSettings::for('en')['hero_title'])->toBe('Launch your SaaS');
    });

    test('saving is recorded in the activity log', function () {
        Livewire::test(Website\Settings::class)->call('save');

        expect(ActivityLog::where('event', ActivityEvent::ContentUpdated)->first()->properties)
            ->toMatchArray(['section' => 'settings', 'action' => 'updated']);
    });
});

describe('features', function () {
    beforeEach(function () {
        $this->actingAs(User::factory()->superAdmin()->create());
    });

    test('a feature can be added in several languages', function () {
        Livewire::test(Website\Features::class)
            ->call('create')
            ->set('icon', 'rocket-launch')
            ->set('visual', 'analytics')
            ->set('title.en', 'Rockets')
            ->set('title.ar', 'صواريخ')
            ->set('description.en', 'Very fast.')
            ->call('save')
            ->assertHasNoErrors()
            ->assertSet('showModal', false);

        $feature = Feature::latest('id')->first();

        expect($feature->getTranslation('title', 'ar'))->toBe('صواريخ')
            ->and($feature->getTranslation('title', 'ckb'))->toBe('Rockets')
            ->and($feature->getTranslations('description'))->toBe(['en' => 'Very fast.']);

        $this->get('/')->assertSee('Rockets');
    });

    test('icons and animations must come from the allowed lists', function () {
        Livewire::test(Website\Features::class)
            ->call('create')
            ->set('icon', '../../secret')
            ->set('visual', 'welcome')
            ->set('title.en', 'Title')
            ->set('description.en', 'Text')
            ->call('save')
            ->assertHasErrors(['icon' => 'in', 'visual' => 'in']);
    });

    test('the default language is required', function () {
        Livewire::test(Website\Features::class)
            ->call('create')
            ->set('title.ar', 'عنوان')
            ->set('description.ar', 'نص')
            ->call('save')
            ->assertHasErrors(['title.en' => 'required', 'description.en' => 'required']);
    });

    test('titles are escaped on the website', function () {
        Feature::first()->update(['title' => ['en' => '<script>alert(1)</script>']]);

        $this->get('/')
            ->assertDontSee('<script>alert(1)</script>', false)
            ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false);
    });

    test('features can be hidden and reordered', function () {
        [$first, $second] = Feature::ordered()->take(2)->get()->all();
        $first->update(['title' => ['en' => 'Soon to be hidden']]);

        Livewire::test(Website\Features::class)
            ->call('toggle', $first->id)
            ->call('move', $second->id, -1);

        expect($first->fresh()->is_active)->toBeFalse()
            ->and(Feature::ordered()->first()->is($second))->toBeTrue();

        $this->get('/')->assertDontSee('Soon to be hidden');
    });

    test('a feature can be deleted', function () {
        $feature = Feature::first();

        Livewire::test(Website\Features::class)->call('delete', ['feature' => $feature->id]);

        expect(Feature::find($feature->id))->toBeNull();
    });
});

describe('pricing', function () {
    beforeEach(function () {
        $this->actingAs(User::factory()->superAdmin()->create());
    });

    test('a plan can be added', function () {
        Livewire::test(Website\Plans::class)
            ->call('create')
            ->set('name.en', 'Enterprise')
            ->set('features.en', "SSO\nSLA")
            ->set('price_monthly', '199')
            ->set('price_yearly', '1990')
            ->set('currency', 'EUR')
            ->set('cta_url', 'https://example.com/contact')
            ->call('save')
            ->assertHasNoErrors();

        $plan = Plan::latest('id')->first();

        expect($plan->featureList())->toBe(['SSO', 'SLA'])
            ->and($plan->currency)->toBe('EUR');

        $this->get('/')->assertSee('Enterprise')->assertSee('€199')->assertSee('https://example.com/contact');
    });

    test('button links can not run scripts or leave for protocol-relative hosts', function (string $url) {
        Livewire::test(Website\Plans::class)
            ->call('edit', Plan::first()->id)
            ->set('cta_url', $url)
            ->call('save')
            ->assertHasErrors(['cta_url' => 'regex']);
    })->with([
        'javascript:alert(1)',
        'JaVaScRiPt:alert(1)',
        'data:text/html,<script>alert(1)</script>',
        'vbscript:msgbox(1)',
        '//evil.test',
        '/\\evil.test',
        'https://ok.test" onmouseover="alert(1)',
        "https://ok.test\njavascript:alert(1)",
        'ftp://files.test',
    ]);

    test('safe button links are accepted', function (string $url) {
        Livewire::test(Website\Plans::class)
            ->call('edit', Plan::first()->id)
            ->set('cta_url', $url)
            ->call('save')
            ->assertHasNoErrors();
    })->with(['https://example.com/pay?plan=pro', 'http://example.com', '/register', '#faq']);

    test('a tampered link in the database is never rendered', function () {
        Plan::first()->forceFill(['cta_url' => 'javascript:alert(1)'])->save();

        $this->get('/')->assertOk()->assertDontSee('javascript:alert(1)', false);
    });

    test('prices must be valid amounts in a supported currency', function () {
        Livewire::test(Website\Plans::class)
            ->call('create')
            ->set('name.en', 'Bad')
            ->set('price_monthly', '-5')
            ->set('price_yearly', '1.999')
            ->set('currency', 'BTC')
            ->call('save')
            ->assertHasErrors(['price_monthly' => 'min', 'price_yearly' => 'decimal', 'currency' => 'in']);
    });
});

describe('faq', function () {
    beforeEach(function () {
        $this->actingAs(User::factory()->superAdmin()->create());
    });

    test('a question can be added and edited', function () {
        Livewire::test(Website\Faqs::class)
            ->call('create')
            ->set('question.en', 'Is there a free trial?')
            ->set('answer.en', 'Yes, 14 days.')
            ->call('save')
            ->assertHasNoErrors();

        $faq = Faq::latest('id')->first();

        Livewire::test(Website\Faqs::class)
            ->call('edit', $faq->id)
            ->assertSet('question.en', 'Is there a free trial?')
            ->set('answer.ckb', 'بەڵێ، 14 ڕۆژ.')
            ->call('save');

        expect($faq->fresh()->getTranslation('answer', 'ckb'))->toBe('بەڵێ، 14 ڕۆژ.');

        $this->get('/')->assertSee('Is there a free trial?');
    });

    test('clearing a translation falls back to the default language', function () {
        $faq = Faq::first();
        $faq->setTranslation('question', 'ar', 'سؤال')->save();

        Livewire::test(Website\Faqs::class)
            ->call('edit', $faq->id)
            ->set('question.ar', '')
            ->call('save');

        expect($faq->fresh()->getTranslations('question'))->not->toHaveKey('ar');
    });

    test('empty sections are left out of the website', function () {
        Faq::query()->delete();

        $this->get('/')->assertOk()->assertDontSee('id="faq"', false);
    });
});

test('the content seeder never overwrites edited content', function () {
    Feature::first()->update(['title' => ['en' => 'Edited']]);

    $this->seed(ContentSeeder::class);

    expect(Feature::where('title->en', 'Edited')->exists())->toBeTrue()
        ->and(Feature::count())->toBe(count(ContentSeeder::features()));
});
