<?php

namespace App\Support;

/**
 * Port de afficherBadgeStatus() (Projets_licence/app/views/admin/helpers.view.php).
 */
class ColisStatus
{
    public static function badge(?string $status): string
    {
        return match ($status) {
            'enregistre' => '<span class="badge bg-primary">Prise en charge</span>',
            'en_cours' => '<span class="badge bg-warning">En cours</span>',
            'recu' => '<span class="badge bg-success">Colis reçu</span>',
            'livre' => '<span class="badge bg-info">Colis livré</span>',
            null, '' => '<span class="badge bg-secondary">En attente</span>',
            default => '<span class="badge bg-danger">Inconnu</span>',
        };
    }
}
