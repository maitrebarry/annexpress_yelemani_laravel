<?php

namespace App\Http\Controllers;

abstract class Controller
{
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
