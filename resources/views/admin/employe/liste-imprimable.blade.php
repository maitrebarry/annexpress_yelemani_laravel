<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Liste des employés</title>
    <style>
        @page { margin: 12mm 10mm; }

        * { box-sizing: border-box; }

        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 24px;
            color: #222;
            background: #f4f6f9;
        }

        .feuille {
            max-width: 1000px;
            margin: 0 auto;
            background: #fff;
            padding: 28px;
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

        header img { height: 48px; }

        header h2 {
            margin: 0;
            font-size: 18px;
            text-transform: uppercase;
            color: #0f3b5e;
        }

        h3.sous-titre {
            text-align: center;
            font-size: 13px;
            margin: 0 0 18px;
            color: #555;
            font-weight: 500;
        }

        table.liste { width: 100%; border-collapse: collapse; }

        table.liste th,
        table.liste td {
            border: 1px solid #e5e9ef;
            padding: 7px 8px;
            font-size: 12px;
        }

        table.liste th {
            background: #f8f9fb;
            color: #0f3b5e;
            text-transform: uppercase;
            font-size: 10.5px;
            letter-spacing: .3px;
        }

        .col-num { width: 28px; text-align: center; }
        .col-type, .col-statut { width: 90px; text-align: center; }

        .badge {
            display: inline-block;
            padding: 3px 9px;
            border-radius: 20px;
            font-size: 10px;
            font-weight: 600;
            color: #fff;
        }
        .badge-primary { background: #0d6efd; }
        .badge-warning { background: #ffc107; color: #222; }
        .badge-success { background: #198754; }
        .badge-secondary { background: #6c757d; }

        footer {
            margin-top: 18px;
            font-size: 10px;
            text-align: center;
            color: #999;
        }

        .barre-actions {
            max-width: 1000px;
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
            <h2>{{ $compagnie->nom_compagnie ?? 'Compagnie' }}</h2>
        </header>

        <h3 class="sous-titre">Liste des employés — {{ count($employes) }} au total — Générée le {{ now()->format('d/m/Y à H:i') }}</h3>

        <table class="liste">
            <thead>
                <tr>
                    <th class="col-num">#</th>
                    <th>Nom &amp; prénom</th>
                    <th>Fonction</th>
                    <th>Contact</th>
                    <th>Téléphone</th>
                    <th>Affectation</th>
                    <th class="col-type">Type</th>
                    <th class="col-statut">Statut</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($employes as $i => $employe)
                    <tr>
                        <td class="col-num">{{ $i + 1 }}</td>
                        <td>{{ $employe['nom'] }}</td>
                        <td>{{ $employe['fonction'] }}</td>
                        <td>{{ $employe['contact'] }}</td>
                        <td>{{ $employe['telephone'] }}</td>
                        <td>{{ $employe['affectation'] }}</td>
                        <td class="col-type">
                            <span class="badge {{ $employe['type'] === 'Chauffeur' ? 'badge-warning' : 'badge-primary' }}">{{ $employe['type'] }}</span>
                        </td>
                        <td class="col-statut">
                            <span class="badge {{ $employe['statut'] === 'Actif' ? 'badge-success' : 'badge-secondary' }}">{{ $employe['statut'] }}</span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" style="text-align:center;">Aucun employé trouvé pour cette compagnie.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <footer>
            Document généré le {{ now()->format('d/m/Y à H:i') }}.
        </footer>
    </div>

</body>
</html>
