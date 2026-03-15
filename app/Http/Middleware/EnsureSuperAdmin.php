<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSuperAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user() || !$request->user()->isSuperAdmin() || !$request->user()->id === 1) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Accès refusé.'], 403);
            }

            abort(403, 'Accès réservé aux super administrateurs.');
        }

        return $next($request);
    }
}