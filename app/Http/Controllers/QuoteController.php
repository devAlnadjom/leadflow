<?php

namespace App\Http\Controllers;

use App\Models\LeadRecord;
use App\Models\Quote;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\QuoteSentMail;

class QuoteController extends Controller
{
    public function index(Request $request): Response
    {
        $search = trim((string) $request->query('search', ''));
        $status = (string) $request->query('status', '');

        $query = Quote::with('leadRecord:id,name,email')
            ->latest();

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('quote_number', 'like', '%' . $search . '%')
                  ->orWhereHas('leadRecord', fn ($lr) => $lr->where('name', 'like', '%' . $search . '%')
                      ->orWhere('email', 'like', '%' . $search . '%'));
            });
        }

        if ($status !== '') {
            $query->where('status', $status);
        }

        $quotes = $query
            ->paginate(20)
            ->withQueryString()
            ->through(fn (Quote $quote) => [
                'id' => $quote->id,
                'public_uid' => $quote->public_uid,
                'quote_number' => $quote->quote_number,
                'status' => $quote->status,
                'total' => $quote->total,
                'expire_at' => $quote->expire_at?->toDateString(),
                'created_at' => $quote->created_at?->toDateTimeString(),
                'lead_name' => $quote->leadRecord?->name ?? '-',
                'lead_email' => $quote->leadRecord?->email ?? '-',
                'lead_id' => $quote->leadRecord?->id,
            ]);

        return Inertia::render('quotes/Index', [
            'quotes' => $quotes,
            'filters' => [
                'search' => $search,
                'status' => $status,
            ],
        ]);
    }

    public function create(Request $request, int $leadId): Response
    {
        $lead = LeadRecord::query()
            ->whereKey($leadId)
            ->firstOrFail();

        $company = $request->user()->company;
        $settings = $company->settings;

        // Suggest a quote number based on existing count
        $count = Quote::count();
        $suggestedNumber = ($settings->quote_prefix ?? 'DEV-') . (1000 + $count + 1);

        return Inertia::render('quotes/Create', [
            'lead' => [
                'id' => $lead->id,
                'name' => $lead->name,
                'email' => $lead->email,
            ],
            'settings' => $settings,
            'suggestedNumber' => $suggestedNumber,
        ]);
    }

    public function store(Request $request, int $leadId)
    {
        $validated = $request->validate([
            'quote_number' => ['required', 'string', 'max:255'],
            'status' => ['required', 'string', 'in:draft,sent,accepted,rejected'],
            'expire_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
            'terms' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.description' => ['required', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'numeric', 'min:0'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'tax1_rate' => ['nullable', 'numeric'],
            'tax2_rate' => ['nullable', 'numeric'],
        ]);

        $lead = LeadRecord::whereKey($leadId)->firstOrFail();

        return DB::transaction(function () use ($validated, $lead, $request) {
            $subtotal = 0;
            foreach ($validated['items'] as $item) {
                $subtotal += $item['quantity'] * $item['unit_price'];
            }

            $tax1Amount = $subtotal * (($validated['tax1_rate'] ?? 0) / 100);
            $tax2Amount = $subtotal * (($validated['tax2_rate'] ?? 0) / 100);
            $total = $subtotal + $tax1Amount + $tax2Amount;

            $quote = Quote::create([
                'lead_record_id' => $lead->id,
                'quote_number' => $validated['quote_number'],
                'status' => $validated['status'],
                'expire_at' => $validated['expire_at'],
                'notes' => $validated['notes'],
                'terms' => $validated['terms'],
                'subtotal' => $subtotal,
                'tax1_amount' => $tax1Amount,
                'tax2_amount' => $tax2Amount,
                'total' => $total,
                'company_id' => $request->user()->company_id,
            ]);

            foreach ($validated['items'] as $index => $itemData) {
                $quote->items()->create([
                    'description' => $itemData['description'],
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $itemData['unit_price'],
                    'total' => $itemData['quantity'] * $itemData['unit_price'],
                    'sort_order' => $index,
                ]);
            }

            return to_route('leads.show', $lead->id);
        });
    }

    public function edit(Request $request, int $quoteId): Response
    {
        $quote = Quote::with('items', 'leadRecord')->whereKey($quoteId)->firstOrFail();

        $company = $request->user()->company;
        $settings = $company->settings;

        return Inertia::render('quotes/Edit', [
            'quote' => [
                'id' => $quote->id,
                'public_uid' => $quote->public_uid,
                'quote_number' => $quote->quote_number,
                'status' => $quote->status,
                'subtotal' => $quote->subtotal,
                'tax1_amount' => $quote->tax1_amount,
                'tax2_amount' => $quote->tax2_amount,
                'total' => $quote->total,
                'expire_at' => $quote->expire_at?->toDateString(),
                'notes' => $quote->notes,
                'terms' => $quote->terms,
                'items' => $quote->items->map(fn ($item) => [
                    'id' => $item->id,
                    'description' => $item->description,
                    'quantity' => (float) $item->quantity,
                    'unit_price' => (float) $item->unit_price,
                ]),
            ],
            'lead' => [
                'id' => $quote->leadRecord->id,
                'name' => $quote->leadRecord->name,
                'email' => $quote->leadRecord->email,
            ],
            'settings' => $settings,
        ]);
    }

    public function update(Request $request, int $quoteId)
    {
        $validated = $request->validate([
            'quote_number' => ['required', 'string', 'max:255'],
            'status' => ['required', 'string', 'in:draft,sent,accepted,rejected,expired'],
            'expire_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
            'terms' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.description' => ['required', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'numeric', 'min:0'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'tax1_rate' => ['nullable', 'numeric'],
            'tax2_rate' => ['nullable', 'numeric'],
        ]);

        $quote = Quote::whereKey($quoteId)->firstOrFail();

        return DB::transaction(function () use ($validated, $quote) {
            $subtotal = 0;
            foreach ($validated['items'] as $item) {
                $subtotal += $item['quantity'] * $item['unit_price'];
            }

            $tax1Amount = $subtotal * (($validated['tax1_rate'] ?? 0) / 100);
            $tax2Amount = $subtotal * (($validated['tax2_rate'] ?? 0) / 100);
            $total = $subtotal + $tax1Amount + $tax2Amount;

            $quote->update([
                'quote_number' => $validated['quote_number'],
                'status' => $validated['status'],
                'expire_at' => $validated['expire_at'],
                'notes' => $validated['notes'],
                'terms' => $validated['terms'],
                'subtotal' => $subtotal,
                'tax1_amount' => $tax1Amount,
                'tax2_amount' => $tax2Amount,
                'total' => $total,
            ]);

            // Sync items: delete old ones and recreate
            $quote->items()->delete();

            foreach ($validated['items'] as $index => $itemData) {
                $quote->items()->create([
                    'description' => $itemData['description'],
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $itemData['unit_price'],
                    'total' => $itemData['quantity'] * $itemData['unit_price'],
                    'sort_order' => $index,
                ]);
            }

            return to_route('leads.show', $quote->lead_record_id);
        });
    }

    public function destroy(Request $request, int $quoteId)
    {
        $quote = Quote::whereKey($quoteId)->firstOrFail();
        $leadId = $quote->lead_record_id;
        $quote->delete();

        return to_route('leads.show', $leadId);
    }

    public function sendEmail(Request $request, int $quoteId)
    {
        $quote = Quote::with('leadRecord')->whereKey($quoteId)->firstOrFail();
        
        if (!$quote->leadRecord->email) {
            return back()->with('error', "Le prospect n'a pas d'adresse e-mail configurée.");
        }

        // Send email
        Mail::to($quote->leadRecord->email)->send(new QuoteSentMail($quote));

        // Update status if it was a draft
        if ($quote->status === 'draft') {
            $quote->update(['status' => 'sent']);
        }

        // Add a note in the CRM
        $quote->leadRecord->notes()->create([
            'user_id' => $request->user()->id,
            'type' => 'email',
            'content' => "Devis N°{$quote->quote_number} envoyé au client par e-mail.",
        ]);

        return back()->with('success', 'Devis envoyé avec succès !');
    }
}
