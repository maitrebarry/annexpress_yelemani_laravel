<?php

namespace App\Http\Middleware;

use App\Models\Compagnie;
use Closure;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Le site public (vitrine) est dédié à une seule compagnie (config('site.compagnie_id'),
 * voir Compagnie::site()). Sur une base fraîchement installée — avant que le super_admin
 * n'ait créé sa première compagnie —, cette compagnie n'existe pas encore : sans ce
 * middleware, chaque page du site public plante en 404 (ModelNotFoundException) au lieu
 * d'expliquer ce qui se passe.
 */
class EnsureSiteCompagnieConfigured
{
    public function handle(Request $request, Closure $next): Response
    {
        try {
            Compagnie::site();
        } catch (ModelNotFoundException) {
            return response()->view('site.compagnie-non-configuree', status: 503);
        }

        return $next($request);
    }
}
