<?php

namespace App\Http\Controllers\Settings;

use Illuminate\Support\Facades\Storage;

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
            'company' => array_merge($user->company->only([
                'name',
                'description',
                'phone',
                'email',
                'website',
                'industry',
                'address',
                'primary_color',
                'secondary_color',
            ]), [
                'logo_url' => $user->company->logo_path ? Storage::url($user->company->logo_path) : null,
            ]),
            'servedAreas' => $user->company->served_areas ?? [],
            'settings' => $user->company->settings?->only([
                'quote_prefix',
                'invoice_prefix',
                'tax1_name',
                'tax1_rate',
                'tax2_name',
                'tax2_rate',
                'currency',
                'terms_and_conditions',
            ]) ?? [
                'quote_prefix' => 'QUO',
                'invoice_prefix' => 'INV',
                'tax1_name' => 'TPS',
                'tax1_rate' => 5.000,
                'tax2_name' => 'TVQ',
                'tax2_rate' => 9.975,
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

        $companyData = [
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'email' => $validated['email'] ?? null,
            'website' => $validated['website'] ?? null,
            'industry' => $validated['industry'] ?? null,
            'address' => $validated['address'] ?? null,
            'served_areas' => $validated['served_areas'] ?? [],
            'primary_color' => $validated['primary_color'] ?? null,
            'secondary_color' => $validated['secondary_color'] ?? null,
        ];

        if ($request->hasFile('logo')) {
            // Delete old logo if exist
            if ($user->company->logo_path) {
                Storage::disk('public')->delete($user->company->logo_path);
            }
            $companyData['logo_path'] = $request->file('logo')->store('company_logos', 'public');
        }

        $user->company->update($companyData);

        $user->company->settings()->updateOrCreate(
            ['company_id' => $user->company->id],
            [
                'quote_prefix' => strtoupper($validated['quote_prefix']),
                'invoice_prefix' => strtoupper($validated['invoice_prefix']),
                'tax1_name' => $validated['tax1_name'] ?? 'TPS',
                'tax1_rate' => $validated['tax1_rate'] ?? 5.000,
                'tax2_name' => $validated['tax2_name'] ?? 'TVQ',
                'tax2_rate' => $validated['tax2_rate'] ?? 9.975,
                'currency' => strtoupper($validated['currency']),
                'terms_and_conditions' => $validated['terms_and_conditions'] ?? null,
                'legal_mentions' => $validated['legal_mentions'] ?? [],
            ],
        );

        return to_route('company-settings.edit');
    }
}
