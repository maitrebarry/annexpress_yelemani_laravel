<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Http\Response;
use Illuminate\View\View;

/**
 * Port de Projets_licence/app/controllers/admin/Documentations.php.
 *
 * Manuel d'utilisation du système, de la connexion au dernier module. Accessible à TOUT
 * compte connecté quel que soit son rôle ou ses permissions (voir routes/web.php) : c'est
 * de la documentation, pas un écran métier, donc aucune restriction n'a de sens ici.
 */
class DocumentationController extends Controller
{
    public function index(): View
    {
        return view('admin.documentation.index');
    }

    public function pdf(): Response
    {
        // Une quarantaine d'images à assembler : marge de sécurité au cas où l'hébergement
        // serait plus lent qu'en local (les captures sont déjà compressées en JPEG côté
        // source, voir public/images/documentation/, ce qui est la vraie précaution).
        set_time_limit(240);

        $html = view('admin.pdf.documentation')->render();

        $options = new Options();
        $options->setChroot(public_path());
        $options->setIsRemoteEnabled(true);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="manuel_utilisation_sirali.pdf"',
        ]);
    }
}
