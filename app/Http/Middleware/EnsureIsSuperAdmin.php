<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureIsSuperAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user('staff')?->isSuperAdmin()) {
            abort(403, "Accès réservé à l'administration Sirali.");
        }

        return $next($request);
    }
}
