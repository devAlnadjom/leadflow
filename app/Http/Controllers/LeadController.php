<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLeadRecordRequest;
use App\Http\Requests\UpdateLeadRecordRequest;
use App\Models\LeadRecord;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LeadController extends Controller
{
    /**
     * Display a listing of leads for the authenticated company.
     */
    public function index(Request $request): Response
    {
        $company = $request->user()->company;
        $search = trim((string) $request->query('search', ''));
        $leadFormId = (int) $request->query('lead_form_id', 0);

        $recordsQuery = LeadRecord::query()
            ->with('leadForm:id,name,company_id')
            ->whereHas('leadForm', fn ($query) => $query->where('company_id', $company->id))
            ->latest();

        if ($search !== '') {
            $recordsQuery->where(function ($query) use ($search): void {
                $query->where('name', 'like', '%'.$search.'%')
                    ->orWhere('email', 'like', '%'.$search.'%')
                    ->orWhere('phone', 'like', '%'.$search.'%')
                    ->orWhere('source', 'like', '%'.$search.'%');
            });
        }

        if ($leadFormId > 0) {
            $recordsQuery->where('lead_form_id', $leadFormId);
        }

        $records = $recordsQuery
            ->paginate(20)
            ->withQueryString()
            ->through(fn (LeadRecord $record): array => [
                'id' => $record->id,
                'lead_form_id' => $record->lead_form_id,
                'lead_form_name' => $record->leadForm?->name ?? '-',
                'name' => $record->name,
                'email' => $record->email,
                'phone' => $record->phone,
                'source' => $record->source,
                'status' => $record->status ?? 'new',
                'value' => $record->value ?? 0,
                'created_at' => $record->created_at?->toDateTimeString(),
            ]);

        return Inertia::render('leads/Index', [
            'leads' => $records,
            'filters' => [
                'search' => $search,
                'lead_form_id' => $leadFormId > 0 ? $leadFormId : null,
            ],
            'leadForms' => $company->leadForms()
                ->orderBy('name')
                ->get(['id', 'name'])
                ->map(fn ($form): array => [
                    'id' => $form->id,
                    'name' => $form->name,
                ]),
        ]);
    }

    /**
     * Show the form for creating a new lead.
     */
    public function create(Request $request): Response
    {
        $company = $request->user()->company;

        return Inertia::render('leads/Create', [
            'leadForms' => $company->leadForms()
                ->orderBy('name')
                ->get(['id', 'name'])
                ->map(fn ($form): array => [
                    'id' => $form->id,
                    'name' => $form->name,
                ]),
        ]);
    }

    /**
     * Store a newly created lead.
     */
    public function store(StoreLeadRecordRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        LeadRecord::query()->create([
            'lead_form_id' => (int) $validated['lead_form_id'],
            'name' => $validated['name'] ?? null,
            'email' => $validated['email'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'source' => $validated['source'] ?? 'manual',
            'payload' => $validated['payload'] ?? [],
        ]);

        return to_route('leads.index');
    }

    /**
     * Display the specified lead.
     */
    public function show(Request $request, int $lead): Response
    {
        $record = $this->resolveCompanyLead($request, $lead);
        $record->load(['notes.user:id,name', 'quotes']);

        return Inertia::render('leads/Show', [
            'lead' => [
                'id' => $record->id,
                'client_id' => $record->client_id,
                'lead_form_id' => $record->lead_form_id,
                'lead_form_name' => $record->leadForm?->name ?? '-',
                'name' => $record->name,
                'email' => $record->email,
                'phone' => $record->phone,
                'source' => $record->source,
                'status' => $record->status ?? 'new',
                'value' => $record->value ?? 0,
                'payload' => $record->payload ?? [],
                'created_at' => $record->created_at?->toDateTimeString(),
                'updated_at' => $record->updated_at?->toDateTimeString(),
                'notes' => $record->notes->map(fn ($note) => [
                    'id' => $note->id,
                    'content' => $note->content,
                    'type' => $note->type,
                    'author' => $note->user?->name ?? 'Système',
                    'created_at' => $note->created_at?->toDateTimeString(),
                ]),
                'quotes' => $record->quotes->map(fn ($quote) => [
                    'id' => $quote->id,
                    'public_uid' => $quote->public_uid,
                    'quote_number' => $quote->quote_number,
                    'status' => $quote->status,
                    'total' => $quote->total,
                    'expire_at' => $quote->expire_at?->toDateString(),
                    'created_at' => $quote->created_at?->toDateTimeString(),
                ]),
            ],
        ]);
    }

    /**
     * Show the form for editing the specified lead.
     */
    public function edit(Request $request, int $lead): Response
    {
        $company = $request->user()->company;
        $record = $this->resolveCompanyLead($request, $lead);

        return Inertia::render('leads/Edit', [
            'lead' => [
                'id' => $record->id,
                'lead_form_id' => $record->lead_form_id,
                'name' => $record->name ?? '',
                'email' => $record->email ?? '',
                'phone' => $record->phone ?? '',
                'source' => $record->source,
                'payload' => $record->payload ?? [],
            ],
            'leadForms' => $company->leadForms()
                ->orderBy('name')
                ->get(['id', 'name'])
                ->map(fn ($form): array => [
                    'id' => $form->id,
                    'name' => $form->name,
                ]),
        ]);
    }

    /**
     * Update the specified lead.
     */
    public function update(UpdateLeadRecordRequest $request, int $lead): RedirectResponse
    {
        $record = $this->resolveCompanyLead($request, $lead);
        $validated = $request->validated();

        $record->update([
            'lead_form_id' => (int) $validated['lead_form_id'],
            'name' => $validated['name'] ?? null,
            'email' => $validated['email'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'source' => $validated['source'] ?? 'manual',
            'payload' => $validated['payload'] ?? [],
        ]);

        return to_route('leads.show', $record->id);
    }

    /**
     * Remove the specified lead.
     */
    public function destroy(Request $request, int $lead): RedirectResponse
    {
        $record = $this->resolveCompanyLead($request, $lead);
        $record->delete();

        return to_route('leads.index');
    }

    /**
     * Resolve a lead record scoped to the authenticated company.
     */
    private function resolveCompanyLead(Request $request, int $leadId): LeadRecord
    {
        return LeadRecord::query()
            ->with('leadForm:id,name,company_id')
            ->whereKey($leadId)
            ->whereHas('leadForm', fn ($query) => $query->where('company_id', $request->user()->company_id))
            ->firstOrFail();
    }

    /**
     * Update the status of a lead via API/Kanban.
     */
    public function updateStatus(Request $request, int $lead): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'string', 'in:new,contacted,qualified,won,lost'],
        ]);

        $record = LeadRecord::query()
            ->whereKey($lead)
            ->whereHas('leadForm', fn ($query) => $query->where('company_id', $request->user()->company_id))
            ->firstOrFail();

        $record->update(['status' => $validated['status']]);

        return back();
    }

    /**
     * Convert a lead to a client.
     */
    public function convert(Request $request, int $lead): RedirectResponse
    {
        $record = LeadRecord::query()
            ->with(['quotes', 'leadForm:id,name,company_id'])
            ->whereKey($lead)
            ->whereHas('leadForm', fn ($query) => $query->where('company_id', $request->user()->company_id))
            ->firstOrFail();

        if ($record->client_id) {
            return back()->with('error', 'Ce prospect est déjà rattaché à un client.');
        }

        if (!$record->name) {
            return back()->with('error', 'Le prospect doit avoir au moins un nom pour être converti en client.');
        }

        // Create the new client
        $client = $request->user()->company->clients()->create([
            'name' => $record->name,
            'email' => $record->email,
            'phone' => $record->phone,
            'company_name' => $record->payload['company'] ?? null,
        ]);

        // Link the lead and its quotes to the client
        $record->update([
            'client_id' => $client->id,
            'status' => 'won' // Automatically win the lead
        ]);

        if ($record->quotes->count() > 0) {
            foreach ($record->quotes as $quote) {
                $quote->update(['client_id' => $client->id]);
            }
        }

        // Add a note
        $record->notes()->create([
            'user_id' => $request->user()->id,
            'type' => 'note',
            'content' => "Prospect converti en Client.",
        ]);

        return back()->with('success', 'Prospect converti en client avec succès !');
    }
}
