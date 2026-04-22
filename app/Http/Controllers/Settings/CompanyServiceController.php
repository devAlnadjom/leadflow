<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\CompanyService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CompanyServiceController extends Controller
{
    /**
     * Store a newly created service in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'image' => ['nullable', 'image', 'max:2048'], // 2MB max
        ]);

        $company = $request->user()->company;

        $serviceData = [
            'company_id' => $company->id,
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'sort_order' => $company->services()->count(),
        ];

        if ($request->hasFile('image')) {
            $serviceData['image_path'] = $request->file('image')->store('company_services', 'public');
        }

        CompanyService::create($serviceData);

        return back()->with('success', 'Service added.');
    }

    /**
     * Remove the specified service from storage.
     */
    public function destroy(CompanyService $companyService): RedirectResponse
    {
        // Ensure the company owns the service
        if ($companyService->company_id !== auth()->user()->company_id) {
            abort(403);
        }

        if ($companyService->image_path) {
            Storage::disk('public')->delete($companyService->image_path);
        }

        $companyService->delete();

        return back()->with('success', 'Service removed.');
    }
}
