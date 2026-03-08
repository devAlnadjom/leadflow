<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Client;

class ClientController extends Controller
{
    /**
     * Display a listing of clients for the authenticated company.
     */
    public function index(Request $request)
    {
        $company = $request->user()->company;
        $search = trim((string) $request->query('search', ''));

        $clientsQuery = Client::query()
            ->withCount(['leads', 'quotes'])
            ->where('company_id', $company->id)
            ->latest();

        if ($search !== '') {
            $clientsQuery->where(function ($query) use ($search) {
                $query->where('name', 'like', '%'.$search.'%')
                    ->orWhere('email', 'like', '%'.$search.'%')
                    ->orWhere('company_name', 'like', '%'.$search.'%');
            });
        }

        $clients = $clientsQuery->paginate(15)->withQueryString()->through(fn ($client) => [
            'id' => $client->id,
            'name' => $client->name,
            'email' => $client->email,
            'phone' => $client->phone,
            'company_name' => $client->company_name,
            'leads_count' => $client->leads_count,
            'quotes_count' => $client->quotes_count,
            'created_at' => $client->created_at?->toDateTimeString(),
        ]);

        return Inertia::render('clients/Index', [
            'clients' => $clients,
            'filters' => [
                'search' => $search,
            ],
        ]);
    }

    /**
     * Display the specified client.
     */
    public function show(Request $request, int $client)
    {
        $company = $request->user()->company;
        $record = Client::where('company_id', $company->id)->findOrFail($client);
        
        $record->load(['leads' => function ($query) {
            $query->latest();
        }, 'quotes' => function ($query) {
            $query->latest();
        }, 'notes.user']);

        return Inertia::render('clients/Show', [
            'client' => [
                'id' => $record->id,
                'name' => $record->name,
                'email' => $record->email,
                'phone' => $record->phone,
                'company_name' => $record->company_name,
                'address' => $record->address,
                'city' => $record->city,
                'postal_code' => $record->postal_code,
                'tax_number' => $record->tax_number,
                'created_at' => $record->created_at?->toDateTimeString(),
                'leads' => $record->leads->map(fn ($lead) => [
                    'id' => $lead->id,
                    'name' => $lead->name,
                    'status' => $lead->status,
                    'created_at' => $lead->created_at?->toDateTimeString(),
                ]),
                'quotes' => $record->quotes->map(fn ($quote) => [
                    'id' => $quote->id,
                    'quote_number' => $quote->quote_number,
                    'public_uid' => $quote->public_uid,
                    'status' => $quote->status,
                    'total' => $quote->total,
                    'expire_at' => $quote->expire_at?->toDateString(),
                    'created_at' => $quote->created_at?->toDateTimeString(),
                ]),
                'notes' => $record->notes->sortByDesc('created_at')->values()->map(fn ($note) => [
                    'id' => $note->id,
                    'content' => $note->content,
                    'type' => $note->type,
                    'author' => $note->user?->name ?? 'Système',
                    'created_at' => $note->created_at?->toDateTimeString(),
                ]),
            ],
        ]);
    }

    /**
     * Update the specified client in storage.
     */
    public function update(Request $request, int $client)
    {
        $company = $request->user()->company;
        $record = Client::where('company_id', $company->id)->findOrFail($client);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'postal_code' => 'nullable|string|max:255',
            'tax_number' => 'nullable|string|max:255',
        ]);

        $record->update($validated);

        return back()->with('success', 'Les informations du client ont été mises à jour.');
    }

    /**
     * Remove the specified client from storage.
     */
    public function destroy(Request $request, int $client)
    {
        $company = $request->user()->company;
        $record = Client::where('company_id', $company->id)->findOrFail($client);
        
        // Let's just unlink leads & quotes instead of cascade delete for now (safety)
        $record->leads()->update(['client_id' => null]);
        $record->quotes()->update(['client_id' => null]);

        $record->delete();

        return redirect()->route('clients.index')->with('success', 'Le client a été supprimé.');
    }
}
