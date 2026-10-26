<?php

namespace App\Services;

/** TOTP RFC 6238 (SHA1, 6 digit, 30 saat) untuk 2FA. Rahsia Base32. */
class Totp
{
    private const ABJAD = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';

    public static function secret(int $bytes = 20): string
    {
        $bin = random_bytes($bytes);
        $bits = '';
        foreach (str_split($bin) as $c) {
            $bits .= str_pad(decbin(ord($c)), 8, '0', STR_PAD_LEFT);
        }
        $out = '';
        foreach (str_split($bits, 5) as $chunk) {
            $out .= self::ABJAD[bindec(str_pad($chunk, 5, '0'))];
        }

        return $out;
    }

    public static function code(string $secret, ?int $time = null): string
    {
        $key = self::decode($secret);
        $counter = pack('N*', 0).pack('N*', intdiv($time ?? time(), 30));
        $h = hash_hmac('sha1', $counter, $key, true);
        $o = ord($h[19]) & 0xF;
        $num = ((ord($h[$o]) & 0x7F) << 24) | (ord($h[$o + 1]) << 16) | (ord($h[$o + 2]) << 8) | ord($h[$o + 3]);

        return str_pad((string) ($num % 1000000), 6, '0', STR_PAD_LEFT);
    }

    /** Terima kod dalam tetingkap ±1 langkah (jam tidak tepat). */
    public static function verify(string $secret, string $code, ?int $time = null): bool
    {
        $code = preg_replace('/\D/', '', $code);
        if (strlen($code) !== 6) {
            return false;
        }
        $t = $time ?? time();
        foreach ([-30, 0, 30] as $d) {
            if (hash_equals(self::code($secret, $t + $d), $code)) {
                return true;
            }
        }

        return false;
    }

    public static function uri(string $secret, string $label, string $issuer = 'AI Bijan'): string
    {
        return 'otpauth://totp/'.rawurlencode($issuer.':'.$label).'?secret='.$secret.'&issuer='.rawurlencode($issuer).'&digits=6&period=30';
    }

    private static function decode(string $b32): string
    {
        $bits = '';
        foreach (str_split(strtoupper(preg_replace('/[^A-Z2-7]/i', '', $b32))) as $c) {
            $bits .= str_pad(decbin(strpos(self::ABJAD, $c)), 5, '0', STR_PAD_LEFT);
        }
        $out = '';
        foreach (str_split($bits, 8) as $b) {
            if (strlen($b) === 8) {
                $out .= chr(bindec($b));
            }
        }

        return $out;
    }
}
