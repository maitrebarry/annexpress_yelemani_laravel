<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Actualite;
use App\Models\Compagnie;
use Illuminate\View\View;

class ActualiteController extends Controller
{
    public function index(): View
    {
        $compagnie = Compagnie::site();

        $liste = Actualite::where('id_compagnie', $compagnie->id_compagnie)
            ->where('date_publication', '<=', now())
            ->orderByDesc('date_publication')
            ->orderByDesc('id')
            ->paginate(9);

        return view('site.actualites.index', compact('compagnie', 'liste'));
    }

    public function show(Actualite $actualite): View
    {
        $compagnie = Compagnie::site();

        abort_unless($actualite->id_compagnie === $compagnie->id_compagnie, 404);
        abort_if($actualite->date_publication->isFuture(), 404);

        $autres = Actualite::where('id_compagnie', $compagnie->id_compagnie)
            ->where('id', '!=', $actualite->id)
            ->where('date_publication', '<=', now())
            ->orderByDesc('date_publication')
            ->take(3)
            ->get();

        return view('site.actualites.show', compact('compagnie', 'actualite', 'autres'));
    }
}
