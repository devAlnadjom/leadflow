<?php

use App\Models\User;

it('shows company onboarding page for authenticated users without company', function () {
    $user = User::factory()->create();

    $this->withoutVite();

    $this->actingAs($user)
        ->get(route('onboarding.company.create'))
        ->assertOk();
});

it('stores company profile and attaches it to the user', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('onboarding.company.store'), [
        'name' => 'clientux Construction',
        'phone' => '+1 514 555 0000',
        'email' => 'info@clientux.test',
        'industry' => 'Construction',
        'address' => '123 Rue Principale',
        'served_areas' => ['Montreal', 'Laval'],
        'primary_color' => '#1D4ED8',
        'secondary_color' => '#0F172A',
        'quote_prefix' => 'DEV',
        'invoice_prefix' => 'FAC',
        'default_tax_rate' => 14.975,
        'currency' => 'CAD',
        'terms_and_conditions' => 'Paiable a reception.',
    ])
        ->assertRedirect(route('dashboard'));

    $user->refresh();

    expect($user->company_id)->not->toBeNull();

    $this->assertDatabaseHas('companies', [
        'id' => $user->company_id,
        'name' => 'clientux Construction',
        'industry' => 'Construction',
    ]);

    $this->assertDatabaseHas('company_settings', [
        'company_id' => $user->company_id,
        'quote_prefix' => 'DEV',
        'invoice_prefix' => 'FAC',
        'currency' => 'CAD',
    ]);
});