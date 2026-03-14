<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'locale' => app()->getLocale(),
            'locales' => ['en', 'fr'],
            'auth' => [
                'user' => $request->user(),
                'unreadNotificationsCount' => $request->user() ? $request->user()->unreadNotifications()->count() : 0,
                'notifications' => $request->user() ? $request->user()->notifications()->latest()->take(5)->get() : [],
                'is_super_admin' => $request->user()?->isSuperAdmin() ?? false,
            ],
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
            'flash' => [
                'success' => $request->session()->get('success'),
                'error'   => $request->session()->get('error'),
                'message' => $request->session()->get('message'),
            ],
            'ghostMode' => [
                'active'              => (bool) session('impersonating_admin_id'),
                'original_admin_name' => session('impersonating_admin_id')
                    ? optional(\App\Models\User::find(session('impersonating_admin_id')))->name
                    : null,
            ],
        ];
    }
}
