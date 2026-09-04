{{-- Port de Projets_licence/app/views/admin/pdf/bulletin_paie.php — voir GESTION_SALAIRES.md. --}}
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <style>
    @page { margin: 15mm 12mm; }
    body { font-family: DejaVu Sans, sans-serif; font-size: 11px; margin: 0; padding: 0; color: #222; }
    header { border-bottom: 2px solid #333; padding-bottom: 8px; margin-bottom: 16px; }
    header table { width: 100%; }
    header img { height: 48px; }
    h2 { margin: 4px 0; font-size: 16px; text-transform: uppercase; color: #2c3e50; }
    h3 { margin: 20px 0 10px; font-size: 13px; text-align: center; text-transform: uppercase; border-bottom: 1px solid #999; padding-bottom: 4px; }
    table.infos { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
    table.infos td { padding: 4px 6px; vertical-align: top; }
    table.infos td.label { font-weight: bold; width: 140px; color: #555; }
    table.montant { width: 100%; border-collapse: collapse; margin-top: 10px; }
    table.montant th, table.montant td { border: 1px solid #999; padding: 8px 10px; font-size: 12px; }
    table.montant th { background: #f0f0f0; text-align: left; }
    .montant-total td { font-weight: bold; font-size: 14px; background: #f8f9fa; }
    footer { margin-top: 30px; font-size: 9px; text-align: center; color: #777; }
  </style>
</head>
<body>

  <header>
    <table>
      <tr>
        <td width="60">
          @if ($logoPath)
            <img src="file://{{ realpath($logoPath) }}" alt="Logo">
          @endif
        </td>
        <td>
          <h2>{{ $bulletin->nom_compagnie ?? 'Compagnie' }}</h2>
        </td>
      </tr>
    </table>
  </header>

  <h3>Bulletin de paie — {{ $bulletin->periode }}</h3>

  <table class="infos">
    <tr>
      <td class="label">Employé</td>
      <td>{{ $bulletin->nom_affiche ?? 'N/A' }}</td>
      <td class="label">Poste</td>
      <td>{{ $bulletin->poste ?? '' }}</td>
    </tr>
    <tr>
      <td class="label">Gare de rattachement</td>
      <td>{{ $bulletin->localite ?? 'Compagnie entière' }}</td>
      <td class="label">Date de génération</td>
      <td>{{ \Illuminate\Support\Carbon::parse($bulletin->date_generation)->format('d/m/Y à H:i') }}</td>
    </tr>
  </table>

  <table class="montant">
    <thead>
      <tr>
        <th>Désignation</th>
        <th style="text-align:right;">Montant</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td>Salaire de base — {{ $bulletin->periode }}</td>
        <td style="text-align:right;">{{ number_format((float) $bulletin->salaire_verse, 0, ',', ' ') }} FCFA</td>
      </tr>
      <tr class="montant-total">
        <td>Net à payer</td>
        <td style="text-align:right;">{{ number_format((float) $bulletin->salaire_verse, 0, ',', ' ') }} FCFA</td>
      </tr>
    </tbody>
  </table>

  <footer>
    Document généré le {{ now()->format('d/m/Y à H:i') }} — Bulletin n°{{ $bulletin->id_bulletin }}.
  </footer>

</body>
</html>
