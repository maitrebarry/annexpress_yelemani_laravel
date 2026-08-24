<?php

namespace App\Mail;

use App\Models\Billet;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/**
 * Port du corps d'email "✅ Billet validé" (PHPMailer inline) de
 * Projets_licence/app/controllers/admin/Liste_ententes.php::validation(). Le legacy joignait
 * un PDF+QR (Dompdf) ; non disponible ici (voir README "pas de PDF") — renvoie vers la fiche
 * billet imprimable (site.billet) à la place, même déviation que App\Mail\ReservationConfirmee.
 */
class BilletValide extends Mailable
{
    public function __construct(
        public Billet $billet,
        public string $nomCompagnie,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '✅ Billet validé - '.$this->billet->numeroBillets,
            from: config('mail.from.address'),
        );
    }

    public function content(): Content
    {
        return new Content(view: 'site.mail.billet-valide');
    }
}
