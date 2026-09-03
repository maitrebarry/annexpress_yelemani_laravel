<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Compagnie;
use Illuminate\Http\JsonResponse;

// Manifeste PWA du site public — généré dynamiquement (pas un fichier statique) car le
// site est multi-compagnie : chaque compagnie doit voir SON propre nom/logo à
// l'installation, jamais une identité générique codée en dur (même principe que
// site/partials/nav.blade.php — voir décision du 2026-09-03).
class ManifestController extends Controller
{
    public function index(): JsonResponse
    {
        $compagnie = Compagnie::site();

        $icons = $compagnie->logo
            ? [
                ['src' => asset('images/logos/'.$compagnie->logo), 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'any'],
                ['src' => asset('pwa/icon-transgest-192.png'), 'sizes' => '192x192', 'type' => 'image/png', 'purpose' => 'any maskable'],
            ]
            : [
                ['src' => asset('pwa/icon-transgest-192.png'), 'sizes' => '192x192', 'type' => 'image/png', 'purpose' => 'any maskable'],
                ['src' => asset('pwa/icon-transgest-512.png'), 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'any maskable'],
            ];

        return response()->json([
            'name' => $compagnie->nom_compagnie,
            'short_name' => $compagnie->nom_compagnie,
            'description' => $compagnie->slogant ?: 'Réservation de billets et suivi de colis en ligne.',
            'start_url' => '/',
            'scope' => '/',
            'display' => 'standalone',
            'background_color' => '#f5f7fb',
            'theme_color' => '#0f3b5e',
            'orientation' => 'any',
            'icons' => $icons,
        ])->header('Content-Type', 'application/manifest+json');
    }
}
