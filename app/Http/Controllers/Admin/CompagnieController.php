<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Compagnie;
use App\Models\CompagniePhoto;
use App\Models\PlaceMinimale;
use App\Support\Flash;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class CompagnieController extends Controller
{
    private const LOGO_RULES = ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'];

    // Photos de cars (carrousel connexion + site public) : plusieurs fichiers, mêmes
    // contraintes de format/poids que le logo.
    private const PHOTOS_RULES = ['nullable', 'array', 'max:12'];
    private const PHOTO_RULES = ['image', 'mimes:jpg,jpeg,png,webp', 'max:4096'];

    // Coordonnées affichées sur le site public (barre du haut, footer) — toutes
    // facultatives : une compagnie peut ne pas encore les avoir renseignées.
    private const CONTACT_RULES = [
        'telephone' => ['nullable', 'string', 'max:30'],
        'email' => ['nullable', 'email', 'max:150'],
        'adresse' => ['nullable', 'string', 'max:255'],
        'facebook' => ['nullable', 'url', 'max:255'],
        'instagram' => ['nullable', 'url', 'max:255'],
        'whatsapp' => ['nullable', 'string', 'max:30'],
    ];

    public function index()
    {
        $liste = Compagnie::with('photos')->orderBy('nom_compagnie')->get();

        return view('admin.compagnie.index', ['liste' => $liste]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate(array_merge([
            'nom_compagnie' => ['required', 'string', 'max:250'],
            'libele' => ['required', 'string', 'max:250'],
            'slogant' => ['required', 'string', 'max:250'],
            'logo' => self::LOGO_RULES,
            'photos' => self::PHOTOS_RULES,
            'photos.*' => self::PHOTO_RULES,
        ], self::CONTACT_RULES));

        $logoName = $request->hasFile('logo') ? $this->enregistrerLogo($request->file('logo')) : null;

        DB::transaction(function () use ($data, $logoName, $request) {
            $compagnie = Compagnie::create([
                'nom_compagnie' => $data['nom_compagnie'],
                'libele' => $data['libele'],
                'slogant' => $data['slogant'],
                'logo' => $logoName,
                'telephone' => $data['telephone'] ?? null,
                'email' => $data['email'] ?? null,
                'adresse' => $data['adresse'] ?? null,
                'facebook' => $data['facebook'] ?? null,
                'instagram' => $data['instagram'] ?? null,
                'whatsapp' => $data['whatsapp'] ?? null,
            ]);

            // Chaque compagnie a besoin de sa propre limite de places (réservations "demain").
            PlaceMinimale::create([
                'place_minumale' => 0,
                'id_compagnie' => $compagnie->id_compagnie,
            ]);

            $this->enregistrerPhotos($compagnie, $request->file('photos', []));
        });

        Flash::set('Compagnie ajoutée avec succès.', 'success');

        return redirect()->route('admin.compagnie.index');
    }

    public function update(Request $request): RedirectResponse
    {
        $compagnie = Compagnie::findOrFail($request->input('id_compagnie'));

        $data = $request->validate(array_merge([
            'nom_compagnie' => ['required', 'string', 'max:250'],
            'libele' => ['required', 'string', 'max:250'],
            'slogant' => ['required', 'string', 'max:250'],
            'logo' => self::LOGO_RULES,
            'photos' => self::PHOTOS_RULES,
            'photos.*' => self::PHOTO_RULES,
        ], self::CONTACT_RULES));

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
            'telephone' => $data['telephone'] ?? null,
            'email' => $data['email'] ?? null,
            'adresse' => $data['adresse'] ?? null,
            'facebook' => $data['facebook'] ?? null,
            'instagram' => $data['instagram'] ?? null,
            'whatsapp' => $data['whatsapp'] ?? null,
        ]);

        $this->enregistrerPhotos($compagnie, $request->file('photos', []));

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

    // Ajout de photos à une compagnie existante (modal dédié "Photos") — action isolée
    // pour ne pas dupliquer nom/libellé/slogan en champs cachés juste pour uploader
    // des photos.
    public function storePhotos(Request $request): RedirectResponse
    {
        $compagnie = Compagnie::findOrFail($request->input('id_compagnie'));

        $request->validate([
            'photos' => ['required', 'array', 'max:12'],
            'photos.*' => self::PHOTO_RULES,
        ]);

        $this->enregistrerPhotos($compagnie, $request->file('photos', []));

        Flash::set('Photo(s) ajoutée(s) avec succès.', 'success');

        return redirect()->route('admin.compagnie.index');
    }

    // Retrait d'une seule photo (bouton × sur une vignette du modal de modification) —
    // action isolée pour ne pas avoir à retoucher tout le formulaire compagnie pour ça.
    public function destroyPhoto(int $idPhoto): RedirectResponse
    {
        $photo = CompagniePhoto::find($idPhoto);

        if ($photo) {
            if (file_exists(public_path('images/compagnies_photos/'.$photo->chemin))) {
                @unlink(public_path('images/compagnies_photos/'.$photo->chemin));
            }
            $photo->delete();
            Flash::set('Photo supprimée.', 'success');
        }

        return redirect()->route('admin.compagnie.index');
    }

    private function enregistrerPhotos(Compagnie $compagnie, array $fichiers): void
    {
        if (empty($fichiers)) {
            return;
        }

        $ordre = (int) $compagnie->photos()->max('ordre');

        foreach ($fichiers as $fichier) {
            if (! $fichier instanceof UploadedFile || ! $fichier->isValid()) {
                continue;
            }

            $extension = match ($fichier->getMimeType()) {
                'image/png' => 'png',
                'image/webp' => 'webp',
                default => 'jpg',
            };

            $nomFichier = uniqid('car_', true).'.'.$extension;
            $fichier->move(public_path('images/compagnies_photos'), $nomFichier);

            $ordre++;

            CompagniePhoto::create([
                'id_compagnie' => $compagnie->id_compagnie,
                'chemin' => $nomFichier,
                'ordre' => $ordre,
            ]);
        }
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
