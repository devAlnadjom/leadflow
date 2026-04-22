<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\CompanyGallery;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CompanyGalleryController extends Controller
{
    /**
     * Store a newly created photo in the gallery.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'image' => ['required', 'image', 'max:5120'], // 5MB max
            'caption' => ['nullable', 'string', 'max:255'],
        ]);

        $company = $request->user()->company;

        $photoData = [
            'company_id' => $company->id,
            'caption' => $validated['caption'] ?? null,
            'sort_order' => $company->galleries()->count(),
            'image_path' => $request->file('image')->store('company_gallery', 'public'),
        ];

        CompanyGallery::create($photoData);

        return back()->with('success', 'Photo added to gallery.');
    }

    /**
     * Remove the specified photo from gallery.
     */
    public function destroy(CompanyGallery $companyGallery): RedirectResponse
    {
        // Ensure the company owns the photo
        if ($companyGallery->company_id !== auth()->user()->company_id) {
            abort(403);
        }

        if ($companyGallery->image_path) {
            Storage::disk('public')->delete($companyGallery->image_path);
        }

        $companyGallery->delete();

        return back()->with('success', 'Photo removed from gallery.');
    }
}
