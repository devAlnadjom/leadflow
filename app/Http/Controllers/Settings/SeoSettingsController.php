<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SeoSettingsController extends Controller
{
    /**
     * Show the SEO settings page.
     */
    public function edit(Request $request): Response
    {
        $company = $request->user()->company;
        $settings = $company->settings;

        return Inertia::render('settings/Seo', [
            'settings' => [
                'facebook_url' => $settings->facebook_url ?? null,
                'instagram_url' => $settings->instagram_url ?? null,
                'seo_title' => $settings->seo_title ?? null,
                'seo_description' => $settings->seo_description ?? null,
                'seo_keywords' => $settings->seo_keywords ?? null,
            ],
            'services' => $company->services()->orderBy('sort_order')->get(),
            'galleries' => $company->galleries()->orderBy('sort_order')->get(),
            'companySlug' => $company->slug,
        ]);
    }

    /**
     * Update SEO settings.
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'facebook_url' => ['nullable', 'url', 'max:255'],
            'instagram_url' => ['nullable', 'url', 'max:255'],
            'seo_title' => ['nullable', 'string', 'max:255'],
            'seo_description' => ['nullable', 'string', 'max:1000'],
            'seo_keywords' => ['nullable', 'string', 'max:1000'],
        ]);

        $request->user()->company->settings()->update($validated);

        return back()->with('success', 'SEO settings updated.');
    }
}
