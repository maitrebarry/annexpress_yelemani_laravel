<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PlaceMinimale;
use App\Support\Flash;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PlaceLimiteController extends Controller
{
    // super_admin voit/gère la limite de toutes les compagnies ; un Admin/PDG ne voit et
    // ne modifie que la limite de SA propre compagnie.
    public function index()
    {
        $user = Auth::guard('staff')->user();

        $query = PlaceMinimale::query()
            ->join('compagnie', 'compagnie.id_compagnie', '=', 'place_minumale.id_compagnie')
            ->select('place_minumale.*', 'compagnie.nom_compagnie');

        if (! $user->isSuperAdmin()) {
            $query->where('compagnie.id_compagnie', $user->id_compagnie);
        }

        $listePlace = $query->get();

        return view('admin.place-limite.index', ['listePlace' => $listePlace]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = Auth::guard('staff')->user();

        $data = $request->validate([
            'id_place_minumale' => ['required', 'integer'],
            'place_minumale' => ['required', 'integer', 'min:0'],
        ]);

        $query = PlaceMinimale::where('id_place_minumale', $data['id_place_minumale']);
        // Un Admin ne peut modifier que la ligne de sa propre compagnie (protection IDOR).
        if (! $user->isSuperAdmin()) {
            $query->where('id_compagnie', $user->id_compagnie);
        }

        $modifiee = $query->update(['place_minumale' => $data['place_minumale']]);

        Flash::set($modifiee ? 'Modification faite avec succès.' : 'Modification refusée.', $modifiee ? 'success' : 'danger');

        return redirect()->route('admin.place-limite.index');
    }
}
