<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class DirectoryController extends Controller
{
    /**
     * Show the public directory of companies.
     */
    public function index(Request $request): Response
    {
        $query = Company::query()
            ->where('is_active', true)
            ->with(['settings', 'galleries' => function ($q) {
                $q->orderBy('sort_order')->limit(1);
            }, 'services' => function ($q) {
                $q->orderBy('sort_order')->limit(1);
            }]);

        // Filter by text search
        if ($request->filled('q')) {
            $search = $request->input('q');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('industry', 'like', "%{$search}%")
                  ->orWhereHas('services', function ($qServ) use ($search) {
                      $qServ->where('name', 'like', "%{$search}%")
                            ->orWhere('description', 'like', "%{$search}%");
                  });
            });
        }

        // Filter by location (served_areas structure might vary depending on how it's stored.
        // Assuming it's a JSON array since we used Vue arrays for served_areas)
        if ($request->filled('location')) {
            $location = $request->input('location');
            // We use 'like' here instead of whereJsonContains so that partial typing (e.g. "Mon")
            // can match cities like "Montréal" stored within the JSON array.
            $query->where('served_areas', 'like', "%{$location}%");
        }

        // Keep a distinct list of popular cities/locations to help the frontend build a filter menu
        // In a real app this might be cached or retrieved via a separate query
        $allCompanies = Company::where('is_active', true)->get(['served_areas']);
        $allCities = [];
        foreach ($allCompanies as $c) {
            if (is_array($c->served_areas)) {
                foreach ($c->served_areas as $area) {
                    $allCities[] = trim($area);
                }
            }
        }
        $popularLocations = collect($allCities)
            ->countBy()
            ->sortDesc()
            ->keys()
            ->take(15)
            ->values()
            ->all();

        $companies = $query->latest()->paginate(12)->through(function ($company) {
            $coverUrl = null;
            if ($company->galleries->isNotEmpty() && $company->galleries->first()->image_path) {
                $coverUrl = Storage::url($company->galleries->first()->image_path);
            } elseif ($company->services->isNotEmpty() && $company->services->first()->image_path) {
                $coverUrl = Storage::url($company->services->first()->image_path);
            }

            return [
                'id' => $company->id,
                'name' => $company->name,
                'slug' => $company->slug,
                'description' => str()->limit($company->description, 120),
                'industry' => $company->industry,
                'served_areas' => $company->served_areas ?? [],
                'logo_url' => $company->logo_path ? Storage::url($company->logo_path) : null,
                'cover_url' => $coverUrl,
                'primary_color' => $company->primary_color,
            ];
        });

        return Inertia::render('public/Directory', [
            'companies' => $companies,
            'filters' => [
                'q' => $request->input('q', ''),
                'location' => $request->input('location', ''),
            ],
            'popularLocations' => $popularLocations,
        ]);
    }
}
