<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class TagController extends Controller
{
    /**
     * Display a listing of tags as JSON.
     */
    public function index(Request $request): JsonResponse
    {
        $tags = Tag::query()
            ->orderBy('name')
            ->get(['id', 'name', 'color']);

        return response()->json($tags);
    }

    /**
     * Store a newly created tag in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50',
            'color' => 'nullable|string|max:20',
        ]);

        $tag = Tag::query()->firstOrCreate(
            ['name' => $validated['name']],
            ['color' => $validated['color'] ?? '#6366f1'] // Default to indigo
        );

        return response()->json($tag);
    }

    /**
     * Sync tags for a given model.
     */
    public function sync(Request $request): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'taggable_id' => 'required|integer',
            'taggable_type' => 'required|string|in:lead,client',
            'tags' => 'array',
        ]);

        $modelClass = $validated['taggable_type'] === 'lead' ? \App\Models\LeadRecord::class : \App\Models\Client::class;
        $model = $modelClass::findOrFail($validated['taggable_id']);
        
        if ($model->company_id !== $request->user()->company_id) {
            abort(403);
        }

        $tagIds = [];
        $colors = ['#f87171', '#fb923c', '#fbbf24', '#a3e635', '#4ade80', '#34d399', '#2dd4bf', '#38bdf8', '#818cf8', '#a78bfa', '#e879f9', '#fb7185'];

        foreach ($validated['tags'] ?? [] as $tagIdentifier) {
            if (is_numeric($tagIdentifier)) {
                $tagIds[] = (int) $tagIdentifier;
            } else {
                // Determine a random color if created
                $color = $colors[array_rand($colors)];
                $tag = Tag::query()->firstOrCreate(
                    ['company_id' => $request->user()->company_id, 'name' => trim($tagIdentifier)],
                    ['color' => $color]
                );
                $tagIds[] = $tag->id;
            }
        }

        $model->tags()->sync($tagIds);

        return back();
    }
}
