<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\CompanySettingsUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class CompanySettingsController extends Controller
{
    /**
     * Show the company settings page.
     */
    public function edit(): Response|RedirectResponse
    {
        $user = request()->user();

        if ($user->company === null) {
            return to_route('onboarding.company.create');
        }

        return Inertia::render('settings/Company', [
            'company' => $user->company->only([
                'name',
                'phone',
                'email',
                'industry',
                'address',
                'primary_color',
                'secondary_color',
            ]),
            'servedAreas' => $user->company->served_areas ?? [],
            'settings' => $user->company->settings?->only([
                'quote_prefix',
                'invoice_prefix',
                'default_tax_rate',
                'currency',
                'terms_and_conditions',
            ]) ?? [
                'quote_prefix' => 'QUO',
                'invoice_prefix' => 'INV',
                'default_tax_rate' => null,
                'currency' => 'USD',
                'terms_and_conditions' => null,
            ],
        ]);
    }

    /**
     * Update company settings.
     */
    public function update(CompanySettingsUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->company === null) {
            return to_route('onboarding.company.create');
        }

        $validated = $request->validated();

        $user->company->update([
            'name' => $validated['name'],
            'phone' => $validated['phone'] ?? null,
            'email' => $validated['email'] ?? null,
            'industry' => $validated['industry'] ?? null,
            'address' => $validated['address'] ?? null,
            'served_areas' => $validated['served_areas'] ?? [],
            'primary_color' => $validated['primary_color'] ?? null,
            'secondary_color' => $validated['secondary_color'] ?? null,
        ]);

        $user->company->settings()->updateOrCreate(
            ['company_id' => $user->company->id],
            [
                'quote_prefix' => strtoupper($validated['quote_prefix']),
                'invoice_prefix' => strtoupper($validated['invoice_prefix']),
                'default_tax_rate' => $validated['default_tax_rate'] ?? null,
                'currency' => strtoupper($validated['currency']),
                'terms_and_conditions' => $validated['terms_and_conditions'] ?? null,
            ],
        );

        return to_route('company-settings.edit');
    }
}
