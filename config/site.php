<?php

// Compagnie à laquelle le site public (resources/views/site/**, App\Http\Controllers\Site\*)
// est dédié — voir App\Models\Compagnie::site(). Changer cette seule valeur pour réactiver
// le catalogue multi-compagnies plus tard (voir mémoire projet, décision du 2026-08-24).
return [
    'compagnie_id' => (int) env('SITE_COMPAGNIE_ID', 1),
];
