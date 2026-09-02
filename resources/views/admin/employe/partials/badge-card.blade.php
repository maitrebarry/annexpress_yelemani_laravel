{{--
    Format 1 — Corporate Horizontal (110 × 56 mm). Seul format actif pour l'instant.
    $b : tableau retourné par la closure $renderBadge() du parent (print-card.blade.php).
    $compNom / $logoSrc : hérités du scope du parent (les @include Blade héritent des
    variables du parent).
--}}
<div class="badge-h">
    <div class="bh-left">
        @if (! empty($logoSrc))
            <div class="bh-logo-chip"><img src="{{ $logoSrc }}" alt="Logo"></div>
        @endif
        <div class="bh-brand">{{ strtoupper($compNom) }}</div>
        <div class="bh-gold"></div>
        <div class="bh-tag">Voyagez en<br>toute confiance</div>
    </div>
    <div class="bh-right">
        <div class="bh-photo">
            @if ($b['photo'])
                <img src="{{ $b['photo'] }}" alt="Photo">
            @else
                <i class="fas fa-user ph"></i>
            @endif
        </div>
        <div class="bh-name">{{ $b['nom'] }}</div>
        <div class="bh-role">{{ $b['role'] }}</div>
        <div class="bh-grid">
            <div class="bh-item">
                <div class="bh-ic"><i class="fas fa-id-badge"></i></div>
                <div class="bh-dt"><span class="bh-lbl">Fonction</span><span class="bh-val">{{ $b['fonction'] }}</span></div>
            </div>
            <div class="bh-item">
                <div class="bh-ic"><i class="fas fa-phone"></i></div>
                <div class="bh-dt"><span class="bh-lbl">Téléphone</span><span class="bh-val">{{ $b['tel'] }}</span></div>
            </div>
            @if ($b['mail'])
                <div class="bh-item">
                    <div class="bh-ic"><i class="fas fa-envelope"></i></div>
                    <div class="bh-dt"><span class="bh-lbl">Email</span><span class="bh-val">{{ $b['mail'] }}</span></div>
                </div>
            @endif
            @if ($b['location'])
                <div class="bh-item">
                    <div class="bh-ic"><i class="fas fa-location-dot"></i></div>
                    <div class="bh-dt"><span class="bh-lbl">Affectation</span><span class="bh-val">{{ $b['location'] }}</span></div>
                </div>
            @endif
        </div>
    </div>
    <div class="bh-foot"></div>
</div>
