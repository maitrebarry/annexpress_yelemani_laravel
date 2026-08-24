<div style="font-family:Segoe UI,Arial,sans-serif;max-width:480px;margin:auto;">
    <div style="background:#0f3b5e;color:#fff;padding:16px 20px;border-radius:10px 10px 0 0;text-align:center;">
        <h2 style="margin:0;font-size:18px;">{{ $nomCompagnie }}</h2>
    </div>
    <div style="border:1px solid #e5e7eb;border-top:none;border-radius:0 0 10px 10px;padding:20px;">
        <p style="font-size:16px;margin:0 0 14px;">Bonjour <strong>{{ $nomClient }}</strong>, votre réservation est enregistrée ✅</p>
        <table style="width:100%;border-collapse:collapse;font-size:14px;margin-bottom:16px;">
            <tr><td style="padding:6px 0;color:#6b7280;">N° billet</td><td style="padding:6px 0;font-weight:bold;text-align:right;">{{ $billet->numeroBillets }}</td></tr>
            <tr><td style="padding:6px 0;color:#6b7280;">Destination</td><td style="padding:6px 0;font-weight:bold;text-align:right;">{{ $billet->destinationId }}</td></tr>
            <tr><td style="padding:6px 0;color:#6b7280;">Date</td><td style="padding:6px 0;font-weight:bold;text-align:right;">{{ \Illuminate\Support\Carbon::parse($billet->jourVoyage)->format('d/m/Y') }}</td></tr>
            <tr><td style="padding:6px 0;color:#6b7280;">Heure</td><td style="padding:6px 0;font-weight:bold;text-align:right;">{{ $billet->Heur_departs }}</td></tr>
        </table>
        <div style="background:#fff7ed;border-left:4px solid #f59e0b;padding:12px 14px;border-radius:6px;font-size:14px;color:#92400e;">
            ⏱️ <strong>Paiement Orange Money sous 30 minutes</strong><br>Sinon la réservation sera automatiquement annulée.
        </div>
    </div>
</div>
