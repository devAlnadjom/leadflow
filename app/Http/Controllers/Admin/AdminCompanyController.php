<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;

class AdminCompanyController extends Controller
{
    public function index(): Response
    {
        $companies = Company::withoutGlobalScopes()
            ->withCount(['users', 'leadForms'])
            ->with('users:id,company_id,name,email,is_active,is_super_admin')
            ->latest()
            ->get()
            ->map(fn($c) => [
                'id'            => $c->id,
                'name'          => $c->name,
                'email'         => $c->email,
                'phone'         => $c->phone,
                'industry'      => $c->industry,
                'is_active'     => $c->is_active,
                'users_count'   => $c->users_count,
                'lead_forms_count' => $c->lead_forms_count,
                'created_at'    => $c->created_at?->format('d M Y'),
            ]);

        return Inertia::render('admin/companies/Index', [
            'companies' => $companies,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/companies/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'company_name'     => ['required', 'string', 'max:255'],
            'company_email'    => ['nullable', 'email', 'max:255'],
            'company_phone'    => ['nullable', 'string', 'max:50'],
            'company_industry' => ['nullable', 'string', 'max:255'],
            'owner_name'       => ['required', 'string', 'max:255'],
            'owner_email'      => ['required', 'email', 'max:255', 'unique:users,email'],
            'owner_password'   => ['required', Password::min(8)],
        ]);

        DB::transaction(function () use ($data) {
            $company = Company::create([
                'name'     => $data['company_name'],
                'email'    => $data['company_email'] ?? null,
                'phone'    => $data['company_phone'] ?? null,
                'industry' => $data['company_industry'] ?? null,
                'is_active'=> true,
            ]);

            User::create([
                'company_id' => $company->id,
                'name'       => $data['owner_name'],
                'email'      => $data['owner_email'],
                'password'   => Hash::make($data['owner_password']),
                'is_active'  => true,
            ]);

            Log::channel('single')->info('[ADMIN] Entreprise créée', [
                'admin_id'     => auth()->id(),
                'company_name' => $data['company_name'],
            ]);
        });

        return redirect()->route('admin.companies.index')->with('success', 'Entreprise créée avec succès.');
    }

    public function show(Company $company): Response
    {
        $company->loadCount(['users', 'leadForms']);
        $company->load([
            'users:id,company_id,name,email,is_active,is_super_admin,created_at',
        ]);

        $leadsCount = \App\Models\LeadRecord::withoutGlobalScopes()
            ->where('company_id', $company->id)
            ->count();

        $revenue = \App\Models\Invoice::withoutGlobalScopes()
            ->where('company_id', $company->id)
            ->where('status', 'paid')
            ->sum('total');

        return Inertia::render('admin/companies/Show', [
            'company' => [
                'id'         => $company->id,
                'name'       => $company->name,
                'email'      => $company->email,
                'phone'      => $company->phone,
                'industry'   => $company->industry,
                'website'    => $company->website,
                'is_active'  => $company->is_active,
                'created_at' => $company->created_at?->format('d M Y'),
                'users'      => $company->users,
                'stats' => [
                    'users_count'      => $company->users_count,
                    'lead_forms_count' => $company->lead_forms_count,
                    'leads_count'      => $leadsCount,
                    'revenue'          => number_format($revenue, 2),
                ],
            ],
        ]);
    }

    public function edit(Company $company): Response
    {
        return Inertia::render('admin/companies/Edit', [
            'company' => [
                'id'       => $company->id,
                'name'     => $company->name,
                'email'    => $company->email,
                'phone'    => $company->phone,
                'industry' => $company->industry,
                'website'  => $company->website,
                'is_active'=> $company->is_active,
            ],
        ]);
    }

    public function update(Request $request, Company $company): RedirectResponse
    {
        $data = $request->validate([
            'name'      => ['required', 'string', 'max:255'],
            'email'     => ['nullable', 'email', 'max:255'],
            'phone'     => ['nullable', 'string', 'max:50'],
            'industry'  => ['nullable', 'string', 'max:255'],
            'website'   => ['nullable', 'url', 'max:255'],
        ]);

        $company->update($data);

        Log::channel('single')->info('[ADMIN] Entreprise mise à jour', [
            'admin_id'   => auth()->id(),
            'company_id' => $company->id,
        ]);

        return redirect()->route('admin.companies.show', $company)->with('success', 'Entreprise mise à jour.');
    }

    public function toggleActive(Company $company): RedirectResponse
    {
        // Prevent deactivating a company that has super admins
        if (! $company->is_active === false) {
            $hasSuperAdmin = $company->users()->where('is_super_admin', true)->exists();
            if ($hasSuperAdmin) {
                return back()->with('error', 'Impossible de désactiver une entreprise contenant un super administrateur.');
            }
        }

        $company->update(['is_active' => ! $company->is_active]);

        Log::channel('single')->info('[ADMIN] Toggle entreprise active', [
            'admin_id'   => auth()->id(),
            'company_id' => $company->id,
            'is_active'  => $company->is_active,
        ]);

        $status = $company->is_active ? 'activée' : 'désactivée';

        return back()->with('success', "L'entreprise \"{$company->name}\" a été {$status}.");
    }
}
