<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Compagnie;
use Illuminate\View\View;

// Page statique (pas de gestion admin) : présente les services réellement proposés par
// la plateforme (billetterie en ligne, colis, location de car...), pas de contenu éditable.
class ServiceController extends Controller
{
    public function index(): View
    {
        return view('site.services', ['compagnie' => Compagnie::site()]);
    }
}
