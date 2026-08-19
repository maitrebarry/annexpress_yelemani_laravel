<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function create(): \Illuminate\View\View
    {
        return view('auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'emailUser' => ['required', 'email'],
            'motPasse' => ['required', 'string'],
        ]);

        if (! Auth::guard('staff')->attempt([
            'emailUser' => $credentials['emailUser'],
            'password' => $credentials['motPasse'],
        ], $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'emailUser' => "Aucun compte trouvé avec cet email, ou mot de passe incorrect.",
            ]);
        }

        $utilisateur = Auth::guard('staff')->user();

        if ((int) $utilisateur->status !== 1) {
            Auth::guard('staff')->logout();

            throw ValidationException::withMessages([
                'emailUser' => "Ce compte a été désactivé. Contactez votre administrateur.",
            ]);
        }

        $request->session()->regenerate();

        return redirect()->intended(route('admin.home'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('staff')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
