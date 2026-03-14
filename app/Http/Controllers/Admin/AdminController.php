<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Invoice;
use App\Models\LeadRecord;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class AdminController extends Controller
{
    /**
     * Super Admin Dashboard with global platform stats.
     */
    public function dashboard(): Response
    {
        $totalCompanies = Company::withoutGlobalScopes()->count();
        $activeCompanies = Company::withoutGlobalScopes()->where('is_active', true)->count();
        $totalUsers = User::count();
        $activeUsers = User::where('is_active', true)->count();
        $totalLeads = LeadRecord::withoutGlobalScopes()->count();

        $leadsThisMonth = LeadRecord::withoutGlobalScopes()
            ->where('created_at', '>=', now()->startOfMonth())
            ->count();

        $revenueThisMonth = Invoice::withoutGlobalScopes()
            ->whereIn('status', ['paid'])
            ->where('created_at', '>=', now()->startOfMonth())
            ->sum('total');

        $revenueTotal = Invoice::withoutGlobalScopes()
            ->whereIn('status', ['paid'])
            ->sum('total');

        // Recent companies (last 5)
        $recentCompanies = Company::withoutGlobalScopes()
            ->withCount(['users', 'leadForms'])
            ->latest()
            ->take(5)
            ->get()
            ->map(fn($c) => [
                'id'           => $c->id,
                'name'         => $c->name,
                'is_active'    => $c->is_active,
                'users_count'  => $c->users_count,
                'created_at'   => $c->created_at?->format('d M Y'),
            ]);

        return Inertia::render('admin/Dashboard', [
            'stats' => [
                'total_companies'    => $totalCompanies,
                'active_companies'   => $activeCompanies,
                'total_users'        => $totalUsers,
                'active_users'       => $activeUsers,
                'total_leads'        => $totalLeads,
                'leads_this_month'   => $leadsThisMonth,
                'revenue_this_month' => number_format($revenueThisMonth, 2),
                'revenue_total'      => number_format($revenueTotal, 2),
            ],
            'recentCompanies' => $recentCompanies,
        ]);
    }

    /**
     * Impersonate a user (Ghost Mode).
     */
    public function impersonate(Request $request, User $user): RedirectResponse
    {
        // Prevent impersonating another super admin
        if ($user->isSuperAdmin()) {
            return back()->with('error', 'Vous ne pouvez pas usurper l\'identité d\'un autre super administrateur.');
        }

        // Prevent impersonating yourself
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Vous ne pouvez pas vous usurper vous-même.');
        }

        // Store the admin's ID in session
        session(['impersonating_admin_id' => auth()->id()]);

        Log::channel('single')->info('[ADMIN] Ghost Mode activé', [
            'admin_id'   => auth()->id(),
            'admin_name' => auth()->user()->name,
            'target_id'  => $user->id,
            'target_name'=> $user->name,
        ]);

        Auth::loginUsingId($user->id);

        return redirect()->route('dashboard')->with('success', 'Mode Ghost activé — vous êtes connecté en tant que ' . $user->name);
    }

    /**
     * Stop impersonating — return to the admin account.
     */
    public function stopImpersonating(Request $request): RedirectResponse
    {
        $adminId = session('impersonating_admin_id');

        if (! $adminId) {
            return redirect()->route('dashboard');
        }

        session()->forget('impersonating_admin_id');

        Auth::loginUsingId($adminId);

        return redirect()->route('admin.dashboard')->with('success', 'Mode Ghost désactivé — vous êtes de retour en mode Super Admin.');
    }
}
