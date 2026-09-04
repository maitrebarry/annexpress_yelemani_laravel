<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Port de Projets_licence/app/models/BulletinPaie.php — voir GESTION_SALAIRES.md.
 */
class BulletinPaie extends Model
{
    protected $table = 'bulletin_paie';

    protected $primaryKey = 'id_bulletin';

    public $timestamps = false;

    protected $fillable = [
        'id_employe',
        'periode',
        'salaire_verse',
        'date_generation',
        'genere_par',
        'id_compagnie',
    ];

    // Génère (ou renvoie l'existant) le bulletin d'un employé pour une période donnée --
    // un seul bulletin par (id_employe, periode), le montant est figé au moment de la
    // génération (indépendant d'un changement ultérieur du salaire de base). Réservé
    // Admin/PDG/super_admin, contrôlé par l'appelant.
    public static function genererBulletin(int $idEmploye, string $periode, int $salaireBase, ?int $generePar, int $idCompagnie): int
    {
        $existant = static::where('id_employe', $idEmploye)->where('periode', $periode)->first();
        if ($existant) {
            return $existant->id_bulletin;
        }

        $bulletin = static::create([
            'id_employe' => $idEmploye,
            'periode' => $periode,
            'salaire_verse' => $salaireBase,
            'date_generation' => now(),
            'genere_par' => $generePar,
            'id_compagnie' => $idCompagnie,
        ]);

        return $bulletin->id_bulletin;
    }

    // Même scoping hiérarchique que Employe::getEmployesVisibles() (jointure sur employe
    // pour appliquer le même filtre id_agence).
    public static function getBulletinsVisibles(Utilisateur $user)
    {
        $query = static::query()
            ->join('employe', 'bulletin_paie.id_employe', '=', 'employe.id_employe')
            ->leftJoin('utilisateur', 'employe.id_utilisateur', '=', 'utilisateur.idUser')
            ->leftJoin('chauffeur', 'employe.id_chauffeur', '=', 'chauffeur.id_chauffeur')
            ->select(
                'bulletin_paie.*',
                DB::raw('COALESCE(utilisateur.utilisateurs, chauffeur.nom_prenom, employe.nom_prenom) AS nom_affiche'),
                'employe.poste'
            );

        if ($user->droit === 'super_admin') {
            return $query->orderByDesc('bulletin_paie.date_generation')->get();
        }

        $query->where('bulletin_paie.id_compagnie', $user->id_compagnie);

        if (! empty($user->id_agence)) {
            $query->where(function ($q) use ($user) {
                $q->where('employe.id_agence', $user->id_agence)->orWhereNull('employe.id_agence');
            });
        }

        return $query->orderByDesc('bulletin_paie.date_generation')->get();
    }

    // Un seul bulletin, avec re-vérification IDOR (même principe que
    // Employe::getEmployeVisibleById()) -- utilisé avant le téléchargement du PDF.
    public static function getBulletinVisibleById(Utilisateur $user, int $id): ?object
    {
        $query = static::query()
            ->join('employe', 'bulletin_paie.id_employe', '=', 'employe.id_employe')
            ->leftJoin('utilisateur', 'employe.id_utilisateur', '=', 'utilisateur.idUser')
            ->leftJoin('chauffeur', 'employe.id_chauffeur', '=', 'chauffeur.id_chauffeur')
            ->leftJoin('agence', 'employe.id_agence', '=', 'agence.idAgence')
            ->leftJoin('compagnie', 'bulletin_paie.id_compagnie', '=', 'compagnie.id_compagnie')
            ->select(
                'bulletin_paie.*',
                DB::raw('COALESCE(utilisateur.utilisateurs, chauffeur.nom_prenom, employe.nom_prenom) AS nom_affiche'),
                'employe.poste',
                'employe.id_agence',
                'agence.localite',
                'agence.numeroGare',
                'compagnie.nom_compagnie',
                'compagnie.logo'
            )
            ->where('bulletin_paie.id_bulletin', $id);

        if ($user->droit !== 'super_admin') {
            $query->where('bulletin_paie.id_compagnie', $user->id_compagnie);

            if (! empty($user->id_agence)) {
                $query->where(function ($q) use ($user) {
                    $q->where('employe.id_agence', $user->id_agence)->orWhereNull('employe.id_agence');
                });
            }
        }

        return $query->first();
    }
}
