<?php

namespace App\Http\Middleware;

use App\Services\AuthorizationCenterClient;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        if (!$request->user() || !AuthorizationCenterClient::can($permission)) {
            abort(403, 'Anda tidak memiliki permission untuk mengakses fitur ini.');
        }

        return $next($request);
    }
}
