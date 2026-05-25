<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        // Jika user belum login, atau role di database tidak cocok dengan syarat
        if (!$request->user() || $request->user()->role !== $role) {
            abort(403, 'Waduh, Anda tidak punya akses ke halaman ini, bang!');
        }

        return $next($request);
    }
}
