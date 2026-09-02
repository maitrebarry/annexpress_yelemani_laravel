<?php

namespace App\Services;

use App\Models\Chauffeur;
use App\Models\Utilisateur;

/**
 * Port de Projets_licence/app/controllers/admin/Employes.php. Vue unifiée "Employés" :
 * regroupe les comptes utilisateur (avec leur droit/fonction) et les chauffeurs de la
 * compagnie, chacun visible seulement si l'utilisateur connecté a la permission
 * correspondante (mêmes permissions que Configurations et Chauffeurs_cars).
 */
class EmployeService
{
    private const DROIT_LABELS = [
        'super_admin' => 'Super administrateur',
        'Admin' => 'Administrateur',
        'PDG' => 'PDG (superviseur)',
        'chef_d_escale' => "Chef d'escale",
        'Utilisateur' => 'Utilisateur',
    ];

    /**
     * Construit la liste unifiée des employés (utilisateurs + chauffeurs) visibles par
     * l'utilisateur connecté. Partagée entre l'affichage de la page et l'export imprimable.
     *
     * @return array<int, array<string, mixed>>
     */
    public function buildListe(Utilisateur $user, bool $peutVoirUtilisateurs, bool $peutVoirChauffeurs): array
    {
        $employes = [];

        if ($peutVoirUtilisateurs) {
            $query = Utilisateur::query()
                ->leftJoin('agence', 'agence.idAgence', '=', 'utilisateur.id_agence')
                ->where('utilisateur.droit', '!=', 'super_admin');

            if (! $user->isSuperAdmin()) {
                // LEFT JOIN (et non INNER JOIN) car les comptes Admin/PDG n'ont pas
                // forcément d'agence assignée : ils sont rattachés directement à la
                // compagnie via utilisateur.id_compagnie.
                $query->where(function ($q) use ($user) {
                    $q->where(function ($q2) use ($user) {
                        $q2->whereIn('utilisateur.droit', ['Admin', 'PDG', 'secretaire'])
                            ->where('utilisateur.id_compagnie', $user->id_compagnie);
                    })->orWhere(function ($q2) use ($user) {
                        $q2->whereNotIn('utilisateur.droit', ['Admin', 'PDG', 'secretaire'])
                            ->where('agence.id_compagnie', $user->id_compagnie);
                    });
                });
            }

            $utilisateurs = $query->get([
                'utilisateur.idUser', 'utilisateur.utilisateurs', 'utilisateur.emailUser', 'utilisateur.telephone',
                'utilisateur.droit', 'utilisateur.profile', 'utilisateur.status', 'utilisateur.photo', 'agence.numeroGare',
            ]);

            foreach ($utilisateurs as $u) {
                $fonction = self::DROIT_LABELS[$u->droit] ?? $u->droit;
                if ($u->droit === 'Utilisateur' && ! empty($u->profile)) {
                    $service = match ($u->profile) {
                        'billet' => 'Billetterie',
                        'colis' => 'Colis / Courrier',
                        default => $u->profile,
                    };
                    $fonction .= ' - '.$service;
                }

                $employes[] = [
                    'id' => $u->idUser,
                    'type' => 'Utilisateur',
                    'nom' => $u->utilisateurs,
                    'fonction' => $fonction,
                    'contact' => $u->emailUser,
                    'telephone' => $u->telephone ?: '—',
                    'affectation' => $u->numeroGare ?? '—',
                    'statut' => ((int) $u->status === 1) ? 'Actif' : 'Inactif',
                    'photo' => $u->photo ?? null,
                ];
            }
        }

        if ($peutVoirChauffeurs) {
            $chauffeurs = Chauffeur::query()
                ->join('car', 'chauffeur.id_car', '=', 'car.id_car')
                ->when(! $user->isSuperAdmin(), fn ($q) => $q->where('car.id_compagnie', $user->id_compagnie))
                ->get(['chauffeur.*', 'car.numero_car']);

            foreach ($chauffeurs as $c) {
                $employes[] = [
                    'id' => $c->id_chauffeur,
                    'type' => 'Chauffeur',
                    'nom' => $c->nom_prenom,
                    'fonction' => 'Chauffeur',
                    'contact' => '—',
                    'telephone' => $c->numero,
                    'affectation' => 'Car : '.$c->numero_car,
                    'statut' => 'Actif',
                    'photo' => $c->photo ?? null,
                ];
            }
        }

        return $employes;
    }

    /**
     * Résout un employé (Utilisateur ou Chauffeur) vers le tableau attendu par la vue de
     * badge. Applique le même périmètre de compagnie que buildListe() : un compte non
     * super_admin ne peut résoudre que les employés de sa propre compagnie (IDOR sinon).
     *
     * @return array<string, mixed>|null
     */
    public function resolveEmploye(string $type, int $id, Utilisateur $user): ?array
    {
        if ($type === 'Utilisateur') {
            return $this->resolveUtilisateur($id, $user);
        }

        if ($type === 'Chauffeur') {
            return $this->resolveChauffeur($id, $user);
        }

        return null;
    }

    private function resolveUtilisateur(int $id, Utilisateur $user): ?array
    {
        $u = Utilisateur::find($id);
        if (! $u) {
            return null;
        }

        $employe = [
            'type' => 'Utilisateur',
            'nom' => $u->utilisateurs,
            'fonction' => $u->droit,
            'contact' => $u->emailUser,
            'telephone' => $u->telephone,
            'affectation' => '',
            'localite' => '',
            'photo' => $u->photo,
        ];

        // Admin/PDG sont rattachés directement à la compagnie via utilisateur.id_compagnie
        // (pas d'agence assignée), cf. buildListe().
        if (in_array($u->droit, ['Admin', 'PDG', 'secretaire'], true)) {
            $employeCompagnie = $u->id_compagnie;
        } else {
            $employeCompagnie = null;
            if ($u->id_agence) {
                $agence = $u->agence;
                if ($agence) {
                    $employe['affectation'] = $agence->numeroGare;
                    $employe['localite'] = $agence->localite;
                    $employeCompagnie = $agence->id_compagnie;
                }
            }
        }

        if (! $user->isSuperAdmin() && (int) $employeCompagnie !== (int) $user->id_compagnie) {
            return null;
        }

        return $employe;
    }

    private function resolveChauffeur(int $id, Utilisateur $user): ?array
    {
        $c = Chauffeur::find($id);
        if (! $c) {
            return null;
        }

        $employe = [
            'type' => 'Chauffeur',
            'nom' => $c->nom_prenom,
            'fonction' => 'Chauffeur',
            'contact' => '—',
            'telephone' => $c->numero,
            'photo' => $c->photo,
            'affectation' => '',
            'localite' => '',
        ];

        $carCompagnie = null;
        if ($c->id_car) {
            $car = $c->car;
            if ($car) {
                $employe['affectation'] = 'Car : '.$car->numero_car;
                $carCompagnie = $car->id_compagnie;
            }
        }

        if (! $user->isSuperAdmin() && (int) $carCompagnie !== (int) $user->id_compagnie) {
            return null;
        }

        return $employe;
    }
}
