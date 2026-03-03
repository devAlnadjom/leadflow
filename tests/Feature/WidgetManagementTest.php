<?php

use App\Models\Company;
use App\Models\LeadForm;
use App\Models\LeadRecord;
use App\Models\User;

it('shows widgets index for an onboarded user', function () {
    $company = Company::factory()->create();
    $user = User::factory()->create(['company_id' => $company->id]);

    LeadForm::factory()->create([
        'company_id' => $company->id,
        'name' => 'Bathroom Renovation Widget',
    ]);

    $this->withoutVite();

    $this->actingAs($user)
        ->get(route('widgets.index'))
        ->assertOk();
});

it('shows widget dashboard with preview and records', function () {
    $company = Company::factory()->create();
    $user = User::factory()->create(['company_id' => $company->id]);

    $leadForm = LeadForm::factory()->create([
        'company_id' => $company->id,
        'name' => 'Bathroom Widget',
    ]);

    LeadRecord::factory()->create([
        'lead_form_id' => $leadForm->id,
        'name' => 'John Client',
        'email' => 'john@example.com',
    ]);

    $this->withoutVite();

    $this->actingAs($user)
        ->get(route('widgets.show', $leadForm->id))
        ->assertOk()
        ->assertSee('Bathroom Widget')
        ->assertSee('John Client');
});

it('shows public blade preview page using uid query parameter', function () {
    $leadForm = LeadForm::factory()->create([
        'embed_key' => 'widget-key-123',
        'name' => 'Integration Widget',
    ]);

    $this->get(route('widgets.preview', ['uid' => $leadForm->embed_key]))
        ->assertOk()
        ->assertSee('Integration Widget')
        ->assertSee('widget-key-123')
        ->assertSee('/widget.js', false);
});

it('creates a widget with fields', function () {
    $company = Company::factory()->create();
    $user = User::factory()->create(['company_id' => $company->id]);

    $response = $this->actingAs($user)
        ->post(route('widgets.store'), [
            'name' => 'Main Website Widget',
            'is_active' => true,
            'submit_button_label' => 'Get quote',
            'fields' => [
                [
                    'label' => 'Full Name',
                    'type' => 'text',
                    'required' => true,
                    'placeholder' => 'John Doe',
                    'options' => [],
                ],
                [
                    'label' => 'Phone',
                    'type' => 'tel',
                    'required' => true,
                    'placeholder' => '',
                    'options' => [],
                ],
            ],
        ]);

    $response->assertRedirect(route('widgets.index'));

    $leadForm = LeadForm::query()->where('company_id', $company->id)->first();

    expect($leadForm)->not->toBeNull();
    expect($leadForm->embed_key)->not->toBeEmpty();

    $this->assertDatabaseCount('lead_form_fields', 2);
});

it('validates that a contact field exists', function () {
    $company = Company::factory()->create();
    $user = User::factory()->create(['company_id' => $company->id]);

    $this->actingAs($user)
        ->from(route('widgets.create'))
        ->post(route('widgets.store'), [
            'name' => 'Invalid Widget',
            'fields' => [
                [
                    'label' => 'Name',
                    'type' => 'text',
                    'required' => true,
                    'placeholder' => '',
                    'options' => [],
                ],
                [
                    'label' => 'Service',
                    'type' => 'text',
                    'required' => false,
                    'placeholder' => '',
                    'options' => [],
                ],
            ],
        ])
        ->assertSessionHasErrors('fields');
});

it('updates an existing widget', function () {
    $company = Company::factory()->create();
    $user = User::factory()->create(['company_id' => $company->id]);

    $leadForm = LeadForm::factory()->create([
        'company_id' => $company->id,
        'name' => 'Old Widget',
    ]);

    $response = $this->actingAs($user)
        ->patch(route('widgets.update', $leadForm->id), [
            'name' => 'Updated Widget',
            'is_active' => false,
            'submit_button_label' => 'Send now',
            'fields' => [
                [
                    'label' => 'Nom',
                    'type' => 'text',
                    'required' => true,
                    'placeholder' => '',
                    'options' => [],
                ],
                [
                    'label' => 'Email',
                    'type' => 'email',
                    'required' => true,
                    'placeholder' => '',
                    'options' => [],
                ],
            ],
        ]);

    $response->assertRedirect(route('widgets.index'));

    $this->assertDatabaseHas('lead_forms', [
        'id' => $leadForm->id,
        'name' => 'Updated Widget',
        'is_active' => false,
    ]);
});

it('prevents editing another company widget', function () {
    $firstCompany = Company::factory()->create();
    $secondCompany = Company::factory()->create();

    $user = User::factory()->create(['company_id' => $firstCompany->id]);
    $leadForm = LeadForm::factory()->create(['company_id' => $secondCompany->id]);

    $this->actingAs($user)
        ->get(route('widgets.edit', $leadForm->id))
        ->assertNotFound();
});

it('prevents viewing another company widget dashboard', function () {
    $firstCompany = Company::factory()->create();
    $secondCompany = Company::factory()->create();

    $user = User::factory()->create(['company_id' => $firstCompany->id]);
    $leadForm = LeadForm::factory()->create(['company_id' => $secondCompany->id]);

    $this->actingAs($user)
        ->get(route('widgets.show', $leadForm->id))
        ->assertNotFound();
});

it('returns not found for invalid preview uid', function () {
    $this->get(route('widgets.preview', ['uid' => 'unknown-key']))
        ->assertNotFound();
});

it('stores a preview submission into lead records', function () {
    $leadForm = LeadForm::factory()->create([
        'embed_key' => 'widget-submit-123',
    ]);

    $leadForm->fields()->createMany([
        [
            'label' => 'Full Name',
            'field_key' => 'full_name_0',
            'type' => 'text',
            'is_required' => true,
            'sort_order' => 0,
        ],
        [
            'label' => 'Email',
            'field_key' => 'email_1',
            'type' => 'email',
            'is_required' => true,
            'sort_order' => 1,
        ],
    ]);

    $this->post(route('widgets.preview.submit'), [
        'uid' => $leadForm->embed_key,
        'full_name_0' => 'Jane Doe',
        'email_1' => 'jane@example.com',
    ])
        ->assertRedirect(route('widgets.preview', ['uid' => $leadForm->embed_key]));

    $this->assertDatabaseHas('lead_records', [
        'lead_form_id' => $leadForm->id,
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'source' => 'widget_preview',
    ]);
});

it('validates required preview submission fields', function () {
    $leadForm = LeadForm::factory()->create([
        'embed_key' => 'widget-submit-124',
    ]);

    $leadForm->fields()->createMany([
        [
            'label' => 'Full Name',
            'field_key' => 'full_name_0',
            'type' => 'text',
            'is_required' => true,
            'sort_order' => 0,
        ],
        [
            'label' => 'Email',
            'field_key' => 'email_1',
            'type' => 'email',
            'is_required' => true,
            'sort_order' => 1,
        ],
    ]);

    $this->from(route('widgets.preview', ['uid' => $leadForm->embed_key]))
        ->post(route('widgets.preview.submit'), [
            'uid' => $leadForm->embed_key,
            'full_name_0' => 'Jane Doe',
            'email_1' => 'not-an-email',
        ])
        ->assertSessionHasErrors('email_1');
});

it('deletes a widget', function () {
    $company = Company::factory()->create();
    $user = User::factory()->create(['company_id' => $company->id]);

    $leadForm = LeadForm::factory()->create(['company_id' => $company->id]);

    $this->actingAs($user)
        ->delete(route('widgets.destroy', $leadForm->id))
        ->assertRedirect(route('widgets.index'));

    $this->assertDatabaseMissing('lead_forms', ['id' => $leadForm->id]);
});
