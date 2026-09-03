<?php

namespace App\Providers;

use Illuminate\Auth\Middleware\RedirectIfAuthenticated;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Toute la plateforme (admin + site public) est en Bootstrap 5 : sans ceci
        // {{ $x->links() }} rendrait la pagination par défaut de Laravel (Tailwind, jamais
        // chargé ici), non stylée.
        Paginator::useBootstrapFive();

        // Par défaut, un utilisateur déjà connecté qui retombe sur une route "guest"
        // (ex: /admin, protégée par `guest:staff`) est renvoyé vers '/' faute de route
        // nommée `home`/`dashboard` — sur ce site, '/' est la vitrine publique, pas
        // l'espace connecté : un admin déjà connecté doit atterrir sur son tableau de
        // bord, pas sur la page d'accueil du site.
        RedirectIfAuthenticated::redirectUsing(function (Request $request) {
            if (Auth::guard('staff')->check()) {
                return route('admin.home');
            }

            if (Auth::guard('partenaire')->check()) {
                return route('site.partenaire.discussion');
            }

            return route('site.home');
        });
    }
}
