<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\LeadRecord;
use App\Models\Quote;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $companyId = $request->user()->company_id;

        // Stats
        $totalClients = Client::count();
        $totalLeads = LeadRecord::count();

        $totalRevenue = Invoice::where('status', 'paid')->sum('total');
        $pendingRevenue = Invoice::whereIn('status', ['sent', 'overdue'])->sum('total');

        // Recent Data
        $recentInvoices = Invoice::with('client:id,name')->latest()->take(5)->get()->map(function ($invoice) {
            return [
                'id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'client_name' => $invoice->client ? $invoice->client->name : 'N/A',
                'status' => $invoice->status,
                'total' => $invoice->total,
                'issue_date' => $invoice->issue_date ? $invoice->issue_date->toDateString() : null,
            ];
        });

        $recentQuotes = Quote::with('leadRecord:id,name')->latest()->take(5)->get()->map(function ($quote) {
            return [
                'id' => $quote->id,
                'quote_number' => $quote->quote_number,
                'lead_name' => $quote->leadRecord ? $quote->leadRecord->name : 'N/A',
                'status' => $quote->status,
                'total' => $quote->total,
                'expire_at' => $quote->expire_at ? $quote->expire_at->toDateString() : null,
            ];
        });
        
        $settings = $request->user()->company->settings;

        return Inertia::render('Dashboard', [
            'stats' => [
                'total_clients' => $totalClients,
                'total_leads' => $totalLeads,
                'total_revenue' => $totalRevenue,
                'pending_revenue' => $pendingRevenue,
            ],
            'recentInvoices' => $recentInvoices,
            'recentQuotes' => $recentQuotes,
            'currency' => $settings->currency ?? 'USD',
        ]);
    }
}
