<?php

namespace App\Http\Controllers;

use App\Models\LeadNote;
use App\Models\LeadRecord;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;

class LeadNoteController extends Controller
{
    /**
     * Store a newly created note in storage.
     */
    public function store(Request $request, int $lead): RedirectResponse
    {
        $validated = $request->validate([
            'content' => ['required', 'string', 'max:5000'],
            'type' => ['sometimes', 'string', 'in:note,call,email,meeting'],
        ]);

        $record = LeadRecord::query()
            ->whereKey($lead)
            ->whereHas('leadForm', fn ($query) => $query->where('company_id', $request->user()->company_id))
            ->firstOrFail();

        $record->notes()->create([
            'user_id' => $request->user()->id,
            'content' => $validated['content'],
            'type' => $validated['type'] ?? 'note',
        ]);

        return back()->with('success', 'Note ajoutée avec succès.');
    }

    /**
     * Remove the specified note from storage.
     */
    public function destroy(Request $request, int $lead, int $note): RedirectResponse
    {
        $record = LeadRecord::query()
            ->whereKey($lead)
            ->whereHas('leadForm', fn ($query) => $query->where('company_id', $request->user()->company_id))
            ->firstOrFail();

        $leadNote = $record->notes()->whereKey($note)->firstOrFail();

        // Only author or company admin can delete a note (since no fine-grained role, we just let company users delete it)
        $leadNote->delete();

        return back()->with('success', 'Note supprimée.');
    }
}
