<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Notifications\LeadReceivedNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class PublicCompanyController extends Controller
{
    /**
     * Show the company landing page.
     */
    public function show(string $slug): Response
    {
        $company = Company::where('slug', $slug)
            ->where('is_active', true)
            ->with(['settings', 'services', 'galleries'])
            ->firstOrFail();

        return Inertia::render('public/Landing', [
            'company' => [
                'name' => $company->name,
                'description' => $company->description,
                'phone' => $company->phone,
                'email' => $company->email,
                'address' => $company->address,
                'website' => $company->website,
                'industry' => $company->industry,
                'served_areas' => $company->served_areas ?? [],
                'primary_color' => $company->primary_color,
                'secondary_color' => $company->secondary_color,
                'logo_url' => $company->logo_path ? Storage::url($company->logo_path) : null,
                'slug' => $company->slug,
            ],
            'settings' => [
                'facebook_url' => $company->settings->facebook_url ?? null,
                'instagram_url' => $company->settings->instagram_url ?? null,
                'seo_title' => $company->settings->seo_title ?? null,
                'seo_description' => $company->settings->seo_description ?? null,
                'seo_keywords' => $company->settings->seo_keywords ?? null,
            ],
            'services' => $company->services()->orderBy('sort_order')->get()->map(function ($s) {
                return [
                    'id' => $s->id,
                    'name' => $s->name,
                    'description' => $s->description,
                    'image_url' => $s->image_path ? Storage::url($s->image_path) : null,
                ];
            }),
            'galleries' => $company->galleries()->orderBy('sort_order')->get()->map(function ($g) {
                return [
                    'id' => $g->id,
                    'image_url' => Storage::url($g->image_path),
                    'caption' => $g->caption,
                ];
            }),
        ]);
    }

    /**
     * Handle contact form submission from the public landing page.
     */
    public function submitContact(Request $request, string $slug): RedirectResponse
    {
        $company = Company::where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:50'],
            'message' => ['nullable', 'string', 'max:2000'],
        ]);

        // Find the first active lead form to attach the record
        /** @var \App\Models\LeadForm|null $leadForm */
        $leadForm = $company->leadForms()->where('is_active', true)->first();

        $record = null;

        if ($leadForm) {
            $record = $leadForm->records()->create([
                'company_id' => $company->id,
                'name' => $validated['name'],
                'email' => $validated['email'] ?? null,
                'phone' => $validated['phone'],
                'payload' => [
                    'name' => $validated['name'],
                    'email' => $validated['email'] ?? null,
                    'phone' => $validated['phone'],
                    'message' => $validated['message'] ?? null,
                ],
                'source' => 'landing_page',
            ]);
        } else {
            // Create a direct lead without a form
            $record = \App\Models\LeadRecord::create([
                'company_id' => $company->id,
                'name' => $validated['name'],
                'email' => $validated['email'] ?? null,
                'phone' => $validated['phone'],
                'payload' => [
                    'name' => $validated['name'],
                    'email' => $validated['email'] ?? null,
                    'phone' => $validated['phone'],
                    'message' => $validated['message'] ?? null,
                ],
                'source' => 'landing_page',
            ]);
        }

        // Notify company users
        if ($record) {
            Notification::send($company->users, new LeadReceivedNotification($record));
        }

        return back()->with('success', 'Message envoyé avec succès !');
    }
}
