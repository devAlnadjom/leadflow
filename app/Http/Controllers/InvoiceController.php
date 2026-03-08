<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\InvoiceMail;

class InvoiceController extends Controller
{
    public function index(Request $request): Response
    {
        $search = trim((string) $request->query('search', ''));
        $status = (string) $request->query('status', '');

        $query = Invoice::with('client:id,name,email,company_name')
            ->latest();

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', '%' . $search . '%')
                  ->orWhereHas('client', fn ($c) => $c->where('name', 'like', '%' . $search . '%')
                      ->orWhere('company_name', 'like', '%' . $search . '%'));
            });
        }

        if ($status !== '') {
            $query->where('status', $status);
        }

        $invoices = $query
            ->paginate(20)
            ->withQueryString()
            ->through(fn (Invoice $invoice) => [
                'id' => $invoice->id,
                'public_uid' => $invoice->public_uid,
                'invoice_number' => $invoice->invoice_number,
                'status' => $invoice->status,
                'total' => $invoice->total,
                'issue_date' => $invoice->issue_date?->toDateString(),
                'due_date' => $invoice->due_date?->toDateString(),
                'created_at' => $invoice->created_at?->toDateTimeString(),
                'client_name' => $invoice->client?->name ?? '-',
                'client_company' => $invoice->client?->company_name ?? '-',
                'client_id' => $invoice->client?->id,
            ]);

        return Inertia::render('invoices/Index', [
            'invoices' => $invoices,
            'filters' => [
                'search' => $search,
                'status' => $status,
            ],
        ]);
    }

    public function create(Request $request, ?int $clientId = null): Response
    {
        $client = null;
        if ($clientId) {
            $client = Client::query()->whereKey($clientId)->firstOrFail();
        }

        $company = $request->user()->company;
        $settings = $company->settings;
        
        // Suggest a number
        $count = Invoice::count();
        $suggestedNumber = ($settings->invoice_prefix ?? 'FAC-') . (1000 + $count + 1);

        return Inertia::render('invoices/Create', [
            'client' => $client ? [
                'id' => $client->id,
                'name' => $client->name,
                'email' => $client->email,
            ] : null,
            'settings' => $settings,
            'suggestedNumber' => $suggestedNumber,
            'clients' => !$clientId ? Client::select('id', 'name', 'company_name')->get() : [],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id' => ['required', 'exists:clients,id'],
            'invoice_number' => ['required', 'string', 'max:255'],
            'status' => ['required', 'string', 'in:draft,sent,paid,overdue,cancelled'],
            'issue_date' => ['required', 'date'],
            'due_date' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.description' => ['required', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'numeric', 'min:0'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'tax1_rate' => ['nullable', 'numeric', 'min:0'],
            'tax2_rate' => ['nullable', 'numeric', 'min:0'],
        ]);

        return DB::transaction(function () use ($validated, $request) {
            $subtotal = 0;
            foreach ($validated['items'] as $item) {
                $subtotal += $item['quantity'] * $item['unit_price'];
            }

            $tax1Amount = $subtotal * (($validated['tax1_rate'] ?? 0) / 100);
            $tax2Amount = $subtotal * (($validated['tax2_rate'] ?? 0) / 100);
            $total = $subtotal + $tax1Amount + $tax2Amount;

            $invoice = Invoice::create([
                'company_id' => $request->user()->company_id,
                'client_id' => $validated['client_id'],
                'invoice_number' => $validated['invoice_number'],
                'status' => $validated['status'],
                'issue_date' => $validated['issue_date'],
                'due_date' => $validated['due_date'],
                'notes' => $validated['notes'],
                'subtotal' => $subtotal,
                'tax1_amount' => $tax1Amount,
                'tax2_amount' => $tax2Amount,
                'total' => $total,
            ]);

            foreach ($validated['items'] as $itemData) {
                $invoice->items()->create([
                    'description' => $itemData['description'],
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $itemData['unit_price'],
                    'total' => $itemData['quantity'] * $itemData['unit_price'],
                ]);
            }

            return to_route('clients.show', $validated['client_id'])->with('success', 'Facture créée avec succès.');
        });
    }

    public function edit(Request $request, int $id): Response
    {
        $invoice = Invoice::with(['items', 'client'])->whereKey($id)->firstOrFail();
        $company = $request->user()->company;
        $settings = $company->settings;

        return Inertia::render('invoices/Edit', [
            'invoice' => [
                'id' => $invoice->id,
                'public_uid' => $invoice->public_uid,
                'client_id' => $invoice->client_id,
                'invoice_number' => $invoice->invoice_number,
                'status' => $invoice->status,
                'issue_date' => $invoice->issue_date?->toDateString(),
                'due_date' => $invoice->due_date?->toDateString(),
                'subtotal' => $invoice->subtotal,
                'tax1_amount' => $invoice->tax1_amount,
                'tax2_amount' => $invoice->tax2_amount,
                'total' => $invoice->total,
                'notes' => $invoice->notes,
                'items' => $invoice->items->map(function($item) {
                    return [
                        'description' => $item->description,
                        'quantity' => $item->quantity,
                        'unit_price' => $item->unit_price,
                        'total' => $item->total,
                    ];
                }),
                'client' => $invoice->client,
            ],
            'client' => $invoice->client,
            'settings' => $settings,
        ]);
    }

    public function update(Request $request, int $id)
    {
        $validated = $request->validate([
            'invoice_number' => ['required', 'string', 'max:255'],
            'status' => ['required', 'string', 'in:draft,sent,paid,overdue,cancelled'],
            'issue_date' => ['required', 'date'],
            'due_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
            'tax1_rate' => ['nullable', 'numeric', 'min:0'],
            'tax2_rate' => ['nullable', 'numeric', 'min:0'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.description' => ['required', 'string', 'max:500'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
        ]);

        return DB::transaction(function () use ($request, $validated, $id) {
            $invoice = Invoice::whereKey($id)->firstOrFail();

            $subtotal = 0;
            foreach ($validated['items'] as $item) {
                $subtotal += ($item['quantity'] * $item['unit_price']);
            }

            $tax1Amount = $subtotal * (($validated['tax1_rate'] ?? 0) / 100);
            $tax2Amount = $subtotal * (($validated['tax2_rate'] ?? 0) / 100);
            $total = $subtotal + $tax1Amount + $tax2Amount;

            $invoice->update([
                'invoice_number' => $validated['invoice_number'],
                'status' => $validated['status'],
                'issue_date' => $validated['issue_date'],
                'due_date' => $validated['due_date'],
                'notes' => $validated['notes'],
                'subtotal' => $subtotal,
                'tax1_amount' => $tax1Amount,
                'tax2_amount' => $tax2Amount,
                'total' => $total,
            ]);

            // Sync items
            $invoice->items()->delete();

            foreach ($validated['items'] as $itemData) {
                $invoice->items()->create([
                    'description' => $itemData['description'],
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $itemData['unit_price'],
                    'total' => $itemData['quantity'] * $itemData['unit_price'],
                ]);
            }

            return to_route('invoices.show', $invoice->id)->with('success', 'Facture mise à jour avec succès.');
        });
    }

    public function show(Request $request, int $id): Response
    {
        $invoice = Invoice::with(['items', 'client'])->whereKey($id)->firstOrFail();
        $company = $request->user()->company;
        $settings = $company->settings;

        return Inertia::render('invoices/Show', [
            'invoice' => [
                'id' => $invoice->id,
                'public_uid' => $invoice->public_uid,
                'invoice_number' => $invoice->invoice_number,
                'status' => $invoice->status,
                'issue_date' => $invoice->issue_date?->toDateString(),
                'due_date' => $invoice->due_date?->toDateString(),
                'subtotal' => $invoice->subtotal,
                'tax1_amount' => $invoice->tax1_amount,
                'tax2_amount' => $invoice->tax2_amount,
                'total' => $invoice->total,
                'notes' => $invoice->notes,
                'items' => $invoice->items,
                'client' => $invoice->client,
            ],
            'settings' => $settings,
        ]);
    }
    
    public function updateStatus(Request $request, int $id)
    {
        $validated = $request->validate([
            'status' => ['required', 'string', 'in:draft,sent,paid,overdue,cancelled'],
        ]);
        
        $invoice = Invoice::whereKey($id)->firstOrFail();
        $invoice->update(['status' => $validated['status']]);
        
        return back()->with('success', 'Statut de la facture mis à jour.');
    }

    public function destroy(Request $request, int $id)
    {
        $invoice = Invoice::whereKey($id)->firstOrFail();
        $clientId = $invoice->client_id;
        $invoice->delete();

        return to_route('clients.show', $clientId)->with('success', 'Facture supprimée.');
    }

    public function sendEmail(Request $request, int $id)
    {
        $invoice = Invoice::with(['client', 'company.settings'])->whereKey($id)->firstOrFail();
        
        if (!$invoice->client->email) {
            return back()->with('error', "Le client n'a pas d'adresse e-mail configurée.");
        }

        // Send email
        Mail::to($invoice->client->email)->send(new InvoiceMail($invoice));

        // Update status if it was a draft
        if ($invoice->status === 'draft') {
            $invoice->update(['status' => 'sent']);
        }

        // Add a note in the CRM
        $invoice->client->notes()->create([
            'user_id' => $request->user()->id,
            'type' => 'email',
            'company_id' => $invoice->company_id,
            'content' => "Facture N°{$invoice->invoice_number} envoyée au client par e-mail.",
        ]);

        return back()->with('success', 'Facture envoyée par e-mail au client.');
    }
}
