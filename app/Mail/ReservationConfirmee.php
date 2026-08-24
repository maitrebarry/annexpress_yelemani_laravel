<?php

namespace App\Mail;

use App\Models\Billet;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/**
 * Port du corps d'email inline (PHPMailer) de
 * Projets_licence/app/controllers/site/Reservation_formulaire.php::index(). Envoyé de
 * façon synchrone (pas de ShouldQueue) : le legacy envoyait aussi dans la même requête,
 * et rien ne garantit qu'un `queue:work` tourne en continu sur cet environnement.
 */
class ReservationConfirmee extends Mailable
{
    public function __construct(
        public Billet $billet,
        public string $nomClient,
        public string $nomCompagnie,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '🎟️ Réservation enregistrée - '.$this->billet->numeroBillets,
            from: config('mail.from.address'),
        );
    }

    public function content(): Content
    {
        return new Content(view: 'site.mail.reservation-confirmee');
    }
}
