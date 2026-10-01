<?php

use App\Livewire\Settings\Language;
use App\Models\User;
use Livewire\Livewire;

test('the appearance page is displayed', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('settings.appearance'))
        ->assertOk()
        ->assertSee(__('Appearance'));
});

test('the language page is displayed', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('settings.language'))
        ->assertOk()
        ->assertSee('العربية')
        ->assertSee('کوردی');
});

test('users can change their language', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Language::class)
        ->set('locale', 'ar')
        ->call('updateLocale')
        ->assertHasNoErrors()
        ->assertRedirect(route('settings.language'));

    expect($user->refresh()->locale)->toBe('ar')
        ->and(session('locale'))->toBe('ar');
});

test('unsupported languages are rejected', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Language::class)
        ->set('locale', 'xx')
        ->call('updateLocale')
        ->assertHasErrors('locale');

    expect($user->refresh()->locale)->toBeNull();
});
