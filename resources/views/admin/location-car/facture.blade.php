<?php
$statutLabels = [
    'en_attente' => 'En attente de validation',
    'valide' => 'Validée',
    'rejete' => 'Rejetée',
];
$statutLabel = $statutLabels[$location->statut] ?? $location->statut;

// Une location créée par un chef d'escale n'est effective qu'après validation par
// l'Admin : la signature porte alors la mention "P.O." (pour ordre). Une location créée
// directement par l'Admin (toujours "validée" dès sa création) n'a pas besoin de cette
// mention. Même règle que le legacy (app/views/admin/pdf/facture_location.php).
$creeParChefEscale = ($location->agent_droit ?? null) === 'chef_d_escale';
$mentionSignature = ($creeParChefEscale && $location->statut === 'valide')
    ? 'Signature (P.O. '.($location->valide_par_nom ?? 'Admin').')'
    : 'Signature';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Facture location #{{ str_pad($location->id_location, 6, '0', STR_PAD_LEFT) }}</title>
    <style>
        @page { margin: 15mm 10mm; }

        * { box-sizing: border-box; }

        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            font-size: 13px;
            margin: 0;
            padding: 24px;
            color: #222;
            background: #f4f6f9;
        }

        .feuille {
            max-width: 760px;
            margin: 0 auto;
            background: #fff;
            padding: 32px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,.08);
        }

        header {
            display: flex;
            align-items: center;
            gap: 16px;
            border-bottom: 3px solid #0f3b5e;
            padding-bottom: 14px;
            margin-bottom: 18px;
        }

        header img { height: 54px; }

        header h2 {
            margin: 0;
            font-size: 20px;
            text-transform: uppercase;
            color: #0f3b5e;
        }

        header .slogan { font-size: 12px; font-style: italic; color: #666; }

        h3.titre-facture {
            text-align: center;
            font-size: 15px;
            margin: 0 0 20px;
            color: #333;
            font-weight: 600;
            letter-spacing: .5px;
        }

        .section { margin-bottom: 18px; }

        .section h4 {
            font-size: 12px;
            text-transform: uppercase;
            margin: 0 0 8px;
            padding-bottom: 4px;
            border-bottom: 1px solid #ddd;
            color: #0f3b5e;
            letter-spacing: .5px;
        }

        table.infos { width: 100%; border-collapse: collapse; }

        table.infos td {
            border: 1px solid #e5e9ef;
            padding: 8px 10px;
            font-size: 13px;
        }

        table.infos td.label {
            width: 32%;
            font-weight: 600;
            background: #f8f9fb;
            color: #444;
        }

        .badge-statut {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
        }
        .badge-statut.valide { background: #d4f4e2; color: #146c43; }
        .badge-statut.en_attente { background: #fff3cd; color: #997404; }
        .badge-statut.rejete { background: #fde2e1; color: #b02a37; }

        .montant {
            text-align: center;
            margin-top: 22px;
            padding: 18px;
            background: linear-gradient(135deg, #0f3b5e, #1d6fa5);
            border-radius: 10px;
            color: #fff;
        }

        .montant .label { font-size: 12px; opacity: .85; }
        .montant .valeur { font-size: 26px; font-weight: 700; }

        .signature-wrap { margin-top: 46px; text-align: right; }

        .signature { display: inline-block; width: 240px; text-align: center; }

        .signature .ligne {
            margin-top: 50px;
            border-top: 1px solid #333;
            padding-top: 4px;
            font-size: 11px;
            color: #444;
        }

        footer {
            margin-top: 26px;
            font-size: 10px;
            text-align: center;
            color: #999;
        }

        .barre-actions {
            max-width: 760px;
            margin: 0 auto 16px;
            display: flex;
            justify-content: flex-end;
        }

        .btn-imprimer {
            background: #198754;
            color: #fff;
            border: none;
            padding: 10px 20px;
            border-radius: 30px;
            font-size: 13px;
            cursor: pointer;
            box-shadow: 0 2px 8px rgba(25,135,84,.3);
        }

        @media print {
            body { background: #fff; padding: 0; }
            .feuille { box-shadow: none; border-radius: 0; max-width: 100%; }
            .barre-actions { display: none; }
        }
    </style>
</head>
<body>

    <div class="barre-actions">
        <button type="button" class="btn-imprimer" onclick="window.print()">🖨️ Imprimer</button>
    </div>

    <div class="feuille">
        <header>
            @if (! empty($compagnie?->logo))
                <img src="{{ asset('images/logos/'.$compagnie->logo) }}" alt="Logo">
            @endif
            <div>
                <h2>{{ $compagnie->nom_compagnie ?? 'Compagnie' }}</h2>
                @if (! empty($compagnie?->slogant))
                    <div class="slogan">{{ $compagnie->slogant }}</div>
                @endif
            </div>
        </header>

        <h3 class="titre-facture">Facture de location de car — N° {{ str_pad($location->id_location, 6, '0', STR_PAD_LEFT) }}</h3>

        <div class="section">
            <h4>Détails de la location</h4>
            <table class="infos">
                <tr>
                    <td class="label">Gare de départ</td>
                    <td>{{ $location->localite ?? '-' }} ({{ $location->numeroGare ?? '-' }})</td>
                    <td class="label">Destination</td>
                    <td>{{ $location->destination }}</td>
                </tr>
                <tr>
                    <td class="label">Car</td>
                    <td>N°{{ $location->numero_car ?? '-' }} - {{ $location->matriculle ?? '-' }}</td>
                    <td class="label">Statut</td>
                    <td><span class="badge-statut {{ $location->statut }}">{{ $statutLabel }}</span></td>
                </tr>
                <tr>
                    <td class="label">Date de départ</td>
                    <td>{{ \Illuminate\Support\Carbon::parse($location->date_depart)->format('d/m/Y') }}</td>
                    <td class="label">Date de retour prévu</td>
                    <td>{{ \Illuminate\Support\Carbon::parse($location->date_retour_prevu)->format('d/m/Y') }}</td>
                </tr>
                <tr>
                    <td class="label">Enregistrée par</td>
                    <td colspan="3">{{ $location->agent ?? '-' }}</td>
                </tr>
            </table>
        </div>

        <div class="section">
            <h4>Client</h4>
            <table class="infos">
                <tr>
                    <td class="label">Nom et prénom</td>
                    <td>{{ $location->prenom_client }} {{ $location->nom_client }}</td>
                    <td class="label">Téléphone</td>
                    <td>{{ $location->telephone_client }}</td>
                </tr>
            </table>
        </div>

        <div class="montant">
            <div class="label">Frais de location</div>
            <div class="valeur">{{ number_format($location->frais_location, 0, ',', ' ') }} FCFA</div>
        </div>

        <div class="signature-wrap">
            <div class="signature">
                <div class="ligne">{{ $mentionSignature }}</div>
            </div>
        </div>

        <footer>
            Facture générée le {{ now()->format('d/m/Y à H:i') }}.
        </footer>
    </div>

</body>
</html>
