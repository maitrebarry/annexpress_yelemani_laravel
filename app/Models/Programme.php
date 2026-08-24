<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Port de Projets_licence/app/models/Programmer_voyage.php (table `programmer`) —
 * un voyage programmé (départ, destination, heure, prix) pour une compagnie.
 */
class Programme extends Model
{
    protected $table = 'programmer';

    protected $primaryKey = 'idProgrammer';

    public $timestamps = false;

    protected $fillable = [
        'idDepart',
        'idDestination',
        'heureDepart',
        'rdv',
        'prix',
        'id_compagnie',
    ];

    public function depart()
    {
        return $this->belongsTo(Agence::class, 'idDepart', 'idAgence');
    }

    public function destination()
    {
        return $this->belongsTo(Agence::class, 'idDestination', 'idAgence');
    }

    public function compagnie()
    {
        return $this->belongsTo(Compagnie::class, 'id_compagnie', 'id_compagnie');
    }

    public function escales()
    {
        return $this->hasMany(LigneTrajet::class, 'id_trajets', 'idProgrammer')
            ->where('type_trajet', 'programmer');
    }

    // Port de Liste_du_jour::resolveDestinationPrincipale() : un billet stocke soit la
    // destination finale, soit le nom d'une escale intermédiaire dans `destinationId`. Cette
    // méthode retrouve la destination FINALE du trajet, nécessaire pour retrouver la bonne
    // ligne `programmation_voyage`/`suivis` (qui utilisent toujours la destination finale,
    // jamais un nom d'escale). Partagée par les annulations et les reports.
    public static function resolveDestinationPrincipale(string $depart, string $heure, string $destinationId, int $idCompagnie): string
    {
        $direct = self::query()
            ->join('agence as a1', 'programmer.idDepart', '=', 'a1.idAgence')
            ->join('agence as a2', 'programmer.idDestination', '=', 'a2.idAgence')
            ->where('a1.localite', $depart)->where('a2.localite', $destinationId)
            ->where('programmer.heureDepart', $heure)->where('programmer.id_compagnie', $idCompagnie)
            ->exists();

        if ($direct) {
            return $destinationId;
        }

        $viaEscale = DB::table('ligneTrajet as lt')
            ->join('escale as e', 'e.id_escale', '=', 'lt.id_escales')
            ->join('programmer as p', function ($join) {
                $join->on('p.idProgrammer', '=', 'lt.id_trajets')->where('lt.type_trajet', 'programmer');
            })
            ->join('agence as a1', 'p.idDepart', '=', 'a1.idAgence')
            ->join('agence as a2', 'p.idDestination', '=', 'a2.idAgence')
            ->where('e.escales', $destinationId)->where('a1.localite', $depart)
            ->where('p.heureDepart', $heure)->where('p.id_compagnie', $idCompagnie)
            ->value('a2.localite');

        return $viaEscale ?? $destinationId;
    }

    // Port simplifié de Projets_licence/app/models/Programme.php::getByCompagnie() pour
    // la vitrine (Accueil/Compagnies) : pas besoin des escales/numéros de gare ici, voir
    // pourCompagnieAvecEscales() pour la page de détails par compagnie.
    public static function pourVitrine(int $idCompagnie)
    {
        return self::query()
            ->join('agence as a1', 'programmer.idDepart', '=', 'a1.idAgence')
            ->join('agence as a2', 'programmer.idDestination', '=', 'a2.idAgence')
            ->where('programmer.id_compagnie', $idCompagnie)
            ->orderBy('a1.localite')->orderBy('a2.localite')->orderBy('programmer.heureDepart')
            ->get(['programmer.heureDepart', 'programmer.prix', 'a1.localite as departLocalite', 'a2.localite as destinationLocalite']);
    }

    // Port de Projets_licence/app/models/Programme.php::getByCompagnie() — version complète
    // (numéros de gare + escales/tarifs) pour la page publique de détails d'une compagnie.
    // Le GROUP_CONCAT est fait via une sous-requête corrélée plutôt qu'un GROUP BY sur la
    // requête principale : ce serveur MariaDB rejette (ONLY_FULL_GROUP_BY, erreur 1055) le
    // GROUP BY sur la seule clé primaire `programmer.idProgrammer` même si tout le reste est
    // fonctionnellement dépendant — l'optimisation de dépendance fonctionnelle de MySQL n'est
    // pas fiable sur cette version de MariaDB.
    public static function pourCompagnieAvecEscales(int $idCompagnie)
    {
        return self::query()
            ->join('agence as a1', 'programmer.idDepart', '=', 'a1.idAgence')
            ->join('agence as a2', 'programmer.idDestination', '=', 'a2.idAgence')
            ->where('programmer.id_compagnie', $idCompagnie)
            ->orderBy('a1.localite')->orderBy('a2.localite')->orderBy('programmer.heureDepart')
            ->get([
                'programmer.*',
                'a1.localite as departLocalite', 'a1.numeroGare as numeroGare1',
                'a2.localite as destinationLocalite', 'a2.numeroGare as numeroGare2',
                DB::raw("(SELECT GROUP_CONCAT(CONCAT(e.escales, ' (', lt.prix_escale, ' FCFA)') ORDER BY e.id_escale SEPARATOR ', ')
                          FROM ligneTrajet lt JOIN escale e ON e.id_escale = lt.id_escales
                          WHERE lt.id_trajets = programmer.idProgrammer AND lt.type_trajet = 'programmer') as escales_avec_frais"),
            ]);
    }

    // Port de Projets_licence/app/models/Programme.php::findById() — un seul trajet, avec
    // escales/tarifs et numéros de gare, pour préremplir le formulaire de réservation en
    // ligne (Site\ReservationController).
    public static function trouverAvecEscales(int $id): ?self
    {
        return self::query()
            ->join('agence as a1', 'programmer.idDepart', '=', 'a1.idAgence')
            ->join('agence as a2', 'programmer.idDestination', '=', 'a2.idAgence')
            ->where('programmer.idProgrammer', $id)
            ->first([
                'programmer.*',
                'a1.localite as departLocalite', 'a1.numeroGare as numeroGare1', 'a1.code as codeDepart',
                'a2.localite as destinationLocalite', 'a2.numeroGare as numeroGare2',
                DB::raw("(SELECT GROUP_CONCAT(CONCAT(e.escales, ' (', lt.prix_escale, ' FCFA)') ORDER BY e.id_escale SEPARATOR ', ')
                          FROM ligneTrajet lt JOIN escale e ON e.id_escale = lt.id_escales
                          WHERE lt.id_trajets = programmer.idProgrammer AND lt.type_trajet = 'programmer') as escales_avec_frais"),
            ]);
    }

    // Port de Projets_licence/app/models/Programme.php::getVillesDisponibles() : toutes
    // compagnies confondues, pour le sélecteur départ/destination de la recherche publique.
    public static function villesDisponibles(?int $idCompagnie = null)
    {
        return Agence::query()
            ->where(function ($q) use ($idCompagnie) {
                $q->whereIn('idAgence', fn ($sub) => $sub->select('idDepart')->from('programmer')
                        ->when($idCompagnie, fn ($s) => $s->where('id_compagnie', $idCompagnie)))
                    ->orWhereIn('idAgence', fn ($sub) => $sub->select('idDestination')->from('programmer')
                        ->when($idCompagnie, fn ($s) => $s->where('id_compagnie', $idCompagnie)));
            })
            ->distinct()->orderBy('localite')->pluck('localite');
    }

    // Port de Projets_licence/app/models/Programme.php::rechercher() : trajets programmés,
    // toutes compagnies confondues, filtrés par ville de départ/destination/compagnie (chaque
    // filtre optionnel). `$date` n'est volontairement pas utilisé pour filtrer : fidèle au
    // legacy, où `programmer` décrit des trajets récurrents quotidiens, pas des instances par
    // date (celles-ci vivent côté admin dans `programmation_voyage`, jamais exposées ici).
    public static function rechercher(string $depart, string $destination, string $idCompagnie = '')
    {
        return self::query()
            ->join('agence as a1', 'programmer.idDepart', '=', 'a1.idAgence')
            ->join('agence as a2', 'programmer.idDestination', '=', 'a2.idAgence')
            ->join('compagnie as co', 'programmer.id_compagnie', '=', 'co.id_compagnie')
            ->when($depart !== '', fn ($q) => $q->where('a1.localite', $depart))
            ->when($destination !== '', fn ($q) => $q->where('a2.localite', $destination))
            ->when($idCompagnie !== '', fn ($q) => $q->where('programmer.id_compagnie', $idCompagnie))
            ->orderBy('programmer.prix')
            ->get([
                'programmer.*',
                'a1.localite as departLocalite', 'a2.localite as destinationLocalite',
                'co.id_compagnie as compagnieId', 'co.nom_compagnie', 'co.logo',
            ]);
    }
}
