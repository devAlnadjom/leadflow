<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Scopes\CompanyScope;
use Inertia\Inertia;
use Inertia\Response;

class PublicInvoiceController extends Controller
{
    /**
     * Display the public invoice view for clients.
     * Bypasses CompanyScope since the client is not authenticated.
     */
    public function show(string $uid): Response
    {
        $invoice = Invoice::withoutGlobalScope(CompanyScope::class)
            ->with(['items', 'client:id,name,email', 'company', 'company.settings'])
            ->where('public_uid', $uid)
            ->firstOrFail();

        $company = $invoice->company;
        $settings = $company->settings;

        return Inertia::render('invoices/Public', [
            'invoice' => [
                'public_uid' => $invoice->public_uid,
                'invoice_number' => $invoice->invoice_number,
                'status' => $invoice->status,
                'subtotal' => $invoice->subtotal,
                'tax1_amount' => $invoice->tax1_amount,
                'tax2_amount' => $invoice->tax2_amount,
                'total' => $invoice->total,
                'issue_date' => $invoice->issue_date?->toDateString(),
                'due_date' => $invoice->due_date?->toDateString(),
                'notes' => $invoice->notes,
                'created_at' => $invoice->created_at?->toDateString(),
                'items' => $invoice->items->map(fn ($item) => [
                    'description' => $item->description,
                    'quantity' => (float) $item->quantity,
                    'unit_price' => (float) $item->unit_price,
                    'total' => (float) $item->total,
                ]),
            ],
            'client' => [
                'name' => $invoice->client?->name ?? 'Client',
                'email' => $invoice->client?->email,
            ],
            'company' => [
                'name' => $company->name,
                'email' => $company->email,
                'phone' => $company->phone,
                'address' => $company->address,
                'logo_path' => $company->logo_path,
                'primary_color' => $company->primary_color ?? '#4f46e5',
            ],
            'settings' => [
                'tax1_name' => $settings->tax1_name ?? null,
                'tax1_rate' => $settings->tax1_rate ?? 0,
                'tax2_name' => $settings->tax2_name ?? null,
                'tax2_rate' => $settings->tax2_rate ?? 0,
                'currency' => $settings->currency ?? '$',
                'legal_mentions' => $settings->legal_mentions ?? [],
                'terms_and_conditions' => $settings->terms_and_conditions ?? null,
            ],
        ]);
    }
}
