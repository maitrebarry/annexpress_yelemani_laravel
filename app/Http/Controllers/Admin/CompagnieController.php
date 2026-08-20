<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Compagnie;
use App\Models\PlaceMinimale;
use App\Support\Flash;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class CompagnieController extends Controller
{
    private const LOGO_RULES = ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'];

    public function index()
    {
        $liste = Compagnie::orderBy('nom_compagnie')->get();

        return view('admin.compagnie.index', ['liste' => $liste]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nom_compagnie' => ['required', 'string', 'max:250'],
            'libele' => ['required', 'string', 'max:250'],
            'slogant' => ['required', 'string', 'max:250'],
            'logo' => self::LOGO_RULES,
        ]);

        $logoName = $request->hasFile('logo') ? $this->enregistrerLogo($request->file('logo')) : null;

        DB::transaction(function () use ($data, $logoName) {
            $compagnie = Compagnie::create([
                'nom_compagnie' => $data['nom_compagnie'],
                'libele' => $data['libele'],
                'slogant' => $data['slogant'],
                'logo' => $logoName,
            ]);

            // Chaque compagnie a besoin de sa propre limite de places (réservations "demain").
            PlaceMinimale::create([
                'place_minumale' => 0,
                'id_compagnie' => $compagnie->id_compagnie,
            ]);
        });

        Flash::set('Compagnie ajoutée avec succès.', 'success');

        return redirect()->route('admin.compagnie.index');
    }

    public function update(Request $request): RedirectResponse
    {
        $compagnie = Compagnie::findOrFail($request->input('id_compagnie'));

        $data = $request->validate([
            'nom_compagnie' => ['required', 'string', 'max:250'],
            'libele' => ['required', 'string', 'max:250'],
            'slogant' => ['required', 'string', 'max:250'],
            'logo' => self::LOGO_RULES,
        ]);

        $logoName = $compagnie->logo;
        if ($request->hasFile('logo')) {
            $logoName = $this->enregistrerLogo($request->file('logo'));

            if ($compagnie->logo && file_exists(public_path('images/logos/'.$compagnie->logo))) {
                @unlink(public_path('images/logos/'.$compagnie->logo));
            }
        }

        $compagnie->update([
            'nom_compagnie' => $data['nom_compagnie'],
            'libele' => $data['libele'],
            'slogant' => $data['slogant'],
            'logo' => $logoName,
        ]);

        Flash::set('Modification effectuée avec succès.', 'success');

        return redirect()->route('admin.compagnie.index');
    }

    public function destroy(int $idCompagnie): RedirectResponse
    {
        $compagnie = Compagnie::find($idCompagnie);

        if (! $compagnie) {
            Flash::set('Compagnie introuvable.', 'danger');

            return redirect()->route('admin.compagnie.index');
        }

        try {
            $compagnie->delete();
            Flash::set('Compagnie supprimée avec succès.', 'success');
        } catch (\Throwable $e) {
            Flash::set('Impossible de supprimer cette compagnie : des données lui sont encore rattachées (gares, utilisateurs...).', 'danger');
        }

        return redirect()->route('admin.compagnie.index');
    }

    private function enregistrerLogo(UploadedFile $file): string
    {
        $extension = match ($file->getMimeType()) {
            'image/png' => 'png',
            'image/webp' => 'webp',
            default => 'jpg',
        };

        $nomFichier = uniqid('logo_', true).'.'.$extension;
        $file->move(public_path('images/logos'), $nomFichier);

        return $nomFichier;
    }
}
