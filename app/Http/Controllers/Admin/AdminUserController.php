<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;

class AdminUserController extends Controller
{
    public function index(Request $request): Response
    {
        $companyFilter = $request->query('company_id');

        $query = User::with('company:id,name,is_active')
            ->when($companyFilter, fn($q) => $q->where('company_id', $companyFilter))
            ->latest();

        $users = $query->get()->map(fn($u) => [
            'id'             => $u->id,
            'name'           => $u->name,
            'email'          => $u->email,
            'is_active'      => $u->is_active,
            'is_super_admin' => $u->is_super_admin,
            'created_at'     => $u->created_at?->format('d M Y'),
            'company'        => $u->company ? [
                'id'        => $u->company->id,
                'name'      => $u->company->name,
                'is_active' => $u->company->is_active,
            ] : null,
        ]);

        $companies = Company::withoutGlobalScopes()
            ->orderBy('name')
            ->get(['id', 'name']);

        return Inertia::render('admin/users/Index', [
            'users'         => $users,
            'companies'     => $companies,
            'companyFilter' => $companyFilter,
        ]);
    }

    public function create(): Response
    {
        $companies = Company::withoutGlobalScopes()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        return Inertia::render('admin/users/Create', [
            'companies' => $companies,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'           => ['required', 'string', 'max:255'],
            'email'          => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password'       => ['required', 'confirmed', Rules\Password::defaults()],
            'company_id'     => ['nullable', 'exists:companies,id'],
            'is_super_admin' => ['boolean'],
        ]);

        $user = User::create([
            'name'           => $data['name'],
            'email'          => $data['email'],
            'password'       => Hash::make($data['password']),
            'company_id'     => $data['company_id'] ?? null,
            'is_super_admin' => $data['is_super_admin'] ?? false,
            'is_active'      => true,
        ]);

        Log::channel('single')->info('[ADMIN] Utilisateur créé', [
            'admin_id' => auth()->id(),
            'user_id'  => $user->id,
        ]);

        return redirect()->route('admin.users.index')->with('success', 'Utilisateur créé avec succès.');
    }

    public function edit(User $user): Response
    {
        $companies = Company::withoutGlobalScopes()
            ->orderBy('name')
            ->get(['id', 'name']);

        return Inertia::render('admin/users/Edit', [
            'user' => [
                'id'             => $user->id,
                'name'           => $user->name,
                'email'          => $user->email,
                'company_id'     => $user->company_id,
                'is_active'      => $user->is_active,
                'is_super_admin' => $user->is_super_admin,
            ],
            'companies' => $companies,
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'name'           => ['required', 'string', 'max:255'],
            'email'          => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'company_id'     => ['nullable', 'exists:companies,id'],
            'is_super_admin' => ['boolean'],
            'password'       => ['nullable', 'confirmed', Rules\Password::defaults()],
        ]);

        $fillable = [
            'name'           => $data['name'],
            'email'          => $data['email'],
            'company_id'     => $data['company_id'] ?? null,
            'is_super_admin' => $data['is_super_admin'] ?? false,
        ];

        if (! empty($data['password'])) {
            $fillable['password'] = Hash::make($data['password']);
        }

        $user->update($fillable);

        Log::channel('single')->info('[ADMIN] Utilisateur mis à jour', [
            'admin_id' => auth()->id(),
            'user_id'  => $user->id,
        ]);

        return redirect()->route('admin.users.index')->with('success', 'Utilisateur mis à jour.');
    }

    public function toggleActive(User $user): RedirectResponse
    {
        // Prevent super admin from deactivating themselves
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Vous ne pouvez pas désactiver votre propre compte.');
        }

        $user->update(['is_active' => ! $user->is_active]);

        Log::channel('single')->info('[ADMIN] Toggle user active', [
            'admin_id'  => auth()->id(),
            'user_id'   => $user->id,
            'is_active' => $user->is_active,
        ]);

        $status = $user->is_active ? 'activé' : 'désactivé';

        return back()->with('success', "L'utilisateur \"{$user->name}\" a été {$status}.");
    }

    public function resetPassword(User $user): RedirectResponse
    {
        $status = Password::sendResetLink(['email' => $user->email]);

        Log::channel('single')->info('[ADMIN] Password reset envoyé', [
            'admin_id' => auth()->id(),
            'user_id'  => $user->id,
        ]);

        return back()->with(
            $status === Password::RESET_LINK_SENT ? 'success' : 'error',
            $status === Password::RESET_LINK_SENT
                ? 'Lien de réinitialisation envoyé à ' . $user->email
                : 'Impossible d\'envoyer le lien. Vérifiez la configuration email.'
        );
    }
}
