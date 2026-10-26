<?php

namespace App\Support;

/** Aritmetik perpuluhan tepat (bcmath) untuk wang & kuantiti. Elak float dalam pengiraan stok/kos. */
final class Dec
{
    public const SKALA = 6;

    public static function n(string|int|float|null $v): string
    {
        if ($v === null || $v === '') {
            return '0';
        }
        if (is_float($v)) {
            $v = sprintf('%.'.self::SKALA.'F', $v);
        }

        return bcadd((string) $v, '0', self::SKALA);
    }

    public static function add(...$v): string
    {
        return array_reduce($v, fn ($c, $x) => bcadd($c, self::n($x), self::SKALA), '0');
    }

    public static function sub($a, $b): string
    {
        return bcsub(self::n($a), self::n($b), self::SKALA);
    }

    public static function mul($a, $b): string
    {
        return bcmul(self::n($a), self::n($b), self::SKALA);
    }

    public static function div($a, $b): string
    {
        return self::cmp($b, 0) === 0 ? '0' : bcdiv(self::n($a), self::n($b), self::SKALA);
    }

    public static function cmp($a, $b): int
    {
        return bccomp(self::n($a), self::n($b), self::SKALA);
    }

    public static function min($a, $b): string
    {
        return self::cmp($a, $b) <= 0 ? self::n($a) : self::n($b);
    }

    public static function neg($a): string
    {
        return bcmul(self::n($a), '-1', self::SKALA);
    }

    /** Bundar separuh-naik ke $dp tempat perpuluhan. */
    public static function round($v, int $dp = 2): string
    {
        $v = self::n($v);
        $f = bcpow('10', (string) $dp, 0);
        $x = bcmul($v, $f, self::SKALA);
        $x = bccomp($x, '0', self::SKALA) >= 0 ? bcadd($x, '0.5', 0) : bcsub($x, '0.5', 0);

        return bcdiv($x, $f, $dp);
    }
}
