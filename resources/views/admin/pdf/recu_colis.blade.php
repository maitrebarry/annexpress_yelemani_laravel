<!doctype html>
<html lang="fr">
<head>
<meta charset="utf-8">
<style>
    @page { margin: 2mm 3mm; }
    body { font-family: DejaVu Sans, sans-serif; font-size: 13px; margin: 0; padding: 0; color: #000; }
    header { border-bottom: 1px dashed #000; margin-bottom: 6px; padding-bottom: 4px; text-align: center; }
    header img { height: 36px; display: block; margin: 0 auto 3px; }
    h2 { margin: 2px 0; font-size: 16px; text-transform: uppercase; }
    small { font-size: 11px; color: #000; }
    .bloc { margin-bottom: 6px; }
    .bloc h3 { font-size: 12px; margin: 0 0 2px; text-transform: uppercase; border-bottom: 1px solid #000; padding-bottom: 1px; }
    .row { padding: 1px 0; }
    .row .label { font-weight: bold; }
    footer { border-top: 1px dashed #000; margin-top: 6px; padding-top: 4px; font-size: 10px; text-align: center; }
    .qr { margin-top: 6px; text-align: center; }
</style>
</head>
<body>
<header>
    @if (!empty($logoPath) && file_exists($logoPath))
        <img src="file://{{ realpath($logoPath) }}" alt="Logo">
    @endif
    <h2>{{ $compagnie->nom_compagnie ?? 'Nom Compagnie' }}</h2>
    @if (!empty($compagnie->slogant))
        <small><em>{{ $compagnie->slogant }}</em></small>
    @endif
</header>

<div class="bloc">
    <h3>Colis</h3>
    <div class="row"><span class="label">Nom :</span> {{ $colis->nom_colis ?? '-' }}</div>
    <div class="row"><span class="label">Nature :</span> {{ $colis->nature ?? '-' }}</div>
</div>

<div class="bloc">
    <h3>Expéditeur</h3>
    <div class="row"><span class="label">Nom :</span> {{ $colis->expediteur ?? '-' }}</div>
    <div class="row"><span class="label">Tél :</span> {{ $colis->numero_exp ?? '-' }}</div>
</div>

<div class="bloc">
    <h3>Destinataire</h3>
    <div class="row"><span class="label">Nom :</span> {{ $colis->destinataire ?? '-' }}</div>
    <div class="row"><span class="label">Tél :</span> {{ $colis->numero_dest ?? '-' }}</div>
</div>

<div class="bloc">
    <h3>Trajet</h3>
    <div class="row"><span class="label">Gare de départ :</span> {{ $colis->provient_de ?? '-' }}</div>
    <div class="row"><span class="label">Gare de destination :</span> {{ $colis->destination ?? '-' }}</div>
</div>

<div class="qr">
    <img src="{{ $qrPath }}" width="90">
    <div class="row" style="font-weight:bold;margin-top:3px;">Code : {{ $colis->code_colis ?? '-' }}</div>
</div>

<div class="row" style="text-align:center;"><span class="label">Enregistré par :</span> {{ $colis->agent_nom ?? '-' }}</div>

<footer>
    Reçu généré le {{ date('d/m/Y à H:i') }}<br>Merci d’avoir choisi {{ $compagnie->nom_compagnie ?? 'notre compagnie' }}.
</footer>

</body>
</html>
