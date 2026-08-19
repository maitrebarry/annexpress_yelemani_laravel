<?php

namespace App\Http\Middleware;

use App\Support\Flash;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureHasPermission
{
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = $request->user('staff');

        if (! $user?->userHasPermission($permission)) {
            Flash::set("Accès refusé : vous n'avez pas la permission nécessaire.", 'danger');

            return redirect()->route('admin.home');
        }

        return $next($request);
    }
}
