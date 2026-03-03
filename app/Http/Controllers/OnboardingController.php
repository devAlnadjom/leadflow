<?php

namespace App\Http\Controllers;

use App\Http\Requests\Onboarding\StoreCompanyProfileRequest;
use App\Models\Company;
use App\Models\CompanySetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class OnboardingController extends Controller
{
    /**
     * Show the company onboarding form.
     */
    public function create(): Response|RedirectResponse
    {
        if (request()->user()->hasCompletedOnboarding()) {
            return to_route('dashboard');
        }

        return Inertia::render('onboarding/CompanyProfile', [
            'defaults' => [
                'email' => request()->user()->email,
                'currency' => 'USD',
                'quote_prefix' => 'QUO',
                'invoice_prefix' => 'INV',
            ],
        ]);
    }

    /**
     * Store the company profile and complete onboarding.
     */
    public function store(StoreCompanyProfileRequest $request): RedirectResponse
    {
        if ($request->user()->hasCompletedOnboarding()) {
            return to_route('dashboard');
        }

        $validated = $request->validated();

        DB::transaction(function () use ($request, $validated): void {
            $company = Company::create([
                'name' => $validated['name'],
                'phone' => $validated['phone'] ?? null,
                'email' => $validated['email'] ?? null,
                'industry' => $validated['industry'] ?? null,
                'address' => $validated['address'] ?? null,
                'served_areas' => $validated['served_areas'] ?? [],
                'primary_color' => $validated['primary_color'] ?? null,
                'secondary_color' => $validated['secondary_color'] ?? null,
            ]);

            CompanySetting::create([
                'company_id' => $company->id,
                'quote_prefix' => strtoupper($validated['quote_prefix']),
                'invoice_prefix' => strtoupper($validated['invoice_prefix']),
                'default_tax_rate' => $validated['default_tax_rate'] ?? null,
                'currency' => strtoupper($validated['currency']),
                'terms_and_conditions' => $validated['terms_and_conditions'] ?? null,
            ]);

            $request->user()->forceFill([
                'company_id' => $company->id,
            ])->save();
        });

        return to_route('dashboard');
    }
}
