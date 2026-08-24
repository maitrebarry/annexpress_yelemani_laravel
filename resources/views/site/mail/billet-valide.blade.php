<div style="font-family:Segoe UI,Arial,sans-serif;max-width:480px;margin:auto;">
    <div style="background:#10b981;color:#fff;padding:16px 20px;border-radius:10px 10px 0 0;text-align:center;">
        <h2 style="margin:0;font-size:18px;">✅ Billet validé</h2>
    </div>
    <div style="border:1px solid #e5e7eb;border-top:none;border-radius:0 0 10px 10px;padding:20px;">
        <p style="font-size:16px;margin:0 0 14px;">Bonjour <strong>{{ $billet->Client }}</strong>, votre paiement a été confirmé et votre billet est validé ✅</p>
        <table style="width:100%;border-collapse:collapse;font-size:14px;margin-bottom:16px;">
            <tr><td style="padding:6px 0;color:#6b7280;">N° billet</td><td style="padding:6px 0;font-weight:bold;text-align:right;">{{ $billet->numeroBillets }}</td></tr>
            <tr><td style="padding:6px 0;color:#6b7280;">Destination</td><td style="padding:6px 0;font-weight:bold;text-align:right;">{{ $billet->destinationId }}</td></tr>
            <tr><td style="padding:6px 0;color:#6b7280;">Date</td><td style="padding:6px 0;font-weight:bold;text-align:right;">{{ \Illuminate\Support\Carbon::parse($billet->jourVoyage)->format('d/m/Y') }}</td></tr>
            <tr><td style="padding:6px 0;color:#6b7280;">Place(s)</td><td style="padding:6px 0;font-weight:bold;text-align:right;">{{ $billet->numeroPlace }}</td></tr>
        </table>
        <div style="text-align:center;margin:20px 0;">
            <a href="{{ route('site.billet', $billet->numeroBillets) }}" style="display:inline-block;background:#10b981;color:#fff;text-decoration:none;padding:12px 28px;border-radius:8px;font-weight:600;">Voir / imprimer mon billet</a>
        </div>
        <p style="margin-top:16px;font-size:13px;color:#9ca3af;text-align:center;">Merci de voyager avec {{ $nomCompagnie }} !</p>
    </div>
</div>
