<?php

use Illuminate\Support\Facades\Route;

beforeEach(function () {
    Route::middleware('web')->get('/_locale-probe', fn () => response(app()->getLocale()));
});

test('locale can be switched to french and persisted in session', function () {
    $this->from('/_locale-probe')
        ->post(route('locale.update'), [
            'locale' => 'fr',
        ])
        ->assertRedirect('/_locale-probe')
        ->assertCookie('locale', 'fr');

    $this->get('/_locale-probe')->assertSee('fr');
});

test('locale cookie is used when session locale is missing', function () {
    $this->withCookie('locale', 'fr')
        ->get('/_locale-probe')
        ->assertSee('fr');
});

test('unsupported locale is rejected', function () {
    $this->post(route('locale.update'), [
        'locale' => 'es',
    ])->assertSessionHasErrors('locale');
});
