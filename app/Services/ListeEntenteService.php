<?php

namespace App\Services;

use App\Mail\BilletValide;
use App\Models\Billet;
use App\Models\Compagnie;
use App\Models\Utilisateur;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

/**
 * Port de Projets_licence/app/controllers/admin/Liste_ententes.php +
 * app/models/Liste_entente.php — validation manuelle des réservations en ligne créées par
 * Site\ReservationController (billets `status_reservation = 'en_ligne'` /
 * `validation_billets = 'en_attente'`). Legacy joignait un PDF+QR (Dompdf) au mail de
 * confirmation ; non disponible ici (voir README "pas de PDF") — le mail renvoie vers la
 * fiche billet imprimable (site.billet) à la place, même substitution déjà faite pour
 * Site\ReservationController::store()/ReservationEnLigneService.
 */
class ListeEntenteService
{
    public function listeEnAttente(Utilisateur $user): Collection
    {
        $isAdmin = in_array($user->droit, ['Admin', 'PDG'], true);

        return Billet::query()
            ->join('client', 'billets.id_client', '=', 'client.idClient')
            ->where('billets.id_compagnie', $user->id_compagnie)
            ->where('billets.status_reservation', 'en_ligne')
            ->where('billets.validation_billets', 'en_attente')
            ->when(! $isAdmin, fn ($q) => $q
                ->where('billets.departId', $user->agence?->localite)
                ->where('billets.num_gare', $user->agence?->numeroGare))
            ->orderBy('billets.date_reservation')
            ->get(['billets.*', 'client.Client', 'client.montant_payer', 'client.emailClient', 'client.numeroPaiement']);
    }

    public function valider(Utilisateur $user, int $idBillets, string $numeroPaiementConfirme, CaisseUtilisateurService $caisseService): array
    {
        if ($user->estLectureSeule()) {
            return ['ok' => false, 'type' => 'danger', 'message' => 'Votre rôle est en lecture seule.'];
        }

        $billet = Billet::query()
            ->join('client', 'billets.id_client', '=', 'client.idClient')
            ->where('billets.idBillets', $idBillets)
            ->where('billets.id_compagnie', $user->id_compagnie)
            ->first(['billets.*', 'client.Client', 'client.montant_payer', 'client.emailClient', 'client.numeroPaiement']);

        if (! $billet) {
            return ['ok' => false, 'type' => 'danger', 'message' => 'Billet introuvable.'];
        }

        // Seule la gare de départ du billet a le droit de le valider (l'argent est encaissé
        // dans sa caisse) ; Admin/PDG peuvent valider depuis n'importe quelle gare de leur
        // compagnie. Même garde-fou que le legacy.
        if (
            in_array($user->droit, ['chef_d_escale', 'Utilisateur'], true)
            && ($billet->departId !== $user->agence?->localite || (string) $billet->num_gare !== (string) $user->agence?->numeroGare)
        ) {
            return ['ok' => false, 'type' => 'danger', 'message' => 'Ce billet ne relève pas de votre gare de départ.'];
        }

        // Comparaison insensible aux espaces (le formulaire de réservation en ligne formate
        // le numéro en groupes "77 78 88 99" — une comparaison stricte comme le legacy est
        // inutilement fragile ici) : amélioration délibérée, pas un port silencieux.
        $normalise = fn (string $v) => str_replace(' ', '', trim($v));
        if ($normalise($numeroPaiementConfirme) !== $normalise((string) $billet->numeroPaiement)) {
            return ['ok' => false, 'type' => 'warning', 'message' => 'Le numéro de paiement ne correspond pas.'];
        }

        try {
            return DB::transaction(function () use ($user, $billet, $caisseService) {
                // La condition sur validation_billets empêche de créditer la caisse deux fois
                // si le formulaire est soumis deux fois (double clic, requêtes concurrentes) —
                // même garde-fou que le legacy (rowCount() === 0).
                $affecte = Billet::where('idBillets', $billet->idBillets)
                    ->where('validation_billets', '!=', 'valider')
                    ->update(['validation_billets' => 'valider', 'idUser' => $user->idUser]);

                if (! $affecte) {
                    throw new \RuntimeException('Ce billet a déjà été validé.');
                }

                // Crédité sur la caisse individuelle ouverte de l'agent qui valide — même
                // mécanisme qu'un billet vendu au guichet (BilletService::creerReservation),
                // pas la caisse de gare morte que le legacy créditait.
                $montant = (int) preg_replace('/\D/', '', (string) $billet->montant_payer);
                $creditOk = $caisseService->crediterBillet($user->idUser, $montant, $billet->numeroBillets, $billet->idBillets);
                if ($creditOk === false) {
                    throw new \RuntimeException('Vous devez avoir une caisse ouverte pour valider ce billet.');
                }

                $emailEnvoye = null;
                if (! empty($billet->emailClient)) {
                    $emailEnvoye = $this->envoyerEmailValidation($billet, $user);
                }

                return ['ok' => true, 'type' => 'success', 'message' => $this->messageSucces($emailEnvoye)];
            });
        } catch (\RuntimeException $e) {
            return ['ok' => false, 'type' => 'danger', 'message' => $e->getMessage()];
        }
    }

    private function envoyerEmailValidation(Billet $billet, Utilisateur $user): bool
    {
        try {
            $nomCompagnie = Compagnie::where('id_compagnie', $user->id_compagnie)->value('nom_compagnie') ?? 'Billetterie';

            Mail::to($billet->emailClient)->send(new BilletValide($billet, $nomCompagnie));

            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }

    private function messageSucces(?bool $emailEnvoye): string
    {
        return match ($emailEnvoye) {
            true => 'Validation réussie : un reçu a été envoyé au client par email.',
            false => "Validation réussie, mais l'envoi de l'email a échoué.",
            default => "Validation réussie. Aucune adresse email fournie : le reçu n'a pas pu être envoyé.",
        };
    }
}
