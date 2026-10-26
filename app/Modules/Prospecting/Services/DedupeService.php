<?php

namespace App\Modules\Prospecting\Services;

use App\Modules\Prospecting\Models\Prospect;
use Illuminate\Support\Facades\Log;

/**
 * DedupeService — elak prospect berulang.
 *
 * Normalisasi:
 * - E-mel: lowercase, trim, gmailIGNORE dots & +alias
 * - Telefon: E.164 Malaysia (+60...)
 * - Hash: SHA-256 lowercase
 */
class DedupeService
{
    /**
     * Normalkan e-mel kepada bentuk kanonikal.
     */
    public function normalkanEmail(?string $email): ?string
    {
        if (! $email) {
            return null;
        }

        $email = strtolower(trim($email));

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return null;
        }

        [$local, $domain] = explode('@', $email, 2);

        // Gmail: abaikan titik dan +alias
        if (in_array($domain, ['gmail.com', 'googlemail.com'], true)) {
            $local = str_replace('.', '', $local);
            $local = preg_replace('/\+.*$/', '', $local);
            $domain = 'gmail.com';
        } else {
            // Outlook/Hotmail: abaikan +alias sahaja
            $local = preg_replace('/\+.*$/', '', $local);
        }

        return $local.'@'.$domain;
    }

    /**
     * Normalkan telefon kepada E.164 Malaysia.
     */
    public function normalkanPhone(?string $phone): ?string
    {
        if (! $phone) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $phone);

        if (strlen($digits) < 8) {
            return null;
        }

        // Buang awalan 00
        if (str_starts_with($digits, '00')) {
            $digits = substr($digits, 2);
        }

        // 0xx -> +60xx
        if (str_starts_with($digits, '0')) {
            $digits = '60'.substr($digits, 1);
        }

        // 8xx (tanpa 0) -> +608xx (sellular) — hanya jika 9-10 digit
        if (! str_starts_with($digits, '60') && strlen($digits) >= 9 && strlen($digits) <= 10) {
            $digits = '60'.$digits;
        }

        if (! str_starts_with($digits, '60')) {
            return null;
        }

        return '+'.$digits;
    }

    /**
     * Hash dedupe untuk e-mel ATAU telefon.
     */
    public function hash(?string $email, ?string $phone): ?string
    {
        $email = $this->normalkanEmail($email);
        $phone = $this->normalkanPhone($phone);

        $material = $email ?: $phone;

        if (! $material) {
            return null;
        }

        return hash('sha256', $material);
    }

    /**
     * Cari prospect yang sudah wujud (dupe) dalam tenant.
     */
    public function cariDupe(int $tenantId, ?string $email, ?string $phone): ?Prospect
    {
        $hash = $this->hash($email, $phone);

        if (! $hash) {
            return null;
        }

        return Prospect::query()
            ->where('tenant_id', $tenantId)
            ->where('dedupe_hash', $hash)
            ->first();
    }

    /**
     * Simpan prospect dengan semakan dupe.
     *
     * @return array{prospect: Prospect, created: bool, duplicate: bool}
     */
    public function simpanDenganSemakanDupe(int $tenantId, array $data): array
    {
        $email = $data['email'] ?? null;
        $phone = $data['phone'] ?? null;
        $hash = $this->hash($email, $phone);

        if ($hash) {
            $existing = Prospect::query()
                ->where('tenant_id', $tenantId)
                ->where('dedupe_hash', $hash)
                ->first();

            if ($existing) {
                // Kemas kini data ter enriching (jangan timpa consent!)
                $mergeable = array_intersect_key($data, array_flip([
                    'first_name', 'last_name', 'company', 'job_title',
                    'website', 'linkedin', 'country', 'company_size',
                    'revenue_estimate', 'enrichment', 'tags',
                ]));

                if ($mergeable) {
                    $existing->fill($mergeable)->save();
                }

                Log::info('Prospect: dupe dikesan, data digabung', [
                    'tenant_id' => $tenantId,
                    'prospect_id' => $existing->id,
                ]);

                return ['prospect' => $existing, 'created' => false, 'duplicate' => true];
            }
        }

        $data['tenant_id'] = $tenantId;
        $data['dedupe_hash'] = $hash;

        $prospect = Prospect::create($data);

        return ['prospect' => $prospect, 'created' => true, 'duplicate' => false];
    }
}
