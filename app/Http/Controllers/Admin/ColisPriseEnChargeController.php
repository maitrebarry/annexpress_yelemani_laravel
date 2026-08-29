<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Agence;
use App\Models\Colis;
use App\Models\Compagnie;
use App\Models\Destinataire;
use App\Models\Expediteur;
use App\Services\CaisseUtilisateurService;
use App\Support\Flash;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Port de Projets_licence/app/controllers/admin/Colis_prise_en_charges.php +
 * app/models/Colis_prise_en_charge.php (sous-module "Colis pris en charge" uniquement —
 * impression/QR/thermique reportés à une prochaine étape).
 */
class ColisPriseEnChargeController extends Controller
{
    private const CODE_ALPHABET = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';

    public function index(): View|RedirectResponse
    {
        $user = Auth::guard('staff')->user();

        if (! $user->userHasPermission('colis_apercue') && ! $user->userHasPermission('colis_creation')) {
            Flash::set("Accès refusé : vous n'avez pas la permission nécessaire.", 'danger');

            return redirect()->route('admin.home');
        }

        $listeColis = Colis::query()
            ->visiblePar($user)
            ->avecDetails()
            ->orderByDesc('colis.id_colis')
            ->get();

        $listesAgences = Agence::where('id_compagnie', $user->id_compagnie)->get();

        return view('admin.colis.index', [
            'listeColis' => $listeColis,
            'listesAgences' => $listesAgences,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = Auth::guard('staff')->user();

        if ($user->droit === 'PDG') {
            Flash::set('Votre rôle est en lecture seule.', 'danger');

            return redirect()->route('admin.colis.index');
        }

        $idColis = (int) $request->input('id_colis');
        $nomColis = trim((string) $request->input('nom_colis'));
        $nature = trim((string) $request->input('nature'));
        $destination = $request->input('destination');
        $valeur = $request->input('valeur');
        $fraixTransaction = $request->input('fraix_transaction');

        $errors = [];
        if ($idColis <= 0) {
            $errors[] = 'Colis introuvable.';
        }
        if ($nomColis === '') {
            $errors[] = 'Le nom du colis est obligatoire.';
        }
        if ($nature === '') {
            $errors[] = 'La nature du colis est obligatoire.';
        }
        if (empty($destination)) {
            $errors[] = 'La destination est obligatoire.';
        }
        if ($valeur === null || $valeur === '' || ! is_numeric($valeur)) {
            $errors[] = 'La valeur du colis est obligatoire.';
        }
        if ($fraixTransaction === null || $fraixTransaction === '' || ! is_numeric($fraixTransaction)) {
            $errors[] = 'Les frais de transaction sont obligatoires.';
        }

        if (! empty($errors)) {
            Flash::set(implode(' ', $errors), 'warning');

            return redirect()->route('admin.colis.index');
        }

        $updated = Colis::where('id_colis', $idColis)
            ->where('id_compagnie', $user->id_compagnie)
            ->update([
                'nom_colis' => $nomColis,
                'nature' => $nature,
                'id_agence' => $destination,
                'valeur' => $valeur,
                'fraix_transaction' => $fraixTransaction,
            ]);

        Flash::set(
            $updated > 0 ? 'Les informations du colis ont été mises à jour.' : 'Aucune modification effectuée (colis introuvable).',
            $updated > 0 ? 'success' : 'danger'
        );

        return redirect()->route('admin.colis.index');
    }

    public function create(): View
    {
        $user = Auth::guard('staff')->user();

        $listes = Agence::where('id_compagnie', $user->id_compagnie)
            ->where('idAgence', '!=', $user->id_agence)
            ->get();

        return view('admin.colis.create', [
            'listes' => $listes,
            'codeColis' => $this->genererCodeUnique(),
        ]);
    }

    public function store(Request $request, CaisseUtilisateurService $caisseUtilisateurService): RedirectResponse
    {
        $user = Auth::guard('staff')->user();

        if ($user->droit === 'PDG') {
            Flash::set('Votre rôle est en lecture seule.', 'danger');

            return redirect()->route('admin.colis.create');
        }

        $expediteur = trim((string) $request->input('expediteur'));
        $numeroExp = trim((string) $request->input('numero_exp'));
        $whatsappExp = trim((string) $request->input('whatsapp_exp'));
        $destinataire = trim((string) $request->input('destinataire'));
        $numeroDest = trim((string) $request->input('numero_dest'));
        $whatsappDest = trim((string) $request->input('whatsapp_dest'));
        $nomColis = trim((string) $request->input('nom_colis'));
        $nature = trim((string) $request->input('nature'));
        $destination = $request->input('destination');
        $valeur = $request->input('valeur');
        $fraixTransaction = $request->input('fraix_transaction');
        $codeColis = trim((string) $request->input('code_colis'));

        $errors = [];
        if ($expediteur === '') {
            $errors[] = "Le nom de l'expéditeur est obligatoire.";
        }
        if ($numeroExp === '') {
            $errors[] = "Le numéro de l'expéditeur est obligatoire.";
        }
        if ($destinataire === '') {
            $errors[] = 'Le nom du destinataire est obligatoire.';
        }
        if ($numeroDest === '') {
            $errors[] = 'Le numéro du destinataire est obligatoire.';
        }
        if ($nomColis === '') {
            $errors[] = 'Le nom du colis est obligatoire.';
        }
        if ($nature === '') {
            $errors[] = 'La nature du colis est obligatoire.';
        }
        if (empty($destination)) {
            $errors[] = 'La destination est obligatoire.';
        }
        if (empty($valeur)) {
            $errors[] = 'La valeur du colis est obligatoire.';
        }
        if (empty($fraixTransaction)) {
            $errors[] = 'Les frais de transaction sont obligatoires.';
        }
        if ($codeColis === '') {
            $errors[] = 'Le code colis est obligatoire.';
        }

        if (! empty($errors)) {
            Flash::set(implode(' ', $errors), 'warning');

            return redirect()->route('admin.colis.create');
        }

        $ville = $user->agence?->localite;
        $numeroGare = $user->agence?->numeroGare;

        try {
            DB::transaction(function () use (
                $expediteur, $numeroExp, $whatsappExp, $destinataire, $numeroDest, $whatsappDest,
                $nomColis, $nature, $destination, $valeur, $fraixTransaction, $codeColis,
                $ville, $numeroGare, $user, $caisseUtilisateurService
            ) {
                $idExpediteur = Expediteur::create([
                    'expediteur' => $expediteur,
                    'numero_exp' => $numeroExp,
                    'whatsapp_exp' => $whatsappExp !== '' ? $whatsappExp : $numeroExp,
                ])->id_expediteur;

                $idDestinataire = Destinataire::create([
                    'destinataire' => $destinataire,
                    'numero_dest' => $numeroDest,
                    'whatsapp_dest' => $whatsappDest !== '' ? $whatsappDest : $numeroDest,
                    'id_exp' => $idExpediteur,
                ])->id_destinataire;

                $colis = Colis::create([
                    'nom_colis' => $nomColis,
                    'nature' => $nature,
                    'provient_de' => $ville,
                    'id_agence' => $destination,
                    'valeur' => $valeur,
                    'fraix_transaction' => $fraixTransaction,
                    'id_expediteur' => $idExpediteur,
                    'id_destinataire' => $idDestinataire,
                    'id_utilisateur' => $user->idUser,
                    'date_enregistrement' => now()->toDateString(),
                    'code_colis' => $codeColis,
                    'num_gare' => $numeroGare,
                    'status' => 'enregistre',
                    'id_compagnie' => $user->id_compagnie,
                ]);

                $creditOk = $caisseUtilisateurService->crediterColis(
                    $user->idUser,
                    (float) $fraixTransaction,
                    $codeColis,
                    $colis->id_colis
                );

                if ($creditOk === false) {
                    throw new \RuntimeException('CAISSE_FERMEE');
                }

                $caisseAgence = DB::table('caisse as c')
                    ->join('agence as a', 'c.id_agence', '=', 'a.idAgence')
                    ->where('c.id_compagnie', $user->id_compagnie)
                    ->where('a.localite', $ville)
                    ->where('a.numeroGare', $numeroGare)
                    ->where('c.status_caisse', 1)
                    ->select('c.id_caisse')
                    ->first();

                if ($caisseAgence) {
                    DB::table('caisse')
                        ->where('id_caisse', $caisseAgence->id_caisse)
                        ->update(['montant_colis' => DB::raw('montant_colis + '.(float) $fraixTransaction)]);
                }
            });
        } catch (\RuntimeException $e) {
            if ($e->getMessage() === 'CAISSE_FERMEE') {
                Flash::set("Opération bloquée : Aucune caisse ouverte pour votre compte. Veuillez ouvrir votre caisse d'abord.", 'danger');

                return redirect()->route('admin.colis.create');
            }

            throw $e;
        }

        Flash::set('Le colis a été enregistré avec succès dans le système.', 'success');

        return redirect()->route('admin.colis.create');
    }

    private function genererCodeUnique(): string
    {
        do {
            $code = $this->genererCodeColis();
        } while (Colis::where('code_colis', $code)->exists());

        return $code;
    }

    private function genererCodeColis(int $longueur = 6): string
    {
        $alphabet = self::CODE_ALPHABET;
        $max = strlen($alphabet) - 1;
        $code = '';

        for ($i = 0; $i < $longueur; $i++) {
            $code .= $alphabet[random_int(0, $max)];
        }

        return $code;
    }

    // ─── Impression du reçu colis (PDF thermique 80mm) ───────────────────────────
    // Port de Projets_licence/app/controllers/admin/Colis_prise_en_charges.php::imprimer_recu()
    // Les URLs sont figées car thermal-print.js les appelle en dur :
    //   GET /admin/Colis_prise_en_charges/imprimer_recu/{id}   → PDF de repli
    //   GET /admin/Colis_prise_en_charges/donneesRecuThermique/{id} → JSON pour ESC/POS
    public function imprimerRecu(int $id)
    {
        $user = Auth::guard('staff')->user();

        $colis = Colis::query()
            ->avecDetails()
            ->where('colis.id_colis', $id)
            ->where('colis.id_compagnie', $user->id_compagnie)
            ->first();

        $compagnie = Compagnie::find($user->id_compagnie);

        if (! $colis || ! $compagnie) {
            Flash::set('Colis ou compagnie introuvable.', 'danger');

            return redirect()->route('admin.colis.index');
        }

        $logoPath = null;
        if ($compagnie->logo) {
            $logoPath = public_path('images/logos/' . $compagnie->logo);
            if (! is_file($logoPath)) {
                $logoPath = null;
            }
        }

        // Génération du QR Code en base64
        $qrData = "Nom : {$colis->nom_colis}\nCode : {$colis->code_colis}\n"
            . "Départ : {$colis->provient_de}\nDestination : {$colis->destination}\n"
            . "Expéditeur : {$colis->expediteur}\nDestinataire : {$colis->destinataire}";

        $qrCode = new QrCode(data: $qrData, size: 200, margin: 6);
        $qrResult = (new PngWriter())->write($qrCode);
        $qrPath = 'data:image/png;base64,' . base64_encode($qrResult->getString());

        $html = view('admin.pdf.recu_colis', compact('colis', 'compagnie', 'logoPath', 'qrPath'))->render();

        $this->streamThermalPdf($html, "recu_colis_{$id}.pdf");
    }

    // Renvoie les données JSON du colis pour impression thermique ESC/POS via le pont local.
    // Port de Projets_licence/app/controllers/admin/Colis_prise_en_charges.php::donneesRecuThermique()
    public function donneesRecuThermique(int $id): JsonResponse
    {
        $user = Auth::guard('staff')->user();

        $colis = Colis::query()
            ->avecDetails()
            ->where('colis.id_colis', $id)
            ->where('colis.id_compagnie', $user->id_compagnie)
            ->first();

        $compagnie = Compagnie::find($user->id_compagnie);

        if (! $colis || ! $compagnie) {
            return response()->json(['error' => 'Colis ou compagnie introuvable.']);
        }

        $logoBase64 = null;
        if ($compagnie->logo) {
            $logoPath = public_path('images/logos/' . $compagnie->logo);
            if (is_file($logoPath)) {
                $logoBase64 = base64_encode(file_get_contents($logoPath));
            }
        }

        $qrData = "Nom : {$colis->nom_colis}\nCode : {$colis->code_colis}\n"
            . "Départ : {$colis->provient_de}\nDestination : {$colis->destination}\n"
            . "Expéditeur : {$colis->expediteur}\nDestinataire : {$colis->destinataire}";

        return response()->json([
            'type'         => 'colis',
            'compagnie'    => $compagnie->nom_compagnie ?? 'Compagnie',
            'slogan'       => $compagnie->slogant ?? '',
            'logo'         => $logoBase64,
            'code'         => $colis->code_colis ?? '-',
            'nom_colis'    => $colis->nom_colis ?? '-',
            'nature'       => $colis->nature ?? '-',
            'expediteur'   => $colis->expediteur ?? '-',
            'tel_exp'      => $colis->numero_exp ?? '-',
            'destinataire' => $colis->destinataire ?? '-',
            'tel_dest'     => $colis->numero_dest ?? '-',
            'depart'       => $colis->provient_de ?? '-',
            'destination'  => $colis->destination ?? '-',
            'agent'        => $colis->agent_nom ?? '-',
            'qr_data'      => $qrData,
        ]);
    }
}
