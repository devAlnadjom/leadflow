<?php

namespace App\Http\Controllers;

use App\Models\Quote;
use App\Models\Scopes\CompanyScope;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PublicQuoteController extends Controller
{
    /**
     * Display the public quote view for anonymous clients.
     * Bypasses CompanyScope since the client is not authenticated.
     */
    public function show(string $uid): Response
    {
        $quote = Quote::withoutGlobalScope(CompanyScope::class)
            ->with(['items', 'leadRecord:id,name,email', 'company', 'company.settings'])
            ->where('public_uid', $uid)
            ->firstOrFail();

        $company = $quote->company;
        $settings = $company->settings;

        return Inertia::render('quotes/Public', [
            'quote' => [
                'public_uid' => $quote->public_uid,
                'quote_number' => $quote->quote_number,
                'status' => $quote->status,
                'subtotal' => $quote->subtotal,
                'tax1_amount' => $quote->tax1_amount,
                'tax2_amount' => $quote->tax2_amount,
                'total' => $quote->total,
                'expire_at' => $quote->expire_at?->toDateString(),
                'terms' => $quote->terms,
                'created_at' => $quote->created_at?->toDateString(),
                'items' => $quote->items->map(fn ($item) => [
                    'description' => $item->description,
                    'quantity' => (float) $item->quantity,
                    'unit_price' => (float) $item->unit_price,
                    'total' => (float) $item->total,
                ]),
            ],
            'client' => [
                'name' => $quote->leadRecord?->name ?? 'Client',
                'email' => $quote->leadRecord?->email,
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
            ],
        ]);
    }

    /**
     * Handle client response (accept or reject).
     */
    public function respond(Request $request, string $uid)
    {
        $validated = $request->validate([
            'action' => ['required', 'string', 'in:accepted,rejected'],
        ]);

        $quote = Quote::withoutGlobalScope(CompanyScope::class)
            ->where('public_uid', $uid)
            ->firstOrFail();

        // Only allow response if quote is in "sent" status
        if ($quote->status !== 'sent') {
            return back()->withErrors([
                'action' => 'Ce devis ne peut plus être modifié.',
            ]);
        }

        $quote->update(['status' => $validated['action']]);

        return back();
    }
}
