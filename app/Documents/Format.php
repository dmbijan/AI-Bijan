<?php

namespace App\Documents;

/** Pemformatan Malaysia: RM, tarikh, amaun dalam perkataan (BM/EN), penyamaran data peribadi. */
class Format
{
    public static function rm(float|int|string|null $v): string
    {
        $n = round((float) $v, 2);

        return ($n < 0 ? '-' : '').'RM'.number_format(abs($n), 2, '.', ',');
    }

    public static function num(float|int|string|null $v, int $dp = 2): string
    {
        return number_format((float) $v, $dp, '.', ',');
    }

    public static function date(?string $d, string $locale = 'ms'): string
    {
        if (! $d) {
            return '';
        }
        $t = strtotime($d);
        $bulan = $locale === 'ms'
            ? ['Januari', 'Februari', 'Mac', 'April', 'Mei', 'Jun', 'Julai', 'Ogos', 'September', 'Oktober', 'November', 'Disember']
            : ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];

        return date('j', $t).' '.$bulan[(int) date('n', $t) - 1].' '.date('Y', $t);
    }

    public static function month(string $ym, string $locale = 'ms'): string
    {
        return preg_replace('/^\d+ /', '', self::date($ym.'-01', $locale));
    }

    /** Sembunyikan kecuali 4 aksara terakhir (No. KP, akaun bank). */
    public static function mask(?string $v, int $show = 4): string
    {
        $v = (string) $v;
        $len = mb_strlen($v);

        return $len <= $show ? $v : str_repeat('•', $len - $show).mb_substr($v, -$show);
    }

    public static function words(float $amount, string $locale = 'ms'): string
    {
        $amount = round($amount, 2);
        $rm = (int) floor($amount);
        $sen = (int) round(($amount - $rm) * 100);
        if ($locale === 'en') {
            $s = 'Ringgit Malaysia '.ucfirst(self::en($rm));
            if ($sen) {
                $s .= ' and '.self::en($sen).' sen';
            }

            return $s.' only';
        }
        $s = 'Ringgit Malaysia '.ucfirst(self::ms($rm));
        if ($sen) {
            $s .= ' dan '.self::ms($sen).' sen';
        }

        return $s.' sahaja';
    }

    private static function ms(int $n): string
    {
        $satu = ['kosong', 'satu', 'dua', 'tiga', 'empat', 'lima', 'enam', 'tujuh', 'lapan', 'sembilan'];
        if ($n < 10) {
            return $satu[$n];
        }
        if ($n === 10) {
            return 'sepuluh';
        }
        if ($n === 11) {
            return 'sebelas';
        }
        if ($n < 20) {
            return $satu[$n - 10].' belas';
        }
        if ($n < 100) {
            return $satu[intdiv($n, 10)].' puluh'.($n % 10 ? ' '.$satu[$n % 10] : '');
        }
        if ($n < 1000) {
            $h = intdiv($n, 100);

            return ($h === 1 ? 'seratus' : $satu[$h].' ratus').($n % 100 ? ' '.self::ms($n % 100) : '');
        }
        foreach ([1_000_000_000 => 'bilion', 1_000_000 => 'juta', 1000 => 'ribu'] as $unit => $nama) {
            if ($n >= $unit) {
                $q = intdiv($n, $unit);
                $depan = ($q === 1 && $unit === 1000) ? 'seribu' : self::ms($q).' '.$nama;

                return $depan.($n % $unit ? ' '.self::ms($n % $unit) : '');
            }
        }

        return (string) $n;
    }

    private static function en(int $n): string
    {
        $a = ['zero', 'one', 'two', 'three', 'four', 'five', 'six', 'seven', 'eight', 'nine', 'ten', 'eleven', 'twelve', 'thirteen', 'fourteen', 'fifteen', 'sixteen', 'seventeen', 'eighteen', 'nineteen'];
        $t = ['', '', 'twenty', 'thirty', 'forty', 'fifty', 'sixty', 'seventy', 'eighty', 'ninety'];
        if ($n < 20) {
            return $a[$n];
        }
        if ($n < 100) {
            return $t[intdiv($n, 10)].($n % 10 ? '-'.$a[$n % 10] : '');
        }
        if ($n < 1000) {
            return $a[intdiv($n, 100)].' hundred'.($n % 100 ? ' and '.self::en($n % 100) : '');
        }
        foreach ([1_000_000_000 => 'billion', 1_000_000 => 'million', 1000 => 'thousand'] as $u => $nm) {
            if ($n >= $u) {
                return self::en(intdiv($n, $u)).' '.$nm.($n % $u ? ' '.self::en($n % $u) : '');
            }
        }

        return (string) $n;
    }
}
