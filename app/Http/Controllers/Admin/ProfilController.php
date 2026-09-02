<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\Flash;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProfilController extends Controller
{
    private const TELEPHONE_REGEX = '/^[0-9+\s.\-]{6,20}$/';

    public function edit(): View
    {
        $user = Auth::guard('staff')->user();
        return view('admin.profil.edit', compact('user'));
    }

    public function update(Request $request): RedirectResponse
    {
        $user = Auth::guard('staff')->user();

        $data = $request->validate([
            'utilisateurs' => ['required', 'string', 'max:250'],
            'emailUser' => ['required', 'email', 'max:250', Rule::unique('utilisateur', 'emailUser')->ignore($user->idUser, 'idUser')],
            'telephone' => ['nullable', 'regex:'.self::TELEPHONE_REGEX],
            'motPasse' => ['nullable', 'string', 'min:6', 'confirmed'],
            'photo' => ['nullable', 'image', 'max:5120'],
        ], [
            'motPasse.confirmed' => 'La confirmation du mot de passe ne correspond pas.',
            'motPasse.min' => 'Le mot de passe doit faire au moins 6 caractères.',
        ]);

        $user->utilisateurs = $data['utilisateurs'];
        $user->emailUser = $data['emailUser'];
        $user->telephone = $data['telephone'] ?: null;

        if (! empty($data['motPasse'])) {
            $user->motPasse = Hash::make($data['motPasse']);
        }

        if ($request->hasFile('photo')) {
            if ($user->photo) {
                Storage::disk('public')->delete('profiles/'.$user->photo);
            }
            $path = $request->file('photo')->store('profiles', 'public');
            $user->photo = basename($path);
        }

        $user->save();

        Flash::set('Votre profil a été mis à jour avec succès.', 'success');

        return redirect()->back();
    }
}
