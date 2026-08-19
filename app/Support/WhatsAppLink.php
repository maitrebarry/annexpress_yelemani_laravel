<?php

namespace App\Support;

/**
 * Port de Projets_licence/app/core/whatsapp.php (whatsapp_number() + whatsapp_link()).
 */
class WhatsAppLink
{
    public static function number(?string $numero): string
    {
        $chiffres = preg_replace('/\D+/', '', (string) $numero);

        if ($chiffres === '') {
            return '';
        }

        // Numéro local malien (8 chiffres) sans indicatif -> on ajoute l'indicatif Mali.
        if (strlen($chiffres) === 8) {
            $chiffres = '223'.$chiffres;
        }

        return $chiffres;
    }

    public static function url(?string $numero, string $message): string
    {
        $chiffres = self::number($numero);

        if ($chiffres === '') {
            return '';
        }

        return 'https://wa.me/'.$chiffres.'?text='.rawurlencode($message);
    }
}
