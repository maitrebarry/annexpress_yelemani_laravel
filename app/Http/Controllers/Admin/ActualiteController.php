<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Actualite;
use App\Support\Flash;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;

class ActualiteController extends Controller
{
    // Rôles gérant le contenu public de LEUR compagnie (comme Configuration/Utilisateurs).
    // super_admin en est volontairement exclu : ce rôle n'est rattaché à aucune compagnie
    // (id_compagnie NULL), donc "leur" actualité n'a pas de sens pour lui — à la différence
    // de "Compagnie" (identité/branding), qui reste réservée au super_admin.
    private const ROLES_AUTORISES = ['Admin', 'PDG', 'secretaire'];

    private const IMAGE_RULES = ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'];

    public function index()
    {
        $this->autoriser();
        $user = Auth::guard('staff')->user();

        $liste = Actualite::where('id_compagnie', $user->id_compagnie)
            ->orderByDesc('date_publication')
            ->orderByDesc('id')
            ->get();

        return view('admin.actualite.index', ['liste' => $liste]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->autoriser();
        $user = Auth::guard('staff')->user();

        $data = $request->validate([
            'titre' => ['required', 'string', 'max:255'],
            'contenu' => ['required', 'string'],
            'date_publication' => ['required', 'date'],
            'image' => self::IMAGE_RULES,
        ]);

        Actualite::create([
            'id_compagnie' => $user->id_compagnie,
            'titre' => $data['titre'],
            'contenu' => $data['contenu'],
            'date_publication' => $data['date_publication'],
            'image' => $request->hasFile('image') ? $this->enregistrerImage($request->file('image')) : null,
        ]);

        Flash::set('Actualité publiée avec succès.', 'success');

        return redirect()->route('admin.actualite.index');
    }

    public function update(Request $request): RedirectResponse
    {
        $this->autoriser();
        $user = Auth::guard('staff')->user();

        $actualite = Actualite::where('id_compagnie', $user->id_compagnie)->findOrFail($request->input('id'));

        $data = $request->validate([
            'titre' => ['required', 'string', 'max:255'],
            'contenu' => ['required', 'string'],
            'date_publication' => ['required', 'date'],
            'image' => self::IMAGE_RULES,
        ]);

        $imageName = $actualite->image;
        if ($request->hasFile('image')) {
            $imageName = $this->enregistrerImage($request->file('image'));

            if ($actualite->image && file_exists(public_path('images/actualites/'.$actualite->image))) {
                @unlink(public_path('images/actualites/'.$actualite->image));
            }
        }

        $actualite->update([
            'titre' => $data['titre'],
            'contenu' => $data['contenu'],
            'date_publication' => $data['date_publication'],
            'image' => $imageName,
        ]);

        Flash::set('Actualité modifiée avec succès.', 'success');

        return redirect()->route('admin.actualite.index');
    }

    public function destroy(int $id): RedirectResponse
    {
        $this->autoriser();
        $user = Auth::guard('staff')->user();

        $actualite = Actualite::where('id_compagnie', $user->id_compagnie)->find($id);

        if ($actualite) {
            if ($actualite->image && file_exists(public_path('images/actualites/'.$actualite->image))) {
                @unlink(public_path('images/actualites/'.$actualite->image));
            }
            $actualite->delete();
            Flash::set('Actualité supprimée avec succès.', 'success');
        }

        return redirect()->route('admin.actualite.index');
    }

    private function enregistrerImage(UploadedFile $file): string
    {
        $extension = match ($file->getMimeType()) {
            'image/png' => 'png',
            'image/webp' => 'webp',
            default => 'jpg',
        };

        $nomFichier = uniqid('actu_', true).'.'.$extension;
        $file->move(public_path('images/actualites'), $nomFichier);

        return $nomFichier;
    }

    private function autoriser(): void
    {
        abort_unless(in_array(Auth::guard('staff')->user()?->droit, self::ROLES_AUTORISES, true), 403);
    }
}
