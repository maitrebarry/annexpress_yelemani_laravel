<?php

namespace App\Support;

class Flash
{
    public static function set(string $message, string $type = 'danger'): void
    {
        session()->flash('notification', [
            'message' => $message,
            'class' => self::color($type),
            'icon' => self::icon($type),
        ]);
    }

    private static function color(string $type): string
    {
        return match ($type) {
            'success' => '#10b981',
            'danger' => '#e11d48',
            'warning' => '#f59e0b',
            default => '#0f3b5e',
        };
    }

    private static function icon(string $type): string
    {
        return match ($type) {
            'success' => 'bx bx-check-circle',
            'danger' => 'bx bx-error',
            'warning' => 'bx bx-warning',
            default => 'bx bx-info-circle',
        };
    }
}
