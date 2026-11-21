<?php

namespace App\Support;

class ScamDetector
{
    private const PATTERNS = [
        '/\bdinero\b/iu',
        '/\btransferenc/iu',
        '/\bpago(s)?\b/iu',
        '/\bpaypal\b/iu',
        '/\bwestern\s*union\b/iu',
        '/\bbizum\b/iu',
        '/\bcrypto\b/iu',
        '/\busdt\b/iu',
        '/\bwire\s*transfer\b/iu',
        '/\bmoney\b/iu',
        '/\bcashapp\b/iu',
        '/\bvenmo\b/iu',
    ];

    public static function mentionsMoney(string $text): bool
    {
        foreach (self::PATTERNS as $pattern) {
            if (preg_match($pattern, $text) === 1) {
                return true;
            }
        }

        return false;
    }

    public static function warningMessage(): string
    {
        return 'HubSport nunca te pedirá dinero por chat. Desconfía de transferencias y reporta al usuario.';
    }
}
