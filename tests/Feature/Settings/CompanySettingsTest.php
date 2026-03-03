<?php

use App\Models\Company;
use App\Models\CompanySetting;
use App\Models\User;

test('company settings page is displayed', function () {
    $company = Company::create([
        'name' => 'LeadFlow Inc.',
    ]);

    CompanySetting::create([
        'company_id' => $company->id,
        'quote_prefix' => 'QUO',
        'invoice_prefix' => 'INV',
        'currency' => 'USD',
    ]);

    $user = User::factory()->create([
        'company_id' => $company->id,
    ]);

    $this->withoutVite();

    $this->actingAs($user)
        ->get(route('company-settings.edit'))
        ->assertOk();
});

test('users without company are redirected to onboarding from company settings', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('company-settings.edit'))
        ->assertRedirect(route('onboarding.company.create'));

    $this->actingAs($user)
        ->patch(route('company-settings.update'), [
            'name' => 'Any Company',
            'quote_prefix' => 'QUO',
            'invoice_prefix' => 'INV',
            'currency' => 'USD',
        ])
        ->assertRedirect(route('onboarding.company.create'));
});

test('company settings can be updated', function () {
    $company = Company::create([
        'name' => 'Initial Name',
    ]);

    CompanySetting::create([
        'company_id' => $company->id,
        'quote_prefix' => 'QUO',
        'invoice_prefix' => 'INV',
        'currency' => 'USD',
    ]);

    $user = User::factory()->create([
        'company_id' => $company->id,
    ]);

    $response = $this->actingAs($user)
        ->patch(route('company-settings.update'), [
            'name' => 'LeadFlow Construction',
            'phone' => '+1 514 555 0000',
            'email' => 'info@leadflow.test',
            'industry' => 'Construction',
            'address' => '123 Rue Principale',
            'served_areas' => ['Montreal', 'Laval'],
            'primary_color' => '#1D4ED8',
            'secondary_color' => '#0F172A',
            'quote_prefix' => 'dev',
            'invoice_prefix' => 'fac',
            'default_tax_rate' => 14.975,
            'currency' => 'cad',
            'terms_and_conditions' => 'Paiable a reception.',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('company-settings.edit'));

    $this->assertDatabaseHas('companies', [
        'id' => $company->id,
        'name' => 'LeadFlow Construction',
        'industry' => 'Construction',
        'email' => 'info@leadflow.test',
    ]);

    $this->assertDatabaseHas('company_settings', [
        'company_id' => $company->id,
        'quote_prefix' => 'DEV',
        'invoice_prefix' => 'FAC',
        'currency' => 'CAD',
    ]);
});
