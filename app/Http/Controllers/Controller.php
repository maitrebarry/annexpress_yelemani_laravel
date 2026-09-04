<?php

namespace App\Http\Controllers;

use App\Support\Flash;

abstract class Controller
{
    // Utilisé par les formulaires "add to row" (plusieurs lignes ajoutées dynamiquement en
    // une soumission — voir CarController::store()/CamionController::store()) :
    // Flash::set() ne conserve qu'un seul message par requête (contrairement au set_flash()
    // du legacy Projets_licence, qui empile plusieurs toasts), donc les succès/erreurs de
    // chaque ligne sont regroupés en un seul message (le toast affiche du HTML, voir
    // admin/partials/set_flash.blade.php).
    protected function flashResultatAjoutMultiple(int $nbAjoutes, array $erreurs, string $singulier, string $pluriel): void
    {
        $lignes = [];
        if ($nbAjoutes > 0) {
            $lignes[] = $nbAjoutes > 1 ? "$nbAjoutes $pluriel ajoutés avec succès." : ucfirst($singulier).' ajouté avec succès.';
        }
        $lignes = [...$lignes, ...$erreurs];

        if (empty($lignes)) {
            Flash::set("Aucun $singulier à ajouter.", 'danger');

            return;
        }

        $type = $nbAjoutes > 0 ? (empty($erreurs) ? 'success' : 'warning') : 'danger';
        Flash::set(implode('<br>', array_map('e', $lignes)), $type);
    }
    protected function renderThermalDompdf(string $html, float $widthMm = 72.0): \Dompdf\Dompdf
    {
        $ptPerMm = 72 / 25.4;
        $widthPt = $widthMm * $ptPerMm;

        $options = new \Dompdf\Options();
        $options->setChroot(public_path());
        $options->setIsRemoteEnabled(true);

        $render = function (float $heightPt) use ($html, $widthPt, $options): \Dompdf\Dompdf {
            $dompdf = new \Dompdf\Dompdf($options);
            $dompdf->loadHtml($html);
            $dompdf->setPaper([0, 0, $widthPt, $heightPt], 'portrait');
            $dompdf->render();
            return $dompdf;
        };

        $lo = 20 * $ptPerMm;
        $hi = 600 * $ptPerMm;

        for ($i = 0; $i < 9; $i++) {
            $mid = ($lo + $hi) / 2;
            $pageCount = $render($mid)->getCanvas()->get_page_count();
            if ($pageCount > 1) {
                $lo = $mid;
            } else {
                $hi = $mid;
            }
        }

        // petite marge de sécurité pour éviter tout rognage de la dernière ligne
        return $render($hi + (3 * $ptPerMm));
    }

    protected function streamThermalPdf(string $html, string $filename, float $widthMm = 72.0): void
    {
        $this->renderThermalDompdf($html, $widthMm)->stream($filename, ['Attachment' => false]);
        exit;
    }
}
