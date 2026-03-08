<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Client;
use App\Models\ClientNote;

class ClientNoteController extends Controller
{
    /**
     * Store a newly created note in storage.
     */
    public function store(Request $request, int $client)
    {
        $company = $request->user()->company;
        $record = Client::where('company_id', $company->id)->findOrFail($client);
        
        $validated = $request->validate([
            'content' => 'required|string',
            'type' => 'nullable|string|in:note,email,call,meeting,task',
        ]);

        $record->notes()->create([
            'company_id' => $company->id,
            'user_id' => $request->user()->id,
            'content' => $validated['content'],
            'type' => $validated['type'] ?? 'note',
        ]);

        return back()->with('success', 'La note a été ajoutée au dossier client.');
    }

    /**
     * Remove the specified note from storage.
     */
    public function destroy(Request $request, int $client, int $note)
    {
        $company = $request->user()->company;
        $record = Client::where('company_id', $company->id)->findOrFail($client);
        
        $noteRecord = $record->notes()->findOrFail($note);
        $noteRecord->delete();

        return back()->with('success', 'La note interne a été supprimée.');
    }
}
