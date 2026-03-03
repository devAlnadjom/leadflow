<?php

use App\Models\Company;
use App\Models\LeadForm;
use App\Models\LeadRecord;
use App\Models\User;

it('shows leads index for an onboarded user', function () {
    $company = Company::factory()->create();
    $user = User::factory()->create(['company_id' => $company->id]);
    $form = LeadForm::factory()->create(['company_id' => $company->id, 'name' => 'Main Widget']);

    LeadRecord::factory()->create([
        'lead_form_id' => $form->id,
        'name' => 'Jane Client',
    ]);

    $this->withoutVite();

    $this->actingAs($user)
        ->get(route('leads.index'))
        ->assertOk()
        ->assertSee('Jane Client');
});

it('creates a lead record', function () {
    $company = Company::factory()->create();
    $user = User::factory()->create(['company_id' => $company->id]);
    $form = LeadForm::factory()->create(['company_id' => $company->id]);

    $this->actingAs($user)
        ->post(route('leads.store'), [
            'lead_form_id' => $form->id,
            'name' => 'Lead Created',
            'email' => 'lead@example.com',
            'phone' => '555-0100',
            'source' => 'manual',
            'payload' => [
                'service' => 'Kitchen',
            ],
        ])
        ->assertRedirect(route('leads.index'));

    $this->assertDatabaseHas('lead_records', [
        'lead_form_id' => $form->id,
        'name' => 'Lead Created',
        'email' => 'lead@example.com',
        'source' => 'manual',
    ]);
});

it('updates a lead record', function () {
    $company = Company::factory()->create();
    $user = User::factory()->create(['company_id' => $company->id]);
    $form = LeadForm::factory()->create(['company_id' => $company->id]);
    $lead = LeadRecord::factory()->create(['lead_form_id' => $form->id, 'name' => 'Old Name']);

    $this->actingAs($user)
        ->patch(route('leads.update', $lead->id), [
            'lead_form_id' => $form->id,
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
            'phone' => '555-9999',
            'source' => 'phone_call',
            'payload' => ['budget' => '20k'],
        ])
        ->assertRedirect(route('leads.show', $lead->id));

    $this->assertDatabaseHas('lead_records', [
        'id' => $lead->id,
        'name' => 'Updated Name',
        'email' => 'updated@example.com',
        'source' => 'phone_call',
    ]);
});

it('deletes a lead record', function () {
    $company = Company::factory()->create();
    $user = User::factory()->create(['company_id' => $company->id]);
    $form = LeadForm::factory()->create(['company_id' => $company->id]);
    $lead = LeadRecord::factory()->create(['lead_form_id' => $form->id]);

    $this->actingAs($user)
        ->delete(route('leads.destroy', $lead->id))
        ->assertRedirect(route('leads.index'));

    $this->assertDatabaseMissing('lead_records', ['id' => $lead->id]);
});

it('prevents viewing another company lead', function () {
    $firstCompany = Company::factory()->create();
    $secondCompany = Company::factory()->create();

    $user = User::factory()->create(['company_id' => $firstCompany->id]);
    $secondForm = LeadForm::factory()->create(['company_id' => $secondCompany->id]);
    $lead = LeadRecord::factory()->create(['lead_form_id' => $secondForm->id]);

    $this->actingAs($user)
        ->get(route('leads.show', $lead->id))
        ->assertNotFound();
});

it('prevents creating a lead on another company widget', function () {
    $firstCompany = Company::factory()->create();
    $secondCompany = Company::factory()->create();

    $user = User::factory()->create(['company_id' => $firstCompany->id]);
    $secondForm = LeadForm::factory()->create(['company_id' => $secondCompany->id]);

    $this->actingAs($user)
        ->from(route('leads.create'))
        ->post(route('leads.store'), [
            'lead_form_id' => $secondForm->id,
            'name' => 'Blocked Lead',
            'payload' => [],
        ])
        ->assertSessionHasErrors('lead_form_id');
});
